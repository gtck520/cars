<?php

use king\lib\Env;

return [
    'redis' => [
        'host' => Env::get('cache.host'),
        'port' => Env::get('cache.port'),
        'password' => Env::get('cache.password'),
        'db' => Env::get('cache.db'),
    ]
];