<?php

namespace app\service;

use king\lib\Log;
use app\model\User as UserModel;
use app\model\MoneyRecord as MoneyRecordModel;

class Money
{ 

    //资金变动
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

}
