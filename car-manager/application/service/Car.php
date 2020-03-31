<?php

namespace app\service;

use app\helper\Helper;
use app\model\User as UserModel;
use app\model\CityArea as CityAreaModel;
use app\model\CarType as CarTypeModel;
use app\model\CarColour as CarColourModel;
use app\model\CarBrowse as CarBrowseModel;
use app\model\CarSc as CarScModel;
use app\model\Car as CarModel;
use app\cache\Car as CarCache;
use app\service\CityActive as CityActiveService;

class Car
{
    //列表
    public static function getList($req)
    {
        $orderby = ['a.create_time' => 'desc', 'a.id' => 'asc'];

        $field = ['a.id','a.status','a.is_hidden','a.chejiahao','a.province_id', 'a.city_id', 'a.area_id', 'a.price', 'a.chexing_id', 'shangpai_time', 'a.biaoxianlicheng', 'a.create_time', 'b.MODEL_NAME', 'b.TYPE_SERIES', 'b.TYPE_NAME',"u.mobile","u.realname"];

        // 关键字搜索
        //CarModel::setDebug();
        $query = CarModel::setTable('car a')->field($field)->join('car_type b', 'a.chexing_id = b.ID')->join('user u', 'a.user_id = u.id');
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

        // 品牌搜索
        if (!empty($req['pinpai']) || isset($req['pinpai'])) {
            $query->where('a.pinpai', '=', $req['pinpai']);
        }

        // 最小价格区间查询
        if (!empty($req['low_price']) || isset($req['low_price'])) {
            $query->where('a.price', '<=', $req['low_price']);
        }

        // 最大价格区间查询
        if (!empty($req['high_price']) || isset($req['high_price'])) {
            $query->where('a.price', '>=', $req['high_price']);
        }


        // 颜色搜索
        if (!empty($req['colour_id']) || isset($req['colour_id'])) {
            $query->where('a.colour_id', '=', $req['colour_id']);
        }

        // 车辆类型搜索
        if (!empty($req['car_type']) || isset($req['car_type'])) {
            $query->where('a.car_type', '=', $req['car_type']);
        }

        // 变速箱搜索
        if (!empty($req['biansu']) || isset($req['biansu'])) {
            $query->where('a.biansu', '=', $req['biansu']);
        }

        // 车辆标签搜索
        if (!empty($req['cheyuan_id']) || isset($req['cheyuan_id'])) {
            $query->where('a.cheyuan_id', '=', $req['cheyuan_id']);
        }

        // 车辆里程搜索
        if (!empty($req['licheng']) || isset($req['licheng'])) {
            $query->where('a.biaoxianlicheng', '=', $req['licheng']);
        }

        // 最小车龄区间查询
        if (!empty($req['low_age']) || isset($req['low_age'])) {
            $query->where('a.age', '<=', $req['low_age']);
        }

        // 最大车龄区间查询
        if (!empty($req['high_age']) || isset($req['high_age'])) {
            $query->where('a.age', '>=', $req['high_age']);
        }

        // 排序
        if (!empty($req['sort']) || isset($req['sort'])) {
            switch ($req['sort']) {
                case '1':
                    // 价格最低
                    $orderby = ['a.price' => 'asc', 'a.id' => 'asc'];
                    break;
                case '2':
                    // 价格最高
                    $orderby = ['a.price' => 'desc', 'a.id' => 'asc'];
                    break;
                case '3':
                    // 最新发布
                    $orderby = ['a.create_time' => 'desc', 'a.id' => 'asc'];
                    break;
            }
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

    //车辆详情
    public static function getCarInfo($car_id)
    {
        $car_info = CarModel::setTable('car a')->field(['b.MODEL_NAME','b.TYPE_SERIES','b.TYPE_NAME','u.realname','a.*'])->join('car_type b', 'a.chexing_id = b.ID')->join('user u', 'a.user_id = u.id')->where(["a.id"=>$car_id])->find();

        $car_info = Helper::formatTimt($car_info, ['shangpai_time', 'nianjiandaoqi', 'qiangxiandaoqi', 'create_time']);
        $car_info['yanse'] = CarColourModel::where(['id' => $car_info['yanse_id']])->value('name');
        $car_info['biaoxianlicheng'] = $car_info['biaoxianlicheng'] . '万公里';
        $current_id=CityActiveService::getCurrentCity($car_info);
        $car_info['city_name'] = CityAreaModel::where(['id' => $current_id])->value(['fullname']);
        $car_info['title'] = "{$car_info['MODEL_NAME']} {$car_info['TYPE_SERIES']} {$car_info['TYPE_NAME']}";
        $car_info['chexing'] = CarTypeModel::field(['MODEL_NAME', 'ENGINE_CAPACITY'])->where(['ID' => $car_info['chexing_id']])->find();
        unset($car_info['yanse_id'], $car_info['status'], $car_info['cheixng_id'], $car_info['id']);

        return ['code' => 200, 'data' => $car_info];
    }

    //修改状态
    public static function modifyStatus($admin_id,$id, $req)
    {
        $where=[];
        if (isset($req['is_hidden'])) {
            $where['is_hidden']=$req['is_hidden'];
        }
        if (isset($req['status'])) {
            $where['status']=$req['status'];
        }
        if(empty($where)){
            return ['code' => 400, 'data' => "未做任何修改"];
        }
        //
        $old_value = CarModel::field(["is_hidden","status"])->where(['id' => $id])->find();
        CarModel::where(['id' => $id])->update($where);
        //
        $where['status']=$where['status'] ?? '未修改';
        $where['is_hidden']=$where['is_hidden'] ?? '未修改';
        Helper::saveToLog($admin_id, '',"下架状态：".$old_value['is_hidden']."审核状态：".$old_value['status'], "下架状态：".$where['is_hidden']."审核状态：".$where['status'], "管理员ID:$admin_id 修改车辆颜色ID:$id [下架状态：".$where['is_hidden']."审核状态".$where['status']."]");
        CarCache::setCarInfo($id);
        return ['code' => 200, 'data' => ''];
    }

}
