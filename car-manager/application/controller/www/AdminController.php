<?php

namespace app\controller\www;

use king\lib\Request;
use king\lib\Response;
use app\cache\Token;
use app\model\Admin as ManAdminModel;
use app\model\ManPower as ManPowerModel;
use app\model\ManRole as ManRoleModel;
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
}
