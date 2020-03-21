<?php

namespace app\service;

use app\cache\Token;
use app\model\Car as CarModel;
use app\model\User as UserModel;
use app\helper\Helper;

class Index
{

    public static function getIndex()
    {
        $user_total=UserModel::field(['count(*) as total'])->find();
        $user_level_total=UserModel::where(['level_id'=>1])->field(['count(*) as total'])->find();
        $car_total=CarModel::field(['count(*) as total'])->find();
        $return_data['user_total']=$user_total['total'];
        $return_data['user_level_total']=$user_level_total['total'];
        $return_data['car_total']=$car_total['total'];
        return ['code' => 200, 'data' => $return_data];
    }




}
