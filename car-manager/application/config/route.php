<?php
return [
    //登录
    'post::logins$'         => 'Login/login',
    'post::checkToken$'     => 'Login/checkToken',

    //管理员
    'get::admins$'          => 'Admin/getList',
    'post::admins$'         => 'Admin/add',
    'put::admins/(\d+)$'    => 'Admin/modify',
    'delete::admins/(\d+)$' => 'Admin/delete',

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

    //用户管理
    'get::users$'          => 'User/getList',
    'get::user/(\d+)$'    => 'User/getUserInfo',
    'put::user/(\d+)$'    => 'User/modify',
    'delete::user/(\d+)$' => 'User/delete',
    //会员等级相关规则
    'get::levels'          => 'User/getLevelList',
    'put::level/(\d+)$'    => 'User/updateLevel',

    //车辆管理
    'get::cars$'          => 'Car/getList',
    'get::car/(\d+)$'    => 'Car/getCarInfo',
    'put::car/(\d+)$'    => 'Car/modify',
    'put::car/updateStatus/(\d+)$'    => 'Car/updateStatus',
    'delete::car/(\d+)$' => 'Car/delete',

    //各项设置
    'post::cityarea$'         => 'Set/getCity',

    //城市活动
    'get::cityactives$'          => 'CityActive/getList',
    'post::cityactive$'    => 'CityActive/add',
    'put::cityactive/(\d+)$'    => 'CityActive/modify',
    'delete::cityactive/(\d+)$' => 'CityActive/delete',

];
