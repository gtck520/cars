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
    'crypt_key'   => '%&()((*_*)(&^fmmfgmf',
    'log_error' => Env::get('app.log_error'),
    'use_composer'  => true,
    'only_route' => false,
    'timezone' => 'PRC',
    'password' => Env::get('settings.password'),
    'es'  => Env::get('app.es'),
];
