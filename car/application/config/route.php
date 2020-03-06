<?php
return [
    //登录
    'post::logins$'         => 'Login/login',
    'post::easylogin$'         => 'Login/easylogin',
    'post::checkToken$'     => 'login/checkToken',
    'post::register$'       => 'User/register',
    'get::userinfo$'       => 'User/userInfo',
    'delete::enshrine/(\d+)$'  => 'User/enshrineDel',
    'delete::browse/(\d+)$'  => 'User/browseDel',

    //车 
    'get::cars$'        => 'Car/getList',
    'get::cars/name$'   => 'Car/getCarName',
    'post::cars/cache$' => 'Car/setCache',
    'post::cars$'       => 'Car/add',
    'get::cars/(\d+)/info$'        => 'Car/getCarInfo',
    'post::cars/(\d+)/impeach$'   => 'Car/impeach',
    'get::browse$'  => 'Car/getCarBrowseList',
    'get::enshrines$'  => 'Car/getCarEnshrinesList',
    //添加收藏
    'post::cars/(\d+)/enshrines$'   => 'Car/addEnshrines',

    //帮助文本
    'get::text$'        => 'Text/get',

    //查询接口
    'post::query/getpay' => 'Query/getPay',
    'post::query/getVin' => 'Query/vinOcr', //扫码识别车辆vin
    'post::query/getCarInfo' => 'Query/vinGetinfo', //vin获取车辆信息

    //添加出价
    'post::car/price' => 'User/addPrice',
];
