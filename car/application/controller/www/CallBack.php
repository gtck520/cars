<?php

namespace app\controller\www;

use king\lib\Response;
use king\lib\Log;
use app\model\QueryDetail  as QueryDetailModel;
use app\model\QueryOrder  as QueryOrderModel;

class CallBack
{
    //维保回调
    public function maintenance(){
        $result= file_get_contents("php://input");//获取post原始数据

        Log::write($result . ':' . date('Y-m-d H-i-s'), 'maintenance.log',  'callback_query');
        //$result='{"vin":"LSVFD26R1B2722145","orderNo":30963,"result":"{\"url\":\"https:\\\/\\\/ct.kanglan.vip\\\/CallBack\\\/maintenance?orderid=14\",\"content\":{\"orderReportVehicleModel\":{\"cxi\":\"POLO\",\"flag\":true,\"pp\":\"\u5927\u4f17\"},\"orderReportVehicleAccident\":{\"result1\":3},\"orderReportVehicleRepair\":{\"data\":[{\"code\":\"0001\",\"vin\":\"LSVFD26R1B2722145\",\"repairRecordList\":[{\"date\":\"2012-06-22\",\"materal\":\"*D4\u673a\u6cb9\uff01 \uffe5;\u673a\u6cb9\u6ee4\u6e05\u5668;\",\"type\":\"\u9996\u6b21\u4fdd\u517b\",\"content\":\"01\u8868\u68c0\u67e5;02\u8868\u68c0\u67e5;\u9996\u6b217500\u516c\u91cc\u514d\u8d39\u4fdd\u517b;\",\"mileage\":\"5642\"}]}],\"result1\":1},\"orderReportVehicle\":{\"flag\":true,\"suppliers\":[2],\"vin\":\"LSVFD26R1B2722145\"}},\"vin\":\"LSVFD26R1B2722145\",\"orderNo\":30963}"}';
        $req = json_decode($result,true);
        $reqg= G();
        $req['orderid'] =$reqg['orderid'];
        $query_order=QueryOrderModel::where(["id"=>$req['orderid']])->find();
        if($query_order['status']==3||$query_order['status']==4){

            $query_detail=QueryDetailModel::where(["order_id"=>$req['orderid']])->find();
            if(empty($query_detail)){
                $detail_data['maintenance']=$req['result'];
                $detail_data['order_id']=$req['orderid'];
                $detail_data['vin']=$req['vin'];
                $detail_data['add_time']=time();
                QueryDetailModel::insert($detail_data);
            }
            echo '{"result": "SUCCESS"}';
        }else{
            echo '{"result": "FAILD"}';
        }
    }
    //维保回调
    public function collision(){
        $result= file_get_contents("php://input");//获取post原始数据
        Log::write($result . ':' . date('Y-m-d H-i-s'), 'collision.log',  'callback_query');
        //$result='{"vin":"LSVFD26R1B2722145","orderNo":47816,"result":"{\"url\":\"https:\\\/\\\/ct.kanglan.vip\\\/CallBack\\\/collision?orderid=25\",\"content\":{\"orderReportVehicleModel\":{\"cxi\":\"POLO\",\"flag\":true,\"pp\":\"\u5927\u4f17\"},\"orderReportVehicleAccident\":{\"result1\":3},\"orderReportVehicleInsurance\":{\"result1\":2},\"orderReportVehicle\":{\"flag\":true,\"suppliers\":[3],\"vin\":\"LSVFD26R1B2722145\"}},\"vin\":\"LSVFD26R1B2722145\",\"orderNo\":47816}"}';
        $req = json_decode($result,true);
        $reqg= G();
        $req['orderid'] =$reqg['orderid'];
        $query_order=QueryOrderModel::where(["id"=>$req['orderid']])->find();
        if($query_order['status']==3||$query_order['status']==4){

            $query_detail=QueryDetailModel::where(["order_id"=>$req['orderid']])->find();
            if(empty($query_detail)){
                $detail_data['collision']=$req['result'];
                $detail_data['order_id']=$req['orderid'];
                $detail_data['vin']=$req['vin'];
                $detail_data['add_time']=time();
                QueryDetailModel::insert($detail_data);
            }
            echo '{"result": "SUCCESS"}';
        }else{
            echo '{"result": "FAILD"}';
        }
    }
}
