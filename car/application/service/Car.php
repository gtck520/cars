<?php

namespace app\service;

use app\helper\Helper;
use king\lib\Jwt;
use app\cache\Car as CarCache;
use app\model\Car as CarModel;
use app\model\City as CityModel;
use app\model\User as UserModel;
use app\model\CarSc as CarScModel;
use app\model\CarBm  as CarBMModel;
use app\model\CarType as CarTypeModel;
use app\model\Impeach as ImpeachModel;
use app\model\CarColour as CarColourModel;
use app\model\CarBrowse as CarBrowseModel;
use app\model\CarCheYuan as CarCheYuanModel;

class Car
{
    private static $impeach_type = [
        '1' => '非真实车源',
        '2' => '车源已售',
        '3' => '车源描述信息有误',
        '4' => '其他',
    ];

    /**
     * 首页搜索   有bug 待修复...
     */
    public static function getList($user_id, $req)
    {
        $car_num = 8;
        $is_bu = true;
        $user_info = UserModel::field(['province_id','area_id', 'shop_id', 'quan_guo', 'sheng_ji'])->where(['id' => $user_id])->find();
        $orderby = ['a.create_time' => 'desc', 'a.id' => 'asc'];

        $field = ['a.id', 'a.area_id', 'a.price', 'a.chexing_id', 'shangpai_time', 'a.biaoxianlicheng', 'a.create_time', 'a.shop_id', 'b.MODEL_NAME', 'b.TYPE_SERIES', 'b.TYPE_NAME'];

        // 关键字搜索
        $query = CarModel::setTable('car a')->join('car_type b', 'a.chexing_id = b.ID')->field($field);

        // 城市搜索
        if (!empty($req['city_id']) && isset($req['city_id'])) {
            $city_level = CityModel::setTable('city_area')->where('id', '=', $req['city_id'])->value('level');

            switch ($city_level) {
                case '2':
                    if ($user_info['sheng_ji'] == '0') {
                        return ['code' => 400, 'data' => '您没有查询省内城市的权限!'];
                    }else{
                        $query->where('a.city_id', '=', $req['city_id']);
                    }
                    break;
                case '1':
                    if ($user_info['quan_guo'] == '0') {
                        return ['code' => 400, 'data' => '您没有查询各省的权限!'];
                    }else{
                        $query->where('a.province_id', '=', $req['city_id']);
                    }
                    break;
                case '3':
                    $query->where('a.area_id', '=', $req['city_id']);
                    break;
            }
        }else{
            $query->where('a.area_id', '=', $user_info['area_id']);
        }
        
        if (isset($req['search']) && !empty($req['search'])) {
            $is_bu = false;
            $search = $req['search'];
            $query->andWhere(function ($query) use ($search) {
                $query->where('b.MODEL_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.MODEL_NAME', 'like', '%' . $search . '%');
                $query->whereOr('b.TYPE_SERIES', 'like', '%' . $search . '%');
                $query->whereOr('b.VEHICLE_CLASS', 'like', '%' . $search . '%');
                $query->whereOr('b.TRANSMISSION', 'like', '%' . $search . '%');
            });
        }

        // 品牌搜索
        if (!empty($req['pinpai']) && isset($req['pinpai'])) {
            $is_bu = false;
            $query->where('a.pinpai', '=', $req['pinpai']);
        }

        // 最小价格区间查询
        if (!empty($req['low_price']) && isset($req['low_price'])) {
            $is_bu = false;
            $query->where('a.price', '<=', $req['low_price']);
        }

        // 最大价格区间查询
        if (!empty($req['high_price']) && isset($req['high_price'])) {
            $is_bu = false;
            $query->where('a.price', '>=', $req['high_price']);
        }


        // 颜色搜索
        if (!empty($req['colour_id']) && isset($req['colour_id'])) {
            $is_bu = false;
            $query->where('a.yanse_id', '=', $req['colour_id']);
        }

        // 车辆类型搜索
        if (!empty($req['car_type']) && isset($req['car_type'])) {
            $is_bu = false;
            $query->where('a.type_name', '=', $req['car_type']);
        }

        // 变速箱搜索
        if (!empty($req['biansu']) && isset($req['biansu'])) {
            $is_bu = false;
            $query->where('a.biansu', '=', $req['biansu']);
        }

        // 车辆标签搜索
        if (!empty($req['cheyuan_id']) && isset($req['cheyuan_id'])) {
            $is_bu = false;
            $query->where('a.cheyuan_id', '=', $req['cheyuan_id']);
        }

        // 车辆里程搜索
        if (!empty($req['licheng']) && isset($req['licheng'])) {
            $is_bu = false;
            $query->where('a.biaoxianlicheng', '=', $req['licheng']);
        }

        // 最小车龄区间查询
        if (!empty($req['low_age']) && isset($req['low_age'])) {
            $is_bu = false;
            $query->where('a.age', '<=', $req['low_age']);
        }

        // 最大车龄区间查询
        if (!empty($req['high_age']) && isset($req['high_age'])) {
            $is_bu = false;
            $query->where('a.age', '>=', $req['high_age']);
        }

        // 排序
        if (!empty($req['sort']) && isset($req['sort'])) {
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
        //
        $car_list = $query->where('a.status','=',1)->orderby($orderby)->page($req['c'], $req['p']);
        if ($car_list['total'] > 0) {
            if ($is_bu) {
                if ($car_list['total'] < $car_num) {
                    //现有车辆
                    $car_id_arr = array_column($car_list['rs'], 'id');
                    // $city_arr =  array_column(CityModel::where(['pid' => CityModel::where(['id' => $user_info['area_id']])->value('pid')])->get(), 'id') ;

                    $car_list['rs'] =   array_merge($car_list['rs'], CarModel::setTable('car a')->field($field)->join('car_type b', 'a.chexing_id = b.ID')->where('a.id', 'not in', $car_id_arr)->where('a.province_id', '=', $user_info['province_id'])->limit(0, $car_num - $car_list['total'])->get()) ;
                    $car_list_count = count($car_list['rs']);
                    //还不够
                    if ($car_list_count < $car_num) {
                        $car_id_arr_h = array_column($car_list['rs'], 'id');
                        $car_list['rs'] =   array_merge($car_list['rs'], CarModel::setTable('car a')->field($field)->join('car_type b', 'a.chexing_id = b.ID')->where('a.id', 'not in', $car_id_arr_h)->limit(0, $car_num - $car_list_count)->get());
                    }
                }
            }
            
            $same_shop_car = [];
            foreach ($car_list['rs'] as $key =>&$value) {
                //格式化返回
                $shop_id = $user_info['shop_id'];
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
                if ($value['shop_id'] == $shop_id) {
                    $same_shop_car[] = $value;
                    unset($car_list['rs'][$key]);
                }
            }
            //发现相同门店的置顶
            $car_list['rs'] = array_merge($same_shop_car,$car_list['rs'] );
        }

        return ['code' => 200, 'data' => $car_list];
    }

    //所有车辆名称
    public static function getCarName()
    {
        $car_name = CarCache::getCarName();
        return ['code' => 200, 'data' => $car_name];
    }

    //所有类型
    public static function getCarType($req)
    {
        $pinpai = $req['pinpai'] ?? '';
        if (empty($pinpai)) {
            $car_name = CarCache::getCarType();
        }else{
            $car_name = CarTypeModel::field(['distinct VEHICLE_CLASS'])->where(['MAKE_NAME' => $pinpai])->get();
        }

        
        return ['code' => 200, 'data' => $car_name];
    }

    //所有变速箱
    public static function getCarBS($req)
    {
        $pinpai = $req['pinpai'] ?? '';
        if (empty($pinpai)) {
            $car_name = CarCache::getCarBS();
        }else{
            $car_name = CarTypeModel::field(['distinct TRANSMISSION'])->where(['MAKE_NAME' => $pinpai])->get();
        }
        
        return ['code' => 200, 'data' => $car_name];
    }

    //所有车辆排量
    public static function getCarPL($req)
    {
        $pinpai = $req['pinpai'] ?? '';
        if (empty($pinpai)) {
            $car_name = CarCache::getCarPL();
        }else{
            $car_name = CarTypeModel::field(['distinct ENGINE_CAPACITY'])->where(['MAKE_NAME' => $pinpai])->get();
        }
        
        return ['code' => 200, 'data' => $car_name];
    }

    //所有车辆类型
    public static function getCarCLLX($req)
     {
        $pinpai = $req['pinpai'] ?? '';
        if (empty($pinpai)) {
            $car_name = CarCache::getCarCLLX();
        }else{
            $car_name = CarTypeModel::field(['distinct MODEL_NAME'])->where(['MAKE_NAME' => $pinpai])->get();
        }
         
         return ['code' => 200, 'data' => $car_name];
     }

    //更新缓存
    public static function setCache()
    {
        CarCache::setCarName();
        CarCache::setCarType();
        CarCache::setCarBS();
        return ['code' => 200, 'data' => '更新成功!'];
    }

    //车辆详情
    public static function getCarInfo($user_id, $car_id)
    {
        //添加浏览记录
        $user_browse = CarBrowseModel::where(['user_id' =>  $user_id, 'car_id' => $car_id])->find();
        if (!$user_browse) {
            CarBrowseModel::insert([
                'user_id' => $user_id,
                'car_id'  => $car_id,
                'create_time' => time()
            ]);
        }
        
        CarModel::where(['id' => $car_id])->update(['liulan_num +' => 1]);
        $car_info = CarModel::where(['id' => $car_id, 'status' => 1])->find();
        $car_info = Helper::formatTimt($car_info, ['shangpai_time', 'nianjiandaoqi', 'qiangxiandaoqi', 'create_time']);
        $car_info['yanse'] = CarColourModel::where(['id' => $car_info['yanse_id']])->value('name');
        $car_info['biaoxianlicheng'] = $car_info['biaoxianlicheng'] . '万公里';
        $car_info['city_name'] = CityModel::where(['id' => $car_info['area_id']])->value(['fullname']);
        $car_info['realname'] = UserModel::where(['id' => $car_info['user_id']])->value(['realname']);
        $car_info['realname'] = Helper::encryptName($car_info['realname']);
        $car_info['chexing'] = CarTypeModel::field(['MODEL_NAME', 'ENGINE_CAPACITY'])->where(['ID' => $car_info['chexing_id']])->find();
        unset($car_info['yanse_id'], $car_info['status'], $car_info['cheixng_id'], $car_info['id']);

        return ['code' => 200, 'data' => $car_info];
    }



    //举报
    public static function impeach($user_id, $car_id, $type_id)
    {
        $res = ImpeachModel::where(['user_id' => $user_id, 'car_id' => $car_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => '您已经投诉过该车辆, 正在处理中!'];
        } else {
            ImpeachModel::insert([
                'user_id' => $user_id,
                'car_id' => $car_id,
                'notes' => self::$impeach_type[$type_id],
                'create_time' => time(),
            ]);

            return ['code' => 200, 'data' => ''];
        }
    }

    // 发布车
    public static function add($user_id, $req)
    {
        $city_res = CityModel::where(['id' => $req['city_id']])->find();
        if (!$city_res) {
            return ['code' => 400, 'data' => '没有查询到这个城市!'];
        }

        if ($city_res['level'] == 1 || $city_res['level'] == 2) {
            return ['code' => 400, 'data' => '城市参数错误!'];
        }
        
        $yanse_res = CarColourModel::where(['id' => $req['yanse_id']])->find();
        if (!$yanse_res) {
            return ['code' => 400, 'data' => '没有查询到这个颜色!'];
        }

        if (!is_numeric($req['price'])) {
            return ['code' => 400, 'data' => '价格非法!'];
        }
        //车龄
        $age = Helper::birthday2($req['shangpai_time']);
        //省级县id
        $city_ids = explode(',', $city_res['path']) ;

        CityModel::insert([
            'user_id' => $user_id,
            'province_id' =>  $city_ids[0],
            'city_id' => $city_ids[1],
            'area_id' => $city_ids[2],
            'chejiahao' => $req['chejiahao'],
            'pinpai'=>$req['pinpai'],
            'chexing_id'=> 1,
            'shangpai_time'=>$req['shangpai_time'],
            'price'=>$req['price'],
            'biaoxianlicheng'=>$req['biaoxianlicheng'],
            'yanse_id'=>$req['yanse'],
            'nianjiandaoqi'=>$req['nianjian_time'],
            'qiangxiandaoqi'=>$req['qiangxian_time'],
            'weixiujilu'=>$req['weixiujilu'],
            'pengzhuangjilu'=>$req['pengzhuang'],
            'notes'=>$req['notes'],
            'images_url'=>$req['images'],
            'status'=> 0,
            'is_hidden' => 0,
            'create_time' => time(),
            'age' =>$age,
            'biansu' => $req['biansuxiang'],
            'cheyuan_id' =>$req['cheyuan_id'],
            'zhengming' => $req['zhemgming'],
        ]);

        //更新各种列表缓存
        self::setCache();
        return ['code' => 200, 'data' => ''];
    }

    //浏览记录列表
    public static function  getCarBrowseList($user_id, $req)
    {
        $orderby = ['a.create_time' => 'desc'];
        
        $field = ['a.create_time','b.id', 'b.price', 'b.chexing_id', 'b.biaoxianlicheng', 'b.shangpai_time', 'b.area_id', 'b.status', 'c.MODEL_NAME', 'c.TYPE_SERIES', 'c.TYPE_NAME'];

        $car_list = CarModel::setTable('car_browse a')->join('car b', 'a.car_id = b.id')->join('car_type c', 'b.chexing_id = c.ID')->field($field)->where('a.user_id', '=', $user_id)->orderby($orderby)->get();

        if ($car_list) {
            foreach ($car_list as &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
            }
        }
        return ['code' => 200, 'data' => $car_list];
    }

     //收藏记录列表
     public static function  getCarEnshrinesList($user_id, $req)
     {
        $orderby = ['a.create_time' => 'desc'];
        
        $field = ['a.create_time','b.id', 'b.price', 'b.chexing_id', 'b.biaoxianlicheng', 'b.shangpai_time', 'b.area_id', 'b.status', 'c.MODEL_NAME', 'c.TYPE_SERIES', 'c.TYPE_NAME'];

        $car_list = CarModel::setTable('car_sc a')->join('car b', 'a.car_id = b.id')->join('car_type c', 'b.chexing_id = c.ID')->field($field)->where('a.user_id', '=', $user_id)->orderby($orderby)->get();

        if ($car_list) {
            foreach ($car_list as &$value) {
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
                $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
                $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
                unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
            }
        }
        return ['code' => 200, 'data' => $car_list];
     }

     // 添加收藏
    public static function addEnshrines($user_id, $car_id)
    {
        $res = CarScModel::where(['user_id' => $user_id, 'car_id' => $car_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => '您已经收藏过了!'];
        } else {
            CarScModel::insert([
                'user_id' => $user_id,
                'car_id' => $car_id,
                'create_time' => time(),
            ]);

            return ['code' => 200, 'data' => ''];
        }
    }

    //车辆颜色
    public static function getColourList()
    {
        return CarColourModel::get();
    }

    //车辆颜色
    public static function getCheyuanList()
    {
        return CarCheYuanModel::get();
    }
    
    // 添加帮卖
    public static function addBM($user_id, $car_id)
    {
        $res = CarBMModel::where(['user_id' => $user_id, 'car_id' => $car_id])->find();
        if ($res) {
            return ['code' => 400, 'data' => '您已经帮卖过了!'];
        } else {
            CarBMModel::insert([
                'user_id' => $user_id,
                'car_id' => $car_id,
                'create_time' => time(),
            ]);

            return ['code' => 200, 'data' => ''];
        }
    }

    //帮卖记录列表
    public static function  getCarBMList($user_id, $req)
    {
    $orderby = ['a.create_time' => 'desc'];
    
    $field = ['a.create_time','b.id', 'b.price', 'b.chexing_id', 'b.biaoxianlicheng', 'b.shangpai_time', 'b.area_id', 'b.status', 'c.MODEL_NAME', 'c.TYPE_SERIES', 'c.TYPE_NAME'];

    $car_list = CarModel::setTable('car_bm a')->join('car b', 'a.car_id = b.id')->join('car_type c', 'b.chexing_id = c.ID')->field($field)->where('a.user_id', '=', $user_id)->orderby($orderby)->get();

    if ($car_list) {
        foreach ($car_list as &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $value['city_name'] = CityModel::where(['id' => $value['area_id']])->value(['name']);
            $value['title'] = "{$value['MODEL_NAME']} {$value['TYPE_SERIES']} {$value['TYPE_NAME']}";
            $value['biaoxianlicheng'] = date('Y', $value['shangpai_time']) . "年/{$value['biaoxianlicheng']}万公里";
            unset($value['chexing_id'], $value['area_id'], $value['shangpai_time'], $value['MODEL_NAME'], $value['TYPE_SERIES'], $value['TYPE_NAME']);
        }
    }
    return ['code' => 200, 'data' => $car_list];
    }

    //编辑车辆
    public static function edit($user_id, $req)
    {
        
    }
}
