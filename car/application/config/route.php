<?php
return [
    //登录
    'post::logins$'         => 'Login/login',
    'post::easylogin$'         => 'Login/easylogin',
    'post::checkToken$'     => 'login/checkToken',
    //注册
    'post::register$'       => 'User/register',
    //用户信息
    'get::userinfo$'       => 'User/userInfo',
     //我的车源
    'get::user/cars$'       => 'User/Cars', 
    //我的出价记录
    'get::user/price$'       => 'User/getPrice',  
    //别人对我的出价记录
    'get::to_user/price$'       => 'User/getToUserPrice',  
    //删除收藏
    'delete::enshrine/(\d+)$'  => 'User/enshrineDel',
    //删除浏览记录
    'delete::browse/(\d+)$'  => 'User/browseDel',
    //删除帮卖
    'delete::bm/(\d+)$'  => 'User/bmDel',
    //充值
    'post::recharge$'   => 'User/recharge',
    //添加电话量
    'post::car/(\d+)/phone$'   => 'User/addPhoneNum',
    //查看其他用户信息
    'get::/(\d+)/info$'       => 'User/getUserInfo',
 
    //门店联想列表
    'get::shops$'        => 'Car/shops',
    //车 
    'get::cars$'        => 'Car/getList',
    'get::cars/name$'   => 'Car/getCarName',
    'post::cars/cache$' => 'Car/setCache',
    'post::cars$'       => 'Car/add',
    'get::cars/(\d+)/info$'        => 'Car/getCarInfo',
    //添加举报
    'post::cars/(\d+)/impeach$'   => 'Car/impeach',
    //添加浏览
    'get::browse$'  => 'Car/getCarBrowseList',
    //添加收藏
    'get::enshrines$'  => 'Car/getCarEnshrinesList',
    //添加帮卖
    'post::car/(\d+)/bm$'  => 'Car/addBM',
    //帮卖列表
    'get::carbm$'  => 'Car/getCarBMList',
    //添加收藏
    'post::cars/(\d+)/enshrines$'   => 'Car/addEnshrines',
    //车辆颜色
    'get::colour$'  => 'Car/getColourList',
    //车源标签
    'get::cheyuan$'  => 'Car/getCheyuanList',
    //类型列表
    'get::cartype$'  => 'Car/getCarType',
    //车辆变速箱列表
    'get::carbs$'  => 'Car/getCarBS',
    //排量列表
    'get::pailiang$'  => 'Car/getCarPL',
    //车辆类型列表
    'get::cheliangleixing$'  => 'Car/getCarCLLX',
    //查看他的车源
    'get::(\d+)/cars$'  => 'Car/getUserCars',
    //编辑车辆
    'put::(\d+)/car$'  => 'Car/edit',
    //门店车源列表
    'get::cars/(\d+)/shop$'       => 'Car/getShopCars',
    //下架车辆
    'post::(\d+)/hidden$'  => 'Car/setHidden',
    //擦亮
    'put::(\d+)/up$'  => 'Car/cl',

    //帮助文本
    'get::text$'        => 'Text/get',

    //查询接口
    'post::query/getpay$' => 'Query/getPay',
    'post::query/maintenance$' => 'Query/maintenance',//维保查询
    'post::query/collision$' => 'Query/collision',//碰撞查询
    'post::query/vehicleCondition$' => 'Query/vehicleCondition',//汽车状态查询
    'post::common/getHpzl$' => 'Common/getHpzl',//号牌种类
    'post::query/regulations$' => 'Query/regulations',//违章查询
    'post::query/smallUnion$' => 'Query/smallUnion',//小综合查询
    'post::query/bigUnion$' => 'Query/bigUnion',//大综合查询
    'post::query/getVin$' => 'Query/vinOcr', //扫码识别车辆vin
    'post::query/getCarInfo$' => 'Query/vinGetinfo', //vin获取车辆信息
    'get::query/getQueryRecord$' => 'Query/getQueryRecord', //获取查询记录
    'get::query/getQueryReport/(\d+)$' => 'Query/getQueryReport', //获取成功的查询报告

    //添加出价
    'post::car/price' => 'User/addPrice',

    //上传图片
    'post::upload/images' => 'U/images',
    //全国城市
    'get::city' => 'Car/getCity',
    //微信支付回调
    'post::pay/wechatNotify$' => 'Pay/wechatNotify',
    //客服发送内容
    'get::(\d+)/sendMsg' => 'User/sendMsg',
];
