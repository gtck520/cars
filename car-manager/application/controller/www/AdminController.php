<?php

namespace app\controller\www;

use king\lib\Request;
use king\lib\Response;
use app\cache\Token;
use app\model\Admin as ManAdminModel;
use app\model\ManPower as ManPowerModel;
use app\model\ManRole as ManRoleModel;
use app\model\AgentCity as AgentCityModel;
use app\model\CityArea as CityAreaModel;
use king\core\Route;

class AdminController
{
    //当前管理员id
    protected static $admin_id;

    //构造方法 验证登录状态
    public function __construct()
    {
        if (!self::isLogin()) {
            Response::SendResponseJson(401, '未登录');
        }
        self::chenkAuth(Route::pregUrl(S(1)));
    }

    //是否登录 和 权限验证
    public static function isLogin()
    {
        $admin_id = self::loginId();
        return is_numeric($admin_id);
    }

    //当前管理员登录ID
    public static function loginId()
    {
        $token = Request::header('Authorization');
        if (!$token || empty($token)) return '';
        //本地缓存中获取
        $admin_id = Token::get($token);
        if (is_numeric($admin_id)) {
            self::$admin_id = $admin_id;
            return $admin_id;
        }
        return '';
    }
    //验证后台管理权限
    protected static function chenkAuth($param)
    {
        //获取类名与方法
        $matchs = explode('/', $param);
        $where = ['id' => self::$admin_id];
        $adminArr = ManAdminModel::where($where)->find();
        if (empty($adminArr)) {
            // 判断是否为合法数据
            Response::SendResponseJson(401, '用户不存在或登录已过期');
        }
        // 对角色进行管理
        // 得到对应的角色以及权限
        $roleArr = ManRoleModel::find($adminArr['rid']);
        if (empty($roleArr)) {
            // 若没有对应的权限则为非法用户
            Response::SendResponseJson(401, '非法用户');
        }
        if ($roleArr['powerid'] != 'ALL') { //系统级超级管理员ALL标志，不做具体权限判断
            $powerid = explode('|', $roleArr['powerid']);
            foreach ($powerid as $k => $v) {
                $where = ['id' => $v];
                $powerTotalArr[] = ManPowerModel::field(['controller', 'action'])->where($where)->find();
            }
            // 做两层判断
            // 对路径进行判断
            $controller = strtolower($matchs[0]);
            $action = strtolower($matchs[1]);
            $flag = false;
            foreach ($powerTotalArr as $k => $v) {
                if (strtolower($v['controller']) == $controller && strtolower($v['action']) == $action) {
                    $flag = true;
                }
            }
            if (!$flag) Response::SendResponseJson(401, '您没有操作权限');
        }
    }
    //校验代理人权限
    protected static function checkAgent($req){
        $current_city=0;
        if(!empty($req["province_id"])){
            $current_city=$req["province_id"];
            $back_city['province_id']=$req["province_id"];
        }
        if(!empty($req["city_id"])){
            $current_city=$req["city_id"];
            $back_city['city_id']=$req["city_id"];
        }
        if(!empty($req["area_id"])){
            $current_city=$req["area_id"];
            $back_city['area_id']=$req["area_id"];
        }
        //判断是否代理人，是的话取默认代理人城市权限， 并且切换城市需校验权限
        $admin_id = self::loginId();
        $where = ['id' => self::$admin_id];
        $adminArr = ManAdminModel::where($where)->find();
        if($adminArr['is_agent']==1){
            $agents=AgentCityModel::where(['admin_id'=>$admin_id])->get();
            foreach ($agents as $key=>$value){
                $result=CityAreaModel::where('path', 'like', '%' . $value['agent_city_id'] . '%')->get();
                $agents_citys=carray_column($result,'id');

                if(in_array($current_city,$agents_citys)){//当前城市存在于已拥有的权限中，验证通过返回所筛选的城市
                   break;
                }else{
                    if($current_city==0){
                        $default_city=CityAreaModel::field(['path'])->where(['id'=>$value['agent_city_id']])->find();
                        $city_array=explode(',',$default_city['path']);
                        $back_city['province_id'] = $city_array[0] ?? '';
                        $back_city['city_id'] = $city_array[1] ?? '';
                        $back_city['area_id'] = $city_array[2] ?? '';
                    }else{
                        $back_city=false;
                    }
                }
            }
         }

        return $back_city;



    }
}
