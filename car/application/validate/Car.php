<?php

namespace app\validate;

use king\lib\Response;
use king\lib\Valid;

class Car extends Common
{
    public static function checkInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('images', 'required|minLength,1', '图片');
        $valid->addRule('chejiahao', 'required|minLength,1', '车架号');
        $valid->addRule('pinpai', 'required|minLength,1', '品牌');
        $valid->addRule('chexing', 'required|minLength,1', '车型');
        $valid->addRule('shangpai_time', 'required|minLength,1', '上牌时间');
        $valid->addRule('price', 'required|minLength,1|int|gt,0', '报价');
        $valid->addRule('biaoxianlicheng', 'required|minLength,1', '表显里程');
        $valid->addRule('biansuxiang', 'required|minLength,1', '变速箱');
        $valid->addRule('pailiang', 'required|minLength,1', '排量');
        $valid->addRule('yanse', 'required|minLength,1', '颜色');
        $valid->addRule('cheliang_type', 'required|minLength,1', '车辆类型');
        $valid->addRule('nianjian_time', 'required|minLength,1', '年检到期');
        $valid->addRule('qiangxian_time', 'required|minLength,1', '强险到期');
        $valid->addRule('cheliangweizhi', 'required|minLength,1', '车辆位置');
        $valid->addRule('weixiujilu', 'required|minLength,1', '维修记录');
        $valid->addRule('pengzhuang', 'required|minLength,1', '碰撞记录');
        $valid->addRule('notes', 'required|minLength,1|maxLength,160', '车辆描述');
        $valid->addRule('zhemgming', 'required|minLength,1', '证明材料');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }

    public static function searchInput($post)
    {
        $valid = Valid::getClass($post);
        $valid->addRule('city_id', 'int', '地区');
        $valid->addRule('biaoxianlicheng', 'gt,0', '表显里程');
        $valid->addRule('colour_id', 'minLength,1', '颜色');
        $valid->addRule('low_price', 'gt,0', '最小价格区间查询');
        $valid->addRule('high_price', 'lt,9999999999999', '最大价格区间查询');
        $valid->addRule('cheliangweizhi', 'int', '车辆位置');
        $valid->addRule('low_age', 'gt,0', ' 最小车龄区间查询');
        $valid->addRule('high_age', 'lt,1000', '最大车龄区间查询');
        $valid->addRule('sort', 'in,1,2,3', '排序');
        if (!$valid->run()) {
            Response::SendResponseJson(400, $valid->getError());
        }
    }
    
}
