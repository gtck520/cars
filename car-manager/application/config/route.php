<?php
return [
    //登录
    'post::logins$'         => 'login/login',
    'post::checkToken$'     => 'login/checkToken',

    //管理员
    'get::admins$'          => 'admin/getList',
    'post::admins$'         => 'admin/add',
    'put::admins/(\d+)$'    => 'admin/modify',
    'delete::admins/(\d+)$' => 'admin/delete',

    //权限管理
    'get::manPower$'          => 'ManPower/getList',
    'post::manPower$'         => 'ManPower/add',
    'put::manPower/(\d+)$'    => 'ManPower/modify',
    'delete::manPower/(\d+)$' => 'ManPower/delete',

    //管理组管理
    'get::group$'          => 'Group/getList',
    'post::group$'         => 'Group/add',
    'put::group/(\d+)$'    => 'Group/modify',
    'delete::group/(\d+)$' => 'Group/delete',  

    //帮助文本管理
    'get::text$'          => 'Text/get',
    'put::text$'          => 'Text/update',

    //车辆颜色管理
    'get::cars/colour$'          => 'CarColour/getList',
    'post::cars/colour$'         => 'CarColour/add',
    'put::cars/(\d+)/colour$'    => 'CarColour/modify',
    'delete::cars/(\d+)/colour$' => 'CarColour/delete',

    //举报管理
    'get::impeachs$'          => 'Impeach/getList',
    'post::impeachs$'         => 'Impeach/add',
    'put::impeach/(\d+)$'    => 'Impeach/modify',
    'delete::impeach/(\d+)$' => 'Impeach/delete',
];
