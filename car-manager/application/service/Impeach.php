<?php

namespace app\service;

use app\helper\Helper;
use app\model\Car as CarModel;
use app\model\Impeach as ImpeachModel;
use app\model\CityArea as CityAreaModel;
use app\service\CityActive as CityActiveService;

class Impeach
{
    //列表
    public static function getList($req)
    {
        $orderby = ['i.create_time' => 'desc', 'i.id' => 'asc'];
        $field = ['i.status','i.notes','i.create_time','i.id','i.car_id','a.is_hidden','a.chejiahao','a.province_id', 'a.city_id', 'a.area_id', 'a.price', 'a.chexing_id', 'shangpai_time', 'a.biaoxianlicheng', 'b.MODEL_NAME', 'b.TYPE_SERIES', 'b.TYPE_NAME',"u.mobile","u.realname"];

        // 关键字搜索
        //CarModel::setDebug();
        $query = ImpeachModel::setTable('impeach i')->field($field)->join('car a','i.car_id=a.id')->join('car_type b', 'a.chexing_id = b.ID')->join('user u', 'a.user_id = u.id');
        if (!empty($req['search'])) {
            $search = $req['search'];
            $query->andWhere(function ($query) use ($search) {
                $query->where('b.MODEL_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.MODEL_NAME', 'like', '%' . $search . '%');
                $query->whereOr('b.TYPE_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.VEHICLE_CLASS', 'like', '%' . $search . '%');
                $query->whereOr('b.TRANSMISSION', 'like', '%' . $search . '%');
                $query->whereOr('b.TYPE_NAME', 'like', '%' . $search . '%');
            });
        }
        //发布人人号码
        if (!empty($req['mobile'])) {
            $query->where('u.mobile', '=', $req['mobile']);
        }

        // 城市筛选
        if (!empty($req['province_id'])) {
            $query->where('a.province_id', '=', $req['province_id']);
        }
        if (!empty($req['city_id']) ) {
            $query->where('a.city_id', '=', $req['city_id']);
        }
        if (!empty($req['area_id'])) {
            $query->where('a.area_id', '=', $req['area_id']);
        }
        $car_list = $query->orderby($orderby)->page($req['c'], $req['p']);
        if ($car_list['total'] > 0) {
            foreach ($car_list['rs'] as &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $current_id=CityActiveService::getCurrentCity($value);
                $value['city_name'] = CityAreaModel::where(['id' => $current_id])->value(['fullname']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['cheliangweizhi'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
            }
        }

        return ['code' => 200, 'data' => $car_list];
    }

    //修改
    public static function modify($admin_id, $id, $req)
    {

        $old_value = ImpeachModel::field(['notes','status'])->where(['id' => $id])->find();
        ImpeachModel::where(['id' => $id])->update([
            'notes' => $req['notes'],
            'status' => $req['status'],
        ]);
        //
        $new_value=json_encode($req);
        Helper::saveToLog($admin_id, '',$old_value['notes'], $req['notes'], "管理员ID:$admin_id 处理举报ID:$id [{$old_value['notes']} 为 $new_value]");
        return ['code' => 200, 'data' => ''];
    }

    //删除
    public static function delete($admin_id, $id)
    {
        $res = ImpeachModel::where(['id' => $id])->find();
        if (!$res) {
            return ['code' => 400, 'data' => '不存在此角色'];
        }
        ImpeachModel::delete(['id' => $id]);
        Helper::saveToLog($admin_id, '', '', '', "管理员ID:$admin_id 删除车辆颜色ID: $id [{$res['name']}]");
        return ['code' => 200, 'data' => ''];
    }
}
