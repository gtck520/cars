<?php

namespace app\controller\www;

use king\lib\Response;
use king\lib\Log;
use app\model\QueryDetail  as QueryDetailModel;

class CallBack
{
    //维保回调
    public function maintenance(){
        $result= file_get_contents("php://input");//获取post原始数据
        $reqg= G();
        //Common::checkVin($req);
        $req['orderid'] =$reqg['orderid'];
        Log::write($result . ':' . date('Y-m-d H-i-s'), 'maintenance.log',  'callback_query');
//        if($req['code']==888){
//            $detail_data['order_id']=$req['orderid'];
//            $detail_data['vin']=$req['vin'];
//            $detail_data['add_time']=time();
//           // QueryDetailModel::insert($detail_data);
//        }
    } 
}
