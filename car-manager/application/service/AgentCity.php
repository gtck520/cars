<?php

namespace app\service;

use app\helper\Helper;
use app\model\CityArea as CityAreaModel;
use app\model\AgentCity as AgentCityModel;

class AgentCity
{
    //列表
    public static function getList($req)
    {
        $orderby = ['create_time' => 'desc'];
        $query = AgentCityModel::setTable('agent_city ac')->join('admins a', 'ac.admin_id = a.id');
        if (!empty($req['search'])) {
            $search = $req['search'];
            $query->andWhere(function ($query) use ($search) {
                $query->where('a.name', 'like', '%' . $search . '%');
                $query->whereOr('a.mobile', 'like', '%' . $search . '%');
            });
        }
        $res = $query->field(['a.name','ac.id','ac.agent_city_id','ac.create_time','ac.admin_id'])->orderby($orderby)->page($req['c'], $req['p']);
        foreach ($res['rs'] as $key=>$value){
            $res['rs'][$key]['create_time']=date('Y-m-d H:i:s',$value['create_time']);
            $city_area=CityAreaModel::where(['id'=>$value['agent_city_id']])->find();
            $city_array=explode(",",$city_area['path']);
            $res['rs'][$key]['city_fullname']=$city_area['fullname'];
            $res['rs'][$key]['province_id']=$city_array[0] ?? 0;
            $res['rs'][$key]['city_id']=$city_array[1] ?? 0;
            $res['rs'][$key]['area_id']=$city_array[2] ?? 0;
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
        if($req["admin_id"]<=0){
            return ['code' => 400, 'data' => "必须选择一个用户"];
        }
        $array = [
            'admin_id'=>$req["admin_id"],
            'agent_city_id'=>$current_id,
            'create_time'=>time()
        ];
        $id = AgentCityModel::insert($array);
        Helper::saveToLog($admin_id, '', '', $req["admin_id"], "管理员ID:$admin_id 为代理人 [".$req["admin_id"]."]添加城市权限：$id");
        return ['code' => 200, 'data' => ''];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {
        $current_id=self::getCurrentCity($req);
        if(empty($current_id)){
            return ['code' => 400, 'data' => "您未选择任何城市或省份"];
        }
        if($req["admin_id"]<=0){
            return ['code' => 400, 'data' => "必须选择一个用户"];
        }
        $array = [
            'admin_id'=>$req["admin_id"],
            'agent_city_id'=>$current_id,
            'create_time'=>time()
        ];
        //
        $old_value = AgentCityModel::where(['id' => $id])->find();
        AgentCityModel::where(['id' => $id])->update($array);
        //
        Helper::saveToLog($admin_id, '',json_encode($old_value), json_encode($array), "管理员ID:$admin_id 修改代理人权限:$id 为 ".json_encode($array)."]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = AgentCityModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在'];
        }
        AgentCityModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除代理人权限ID: $id [{$res['agent_city_id']}]");
        return ['code' => 200, 'data' => ''];
    }
    //获取当前选中的城市
    public static function getCurrentCity($req){
        $current_id=0;
        if(!empty($req["province_id"])){
            $current_id=$req["province_id"];
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
