<?php

namespace app\service;

use app\cache\Token;
use app\helper\Helper;
use app\model\User as UserModel;
use app\model\QueryDetail as QueryDetailModel;
use app\model\QueryOrder as QueryOrderModel;

class Query
{
    //维保查询
    private static function maintenance($req){
        $api_key=C('query.other_key');
        $request = Request::getClass(C('query.maintenance_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'], 'get');
        //$request->header = ['Authorization' => $token];
        $request->sendRequest();
        $httpcode = $request->getResponseInfo();
        $res = $request->getResponseBody();
        $httpcode = $httpcode['http_code'];
        if ($httpcode != '200') {
            Log::write($httpcode . ':' . $res . ':' . C('query.maintenance_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'] . ':' . date('Y-m-d H-i-s'), 'maintenance.log',  'error_query');
            return false;
        }
        return json_decode($res, true);
    }
    //碰撞查询
    private static function collision($req){
        $api_key=C('query.other_key');
        $request = Request::getClass(C('query.collision_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'], 'get');
        //$request->header = ['Authorization' => $token];
        $request->sendRequest();
        $httpcode = $request->getResponseInfo();
        $res = $request->getResponseBody();
        $httpcode = $httpcode['http_code'];
        if ($httpcode != '200') {
            Log::write($httpcode . ':' . $res . ':' . C('query.collision_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'] . ':' . date('Y-m-d H-i-s'), 'collision.log',  'error_query');
            return false;
        }
        return json_decode($res, true);
    }
    //汽车状态查询
    private static function vehicleCondition($req){
        $api_key=C('query.other_key');
        $request = Request::getClass(C('query.vehicleCondition_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'], 'get');
        //$request->header = ['Authorization' => $token];
        $request->sendRequest();
        $httpcode = $request->getResponseInfo();
        $res = $request->getResponseBody();
        $httpcode = $httpcode['http_code'];
        if ($httpcode != '200') {
            Log::write($httpcode . ':' . $res . ':' . C('query.vehicleCondition_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'] . ':' . date('Y-m-d H-i-s'), 'vehicleCondition.log',  'error_query');
            return false;
        }
        return json_decode($res, true);
    }
    //违章查询
    public static function regulations($req){

    }
    //判断余额，余额不够，可以选择单次付款
    public static function getPay($req,$type){
        $state=self::canPay($req,$type);
        if($state['type']!=6){
            return ['code' => 200, 'data' => ['cost'=>$state['cost'],'money'=>$state['money'],'pay'=>1]];
        }else
        {
            return ['code' => 200, 'data' => ['cost'=>$state['cost'],'money'=>$state['money'],'pay'=>'支付相关信息']];
        }
    }
    //余额不足生成付款查询订单
    public static function payQuery($req,$type){
        $state=self::canPay($req,$type);
        //创建查询订单
        $order_id = QueryOrderModel::insert([
            'user_id'        => $req['user_id'],
            'vin'     => $req['vin'],
            'cost'     => $state['cost'],
            'type'     => $state['type'],
            'status'     => 0,//未付款
            'create_time' => time(),
        ]);
        //TODO 待完善
    }
    //余额足够直接查询
    public static function query($req,$type){
        $state=self::canPay($req,$type);
        if($state['type']==6){
            return ['code' => 400, 'data' => '余额已不足，请充值'];
        }
            //创建查询订单
            $order_id = QueryOrderModel::insert([
                'user_id'        => $req['user_id'],
                'vin'     => $req['vin'],
                'cost'     => $state['cost'],
                'type'     => $state['type'],
                'status'     => 1,//直接查询
                'create_time' => time(),
            ]);
            $detail_data=[];
            switch ($type){
                case "maintenance":
                    $backdata=self::maintenance($req);
                    if($backdata['code']==888){
                        $detail_data['maintenance']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']="成功";
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "collision":
                    $backdata=self::collision($req);
                    if($backdata['code']==888){
                        $detail_data['maintenance']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']="成功";
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "vehicleCondition":
                    $backdata=self::vehicleCondition($req);
                    if($backdata['code']==10000){
                        $detail_data['vehicleCondition']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']="成功";
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "smallUnion":
                    $maintenance=self::maintenance($req);
                    $collision=self::collision($req);
                    if($maintenance['code']==888&&$collision['code']==888){
                        $detail_data['maintenance']= $maintenance['msg'];
                        $detail_data['collision']= $collision['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']="成功";
                    }
                    else {
                        if (($maintenance['code'] == 888 && $collision['code'] != 888)||($maintenance['code'] != 888 && $collision['code'] == 888))
                        {
                            $up_order['status'] = 4;//部分失败
                            if($maintenance['code'] != 888){
                                $detail_data['collision']= $collision['msg'];
                                $up_order['msg'] = '维保失败:'.$maintenance['code'] . '-' . $maintenance['msg'];
                            }else{
                                $detail_data['maintenance']= $maintenance['msg'];
                                $up_order['msg'] = '碰撞失败:'.$collision['code'] . '-' . $collision['msg'];
                            }
                        }else{
                            $up_order['status']=5;//查询失败
                            $up_order['msg']='维保失败:'.$maintenance['code'].'-'.$maintenance['msg'].'，碰撞失败:'.$collision['code'].'-'.$collision['msg'];
                        }
                    }

                    break;
                case "bigUnion":
                    $maintenance=self::maintenance($req);
                    $collision=self::collision($req);
                    $vehicleCondition=self::vehicleCondition($req);
                    if($maintenance['code']==888&&$collision['code']==888&&$vehicleCondition['code']==10000){
                        $detail_data['maintenance']= $maintenance['msg'];
                        $detail_data['collision']= $collision['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']="成功";
                    }
                    else if($maintenance['code']!=888&&$collision['code']!=888&&$vehicleCondition['code']!=10000){
                        $up_order['status']=5;//查询失败
                        $up_order['msg']='维保失败:'.$maintenance['code'].'-'.$maintenance['msg'].'，碰撞失败:'.$collision['code'].'-'.$collision['msg'].'，状态失败:'.$vehicleCondition['code'].'-'.$vehicleCondition['msg'];
                    }
                    else{
                        $up_order['msg']='';
                        if($maintenance['code']==888){
                            $detail_data['maintenance']= $maintenance['msg'];
                        }else{
                            $up_order['msg'] .= '维保失败:'.$maintenance['code'] . '-' . $maintenance['msg'];
                        }
                        if($collision['code']==888){
                            $detail_data['collision']= $collision['msg'];
                        }else{
                            $up_order['msg'] .= '碰撞失败:'.$collision['code'] . '-' . $collision['msg'];
                        }
                        if($vehicleCondition['code']==10000){
                            $detail_data['vehicleCondition']= $vehicleCondition['msg'];
                        }else{
                            $up_order['msg'] .= '状态失败:'.$vehicleCondition['code'] . '-' . $vehicleCondition['msg'];
                        }
                        $up_order['status'] = 4;//部分失败
                    }
                    break;
                default:
                    break;
            }
            return self::saveData($req['vin'],$order_id,$up_order,$detail_data);
       }
    //获取某个类型查询所需的费用（$type不填则将所有类型扣费列出来）
    public static function  getCost($user_id,$type=""){
        //用户等级表
        $users_level=UserModel::setTable('user u')
            ->field('ul.*')
            ->join('user_level ul', 'u.level_id = ul.id')
            ->where([
                'u.id' => $user_id
            ])
            ->find();
        if($type==""){
            return $users_level;
        }else
        {
            switch ($type){
                case "maintenance":
                    $cost=$users_level['weibao_money'];
                    break;
                case "collision":
                    $cost=$users_level['penzhuang_money'];
                    break;
                case "vehicleCondition":
                    $cost=$users_level['state_money'];
                    break;
                case "smallUnion":
                    $cost=$users_level['xiaozonghe_money'];
                    break;
                case "bigUnion":
                    $cost=$users_level['dazonghe_money'];
                    break;
                default:
                    break;
            }
            return $cost;
        }

    }
    //判断余额是否足够查询，不够跳转支付
    private static function canPay($req,$type){
        //如果是余额够用情况则直接查询，查询成功扣费，不成功不扣费
        switch ($type){
            case "maintenance":
                $back_type=0;
                break;
            case "collision":
                $back_type=1;
                break;
            case "vehicleCondition":
                $back_type=2;
                break;
            case "smallUnion":
                $back_type=3;
                break;
            case "bigUnion":
                $back_type=4;
                break;
            default:
                $back_type=5;
                break;
        }
        $user=UserModel::where(["id"=>$req['user_id']])->find();
        $cost=self::getCost($req['user_id'],$type);//本次查询扣费
        if($user['money']>=$cost){
            $state['money']=$user['money'];
            $state['type']=$back_type;
            $state['cost']=$cost;
        }else{
            $state['money']=$user['money'];
            $state['cost']=$cost;
            $state['type']=6;//余额不足
        }
        return $state;
    }
    //查询完成修改订单状态，并保存查询成功的结果数据
    private static function saveData($vin,$order_id,$up_order,$detail_data){
        QueryOrderModel::where(["id"=>$order_id])->update($up_order);
        if($up_order['status']==3){//全部查询成功，直接扣除费用
            $detail_data['order_id']=$order_id;
            $detail_data['vin']=$vin;
            $detail_data['add_time']=time();
            QueryOrderModel::insert($detail_data);
            //扣除费用

            return ['code' => 200, 'data' => ["msg"=>'全部成功','cardata'=>$detail_data]];
        }elseif ($up_order['status']==4){//部分查询成功，返还查询失败的费用
            $detail_data['order_id']=$order_id;
            $detail_data['vin']=$vin;
            $detail_data['add_time']=time();
            QueryOrderModel::insert($detail_data);
            //扣除总费用

            //返还失败费用
            return ['code' => 200, 'data' => ["msg"=>'部分成功','cardata'=>$detail_data]];
        }else{//全部失败 不做扣费处理
            return ['code' => 400, 'data' => ["msg"=>'查询失败，请检查vin号码后重试','cardata'=>$detail_data]];
        }
    }
}
