<?php

namespace app\service;

use king\lib\Request;
use king\lib\Log;
use app\cache\Token;
use app\service\Money as MoneyService;
use app\model\User as UserModel;
use app\model\QueryDetail as QueryDetailModel;
use app\model\QueryOrder as QueryOrderModel;

class Query
{
    //维保查询
    private static function maintenance($req,$order_id){

        if($req['vin']=="123"){
            return json_decode( '{"code":809,"msg":"车辆信息获取失败！请检查你的车架号信息是否正确！"}', true);exit;
        }else{
            return json_decode("{\"code\":888,\"msg\":47816}", true);exit;
        }
        $api_key=C('query.other_key');
        $call_back=urlencode(C('query.call_back')."/maintenance?orderid=".$order_id);
        $req['engine']=$req['engine'] ?? '';
        $request = Request::getClass(C('query.maintenance_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'] . '&callbackUrl=' . $call_back, 'get');

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
    private static function collision($req,$order_id){
        if($req['vin']=="123"){
            return json_decode( '{"code":809,"msg":"车辆信息获取失败！请检查你的车架号信息是否正确！"}', true);exit;
        }else{
            return json_decode("{\"code\":888,\"msg\":47816}", true);exit;
        }
        $api_key=C('query.other_key');
        $req['engine']=$req['engine'] ?? '';
        $call_back=urlencode(C('query.call_back')."/collision?orderid=".$order_id);
        $request = Request::getClass(C('query.collision_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine']. '&callbackUrl=' . $call_back, 'get');
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
    private static function vehicleCondition($req,$order_id){
        if($req['vin']=="123"){
            return json_decode( '{"code":10006,"message":"获取发动机号失败","data":null}', true);exit;
        }else{
            return json_decode("{\"code\":10000,\"message\":\"成功\",\"data\":{\"order_id\":131992,\"result\":{\"totalPmCons\":\"一致\",\"zc_date\":\"2012-01-09\",\"licenseType\":\"小型汽车\",\"respCode\":\"1\",\"car_no\":\"闽A0512V\",\"zt\":\"正常\",\"ztCode\":\"A\",\"engine\":\"M10540\"}}}", true);exit;
        }
        $api_key=C('query.other_key');
        $req['engine']=$req['engine'] ?? '';
        $request = Request::getClass(C('query.vehicle_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'], 'get');
        //$request->header = ['Authorization' => $token];
        $request->sendRequest();
        $httpcode = $request->getResponseInfo();
        $res = $request->getResponseBody();
        $httpcode = $httpcode['http_code'];
        if ($httpcode != '200') {
            Log::write($httpcode . ':' . $res . ':' . C('query.vehicle_url') . '?api_key=' . $api_key . '&vin=' . $req['vin'] . '&engine=' . $req['engine'] . ':' . date('Y-m-d H-i-s'), 'vehicleCondition.log',  'error_query');
            return false;
        }
       // Log::write($res . ':' . date('Y-m-d H-i-s'), 'vehicleCondition.log',  'callback_query');
        return json_decode($res, true);
    }
    //违章查询
    public static function regulations($req){
        $api_key=C('query.regulations_key');
        $request = Request::getClass(C('query.regulations_url') . '?key=' . $api_key . '&hpzl=' . $req['hpzl'] . '&hphm=' . $req['hphm']. '&fdjh=' . $req['fdjh']. '&cjh=' . $req['vin'], 'get');
        //$request->header = ['Authorization' => $token];
        $request->sendRequest();
        $httpcode = $request->getResponseInfo();
        $res = $request->getResponseBody();
        $httpcode = $httpcode['http_code'];
        if ($httpcode != '200') {
            Log::write($httpcode . ':' . $res . ':' . C('query.regulations_url') . '?api_key=' . $api_key . '&hpzl=' . $req['hpzl'] . '&hphm=' . $req['hphm']. '&fdjh=' . $req['fdjh']. '&cjh=' . $req['vin'] . ':' . date('Y-m-d H-i-s'), 'regulations_url.log',  'error_query');
            return false;
        }
        return $res;
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
                    $mem='维保查询';
                    $backdata=self::maintenance($req,$order_id);
                    if($backdata['code']==888){
                        $detail_data['maintenance']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']=$backdata['msg'];
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "collision":
                    $mem='碰撞查询';
                    $backdata=self::collision($req,$order_id);
                    if($backdata['code']==888){
                        $detail_data['collision']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']=$backdata['msg'];
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "vehicleCondition":
                    $mem='汽车状态查询';
                    $backdata=self::vehicleCondition($req,$order_id);
                    if($backdata['code']==10000){
                        $detail_data['vehicleCondition']= $backdata['msg'];
                        $up_order['status']=3;//查询成功
                        $up_order['msg']=$backdata['msg'];
                    }
                    else{
                        $up_order['status']=5;//查询失败
                        $up_order['msg']=$backdata['code'].':'.$backdata['msg'];
                    }
                    break;
                case "smallUnion":
                    $mem='小综合查询';
                    $maintenance=self::maintenance($req,$order_id);
                    $collision=self::collision($req,$order_id);
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
                    $mem='大综合查询';
                    $maintenance=self::maintenance($req,$order_id);
                    $collision=self::collision($req,$order_id);
                    $vehicleCondition=self::vehicleCondition($req,$order_id);
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
            return self::saveData($req,$order_id,$up_order,$detail_data,$state,$mem);
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
                    $cost=0;
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
    private static function saveData($req,$order_id,$up_order,$detail_data,$state,$mem){
        QueryOrderModel::where(["id"=>$order_id])->update($up_order);
        if($up_order['status']==3) {//全部查询成功，直接扣除费用
            if (!empty($detail_data['vehicleCondition'])) {
                $query_detail = QueryDetailModel::where(["order_id" => $order_id])->find();
                if (empty($query_detail)) {
                    $insert_data['vehicleCondition'] = $detail_data['vehicleCondition'];
                    $insert_data['order_id'] = $order_id;
                    $insert_data['vin'] = $req['vin'];
                    $insert_data['add_time'] = time();
                    QueryDetailModel::insert($insert_data);
                }else{
                    $updata_data['vehicleCondition'] = $detail_data['vehicleCondition'];
                    QueryDetailModel::where(["order_id" => $order_id])->update($updata_data);

                }
            }
            if($state['cost']>0) {//花费的费用大于0才记录扣款
                MoneyService::moneyChange(-$state['cost'], 3, $mem, $req['user_id']);
            }
            return ['code' => 200, 'data' => ["msg"=>'全部成功','cardata'=>$detail_data]];
        }elseif ($up_order['status']==4){//部分查询成功，返还查询失败的费用

            if($state['cost']>0){//花费的费用大于0才记录扣款
                MoneyService::moneyChange(-$state['cost'],3,$mem,$req['user_id']);
                //返还失败费用
                if($state['type']==3){//如果是小综合查询
                    if(empty($detail_data['collision'])){
                        $cost=self::getCost($req['user_id'],"collision");//查询该用户该类型需退款的单笔费用
                        MoneyService::moneyChange($cost,4,$mem."：碰撞查询失败退款",$req['user_id']);
                    }
                    if(empty($detail_data['maintenance'])){
                        $cost=self::getCost($req['user_id'],"maintenance");//查询该用户该类型需退款的单笔费用
                        MoneyService::moneyChange($cost,4,$mem."：维保查询失败退款",$req['user_id']);
                    }
                }elseif ($state['type']==4){//如果是大综合查询
                    if(empty($detail_data['collision'])){
                        $cost=self::getCost($req['user_id'],"collision");//查询该用户该类型需退款的单笔费用
                        MoneyService::moneyChange($cost,4,$mem."：碰撞查询失败退款",$req['user_id']);
                    }
                    if(empty($detail_data['maintenance'])){
                        $cost=self::getCost($req['user_id'],"maintenance");//查询该用户该类型需退款的单笔费用
                        MoneyService::moneyChange($cost,4,$mem."：维保查询失败退款",$req['user_id']);
                    }
                    if(empty($detail_data['vehicleCondition'])){
                        $cost=self::getCost($req['user_id'],"vehicleCondition");//查询该用户该类型需退款的单笔费用
                        MoneyService::moneyChange($cost,4,$mem."：车辆信息查询失败退款",$req['user_id']);
                    }
                }
            }
            return ['code' => 200, 'data' => json_encode(["msg"=>'部分成功','cardata'=>$detail_data])];
        }else{//全部失败 不做扣费处理
            return ['code' => 400, 'data' => json_encode(["msg"=>'查询失败，请检查vin号码后重试','cardata'=>$detail_data])];
        }
    }
    //车辆vin扫码识别
    public static function vinOcr($req){
        $AppKey=C('query.appkey');
        $AppSecret=C('query.appsecret');
        header("content-type:text/html;charset=utf-8");
        $url = C('query.ocr_http')."?wsdl";
        $method = "level.vehicle.vin.ocr";
        $data = "<root><appkey>".$AppKey."</appkey><appsecret>".$AppSecret."</appsecret><method>".$method."</method><requestformat>json</requestformat><imgbase64>".$req['imgbase64']."</imgbase64></root>";
        $client = new \SoapClient($url);
        $addResult = $client->__soapCall("LevelData",array(array('xmlInput'=>$data)));
        $result = $addResult->LevelDataResult;
        $resultarray=json_decode($result,true);
        if(isset($resultarray['Info']['Success'])&&($resultarray['Info']['Success']==1||$resultarray['Info']['Success']==true)){
            return ['code' => 200, 'data' => $addResult->LevelDataResult];
        }else{
            return ['code' => 400, 'data' => $addResult->LevelDataResult];
        }
    }
    //车辆vin读取车辆信息
    public static function vinGetinfo($req){
        $AppKey=C('query.appkey');
        $AppSecret=C('query.appsecret');
        header("content-type:text/html;charset=utf-8");
        $url = C('query.ocr_http')."?wsdl";
        $method = "level.vehicle.vin.mix";
        $data = "<root><appkey>".$AppKey."</appkey><appsecret>".$AppSecret."</appsecret><method>".$method."</method><requestformat>json</requestformat><vin>".$req['vin']."</vin></root>";
        $client = new \SoapClient($url);
        $addResult = $client->__soapCall("LevelData",array(array('xmlInput'=>$data)));
        $result = json_decode($addResult->LevelDataResult,true);
        if(isset($result['Info']['Success'])&&$result['Info']['Success']==1){
            return ['code' => 200, 'data' => $addResult->LevelDataResult];
        }else{
            return ['code' => 400, 'data' => $addResult->LevelDataResult];
        }

    }

    //获取查询记录
    public static function getQueryRecord($req){
        $res = QueryOrderModel::where(['user_id'=>$req['user_id']])->page($req['c'], $req['p']);
        $dict=[
            0=>'未付款',
            1=>'查询中',
            2=>'已付款，查询中',
            3=>'查询成功',
            4=>'部分成功',
            5=>'查询失败',
            6=>'放弃查询'
        ];
        $type=[
            0=>'维保查询',
            1=>'碰撞查询',
            2=>'汽车状态查询',
            3=>'违章查询',
            4=>'小综合查询',
            5=>'大综合查询'
        ];
        foreach ($res['rs'] as $key=>$value){
            $res['rs'][$key]['create_time']=date('Y-m-d H:i:s',$value['create_time']);
            $res['rs'][$key]['status_msg']=$dict[$value['status']];
            $res['rs'][$key]['type_msg']=$type[$value['type']];
        }
        return ['code' => 200, 'data' => $res];
    }
}
