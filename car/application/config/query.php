<?php

use king\lib\Env;

return [
    'regulations_key' => Env::get('querykey.regulations'),
    'other_key' => Env::get('querykey.other'),
    'regulations_url' => Env::get('queryurl.regulations'),
    'maintenance_url' => Env::get('queryurl.maintenance'),
    'collision_url' => Env::get('queryurl.collision'),
    'vehicleCondition_url' => Env::get('queryurl.vehicleCondition'),

    'appkey' => Env::get('vin.appkey'),
    'appsecret' => Env::get('vin.appsecret'),
    'ocr_http' => Env::get('vin.ocr_http'),
    'ocr_mix' => Env::get('vin.ocr_mix'),
];