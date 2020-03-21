<?php

namespace app\service;

use king\lib\Log;
use app\model\User as UserModel;
use app\model\MoneyRecord as MoneyRecordModel;
use app\model\Cost as CostModel;
use app\model\CityActive as CityActiveModel;

class Money
{
    /**
     * 资金变动
     * @param $amount  影响金额
     * @param $type 0:无变化  1：充值 2：提现  3：查询 4：退款
     * @param $mem 备注
     * @param $user_id  用户id
     * @return bool
     */
    public static function moneyChange($amount,$type,$mem,$user_id)
    {
        if($amount==0){
            return false;
        }
        MoneyRecordModel::startTrans();
        try {
            $user_info=UserModel::where(['id'=>$user_id])->find();
            $money_change['money +']=$amount;
            $new_money=$user_info['money']+$amount;
            UserModel::where(['id'=>$user_id])->update($money_change);
            $record_change=[
                "amount"=>$amount,
                "old_money"=>$user_info['money'],
                "money"=>$new_money,
                "type"=>$type,
                "mem"=>$mem,
                "user_id"=>$user_id,
                "create_time"=>time()
            ];
            MoneyRecordModel::insert($record_change);
        }
        catch (\Exception $e) {
            Log::write("变动：".$amount."类型：".$type."备注：".$mem."用户：".$user_id .':' . date('Y-m-d H-i-s'), 'error.log',  'error_money');
            MoneyRecordModel::rollback();
        }
        MoneyRecordModel::endTrans();
        return $new_money;
    }
    /**
     * 缴费与推荐奖励记录
     * @param $amount 缴费/推荐奖励金额
     * @param $type   费用类型：   0：会员缴费 1：推荐奖励
     * @param $pay_id 支付成功的那笔支付id
     * @param $user_id 用户id
     * @param string $mem 备注
     * @param int $invite_id 邀请人
     * @return bool|int
     */
    public static function costChange($amount,$type,$pay_id,$user_id,$mem='',$invite_id=0){
        $insert_id=0;
        if($amount==0){
            return false;
        }
        $cost_info=CostModel::where(['pay_id'=>$pay_id,'user_id'=>$user_id])->find();
        if(empty($cost_info)){
            $record_change=[
                "amount"=>$amount,
                "user_id"=>$user_id,
                "invite_id"=>$invite_id,
                "pay_id"=>$pay_id,
                "mem"=>$mem,
                "type"=>$type,
                "create_time"=>time()
            ];
           $insert_id = CostModel::insert($record_change);
        }
        return $insert_id;
    }
    /**
     * 获取用户升级需要的费用和用户推荐获得奖励费用
     * @param $user_id  用户id
     * @return array
     */
    public static function getLevelMoney($user_id){
        //获取用户所在地区是否有活动
        $field=['u.province_id','u.city_id','u.area_id','ul.level_money','ul.levelyh_money','ul.invite_money','ul.inviteyh_money'];
        $user_info=UserModel::setTable('user u')->field($field)->join('user_level ul', 'u.level_id = ul.id')->where(['u.id'=>$user_id])->find();

        $is_active=0;//用户所在城市是否有活动
        $start_time=0;
        $end_time=0;
        $city_active=CityActiveModel::where(['current_id'=>$user_info['area_id']])->find();
        if(empty($city_active)){
            $city_active1=CityActiveModel::where(['current_id'=>$user_info['city_id']])->find();
            if(empty($city_active1)){
                $city_active2=CityActiveModel::where(['current_id'=>$user_info['province_id']])->find();
                if(!empty($city_active2)){
                    $is_active=1;
                    $start_time=$city_active2['start_time'];
                    $end_time=$city_active2['end_time'];
                }
            }else{
                $is_active=1;
                $start_time=$city_active1['start_time'];
                $end_time=$city_active1['end_time'];
            }
        }else{
            $is_active=1;
            $start_time=$city_active['start_time'];
            $end_time=$city_active['end_time'];
        }
        $amount=$user_info['level_money'];//会员费
        $award=$user_info['invite_money'];//推荐费

        if($is_active){//如果有活动判断是否在活动期间内
            $time=time();
            if($start_time<= $time && $time <=$end_time){
                $amount=$user_info['levelyh_money'];//会员费
                $award=$user_info['inviteyh_money'];//推荐费
            }
        }
       return ['amount'=>$amount,'award'=>$award];

    }
}
