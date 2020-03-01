<?php

use king\lib\Env;

return [
    'domain' => Env::get('app.domain'),
    'default_folder' => 'www',
    'default_page' => 'index',
    'cache_driver' => 'redis',
    'suffix' => '.html',
    'auto_xss' => true,
    'error_file' => Env::get('app.error_file'),
    'log_error' => Env::get('app.log_error'),
    'use_composer'  => true,
    'only_route' => false,
    'timezone' => 'PRC',

    //小程序
    'app_id' => Env::get('wechat.app_id'),
    'secret' =>Env::get('wechat.secret'),
];
