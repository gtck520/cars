<?php
return [
    //登录
    'post::logins$'         => 'Login/login',
    'post::checkToken$'     => 'login/checkToken',
    'post::register$'       => 'User/register',
    'get::userinfo$'       => 'User/userInfo',

    //车 
    'get::cars$'        => 'Car/getList',
    'get::cars/name$'   => 'Car/getCarName',
    'post::cars/cache$' => 'Car/setCache',
    'post::cars$'       => 'Car/add',
    'get::cars/(\d+)/info$'        => 'Car/getCarInfo',
    'post::cars/(\d+)/impeach$'   => 'Car/impeach',

    //帮助文本
    'get::text$'        => 'Text/get',

    //查询接口
    'post::query/getpay' => 'Query/getPay',

];
