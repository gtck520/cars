<?php

namespace app\service;

use app\helper\Helper;
use app\model\CityActive as CityActiveModel;
use app\model\CityArea as CityAreaModel;

class CityActive
{
    //列表
    public static function getList($req)
    {
        $orderby = ['add_time' => 'desc'];
        $res = CityActiveModel::orderby($orderby)->page($req['c'], $req['p']);
        foreach ($res['rs'] as $key=>$value){
            $res['rs'][$key]['start_time']=date('Y-m-d H:i:s',$value['start_time']);
            $res['rs'][$key]['end_time']=date('Y-m-d H:i:s',$value['end_time']);
            $res['rs'][$key]['add_time']=date('Y-m-d H:i:s',$value['add_time']);
            $city_area=CityAreaModel::where(['id'=>$value['current_id']])->find();
            $res['rs'][$key]['city_fullname']=$city_area['fullname'];
        }
        return ['code' => 200, 'data' => $res];
    }

    //添加
    public static function add($admin_id, $req)
    {
        $current_id=self::getCurrentCity($req);
        if(empty($current_id)){
            return ['code' => 400, 'data' => "您未选择任何城市或省份"];
        }
        if($req["start_time"]<=0){
            return ['code' => 400, 'data' => "开始日期不对"];
        }
        if($req["end_time"]<=0){
            return ['code' => 400, 'data' => "结束日期不对"];
        }
        $array = [
            'current_id'=>$current_id,
            'pronvice_id'=>$req["pronvice_id"],
            'city_id'=>$req["city_id"],
            'area_id'=>$req["area_id"],
            'start_time'=>$req["start_time"],
            'end_time'=>$req["end_time"],
            'add_time'=>time()
        ];
        $res = CityActiveModel::where(['current_id' =>$current_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "该城市已添加"];
        }
        $id = CityActiveModel::insert($array);
        Helper::saveToLog($admin_id, '', '', $current_id, "管理员ID:$id 添加城市活动 [".$current_id."]");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $current_id=self::getCurrentCity($req);
        if(empty($current_id)){
            return ['code' => 400, 'data' => "您未选择任何城市或省份"];
        }
        if($req["start_time"]<=0){
            return ['code' => 400, 'data' => "开始日期不对"];
        }
        if($req["end_time"]<=0){
            return ['code' => 400, 'data' => "结束日期不对"];
        }
        $array = [
            'current_id'=>$current_id,
            'pronvice_id'=>$req["pronvice_id"],
            'city_id'=>$req["city_id"],
            'area_id'=>$req["area_id"],
            'start_time'=>$req["start_time"],
            'end_time'=>$req["end_time"],
            'add_time'=>time()
        ];

        $res = CityActiveModel::where(['current_id' =>$current_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => "该城市已添加"];
        }
        //
        $old_value = CityActiveModel::where(['id' => $id])->find();
        CityActiveModel::where(['id' => $id])->update($array);
        //

        Helper::saveToLog($admin_id, '',json_encode($old_value), json_encode($array), "管理员ID:$admin_id 修改城市活动:$id 为 ".json_encode($array)."]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = CityActiveModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此活动'];
        }
        CityActiveModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除城市活动ID: $id [{$res['name']}]");
        return ['code' => 200, 'data' => ''];
    }
    //获取当前选中的城市
    public static function getCurrentCity($req){
        if(!empty($req["pronvice_id"])){
            $current_id=$req["pronvice_id"];
        }
        if(!empty($req["city_id"])){
            $current_id=$req["city_id"];
        }
        if(!empty($req["area_id"])){
            $current_id=$req["area_id"];
        }
        return $current_id;
    }
}
