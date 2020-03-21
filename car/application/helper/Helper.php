<?php

namespace app\helper;

use king\lib\Request;
use king\lib\Response;
use app\model\Log as LogModel;
use app\model\City as CityModel;
use app\model\Admin as AdminModel;
use king\lib\Input;

class Helper
{
    //
    public static function request($url, $params, $method = 'get', $header = [])
    {

        if (strtolower($method) == 'get') {
            $url = $url . '?' . http_build_query($params);
        }
        $req = Request::getClass($url, $method);
        $req->header = $header;
        if (strtolower($method) == 'post') {
            $req->body = $params;
        }
        $req->sendRequest();
        $rs = $req->getResponseInfo();
        if ($rs['http_code'] != 200) {
            Response::sendResponseJson(400, $req->getResponseBody());
        }
        return json_decode($req->getResponseBody(), true);
    }

    /**
     * 格式化数组中的时间
     *
     * @param [array] $arr
     * @return array
     */
    public static function formatTimt(&$arr, $fields = ['create_time'], $format = 'Y-m-d H:i:s')
    {
        if (is_array($arr)) {
            if (isset($arr['rs'])) {
                foreach ($arr['rs'] as $k => &$v) {
                    if (is_array($v)) {
                        Helper::formatTimt($v, $fields);
                    }

                    in_array($k, $fields, true) && $v = date($format, $v);
                }
            } else {
                foreach ($arr as $k => &$v) {
                    if (is_array($v)) {
                        Helper::formatTimt($v, $fields);
                    }

                    in_array($k, $fields, true) && $v = date($format, $v);
                }
            }
        } else {
            $arr = date($format, $arr);
        }

        return $arr;
    }

    /**
     * 记录后台操作日志
     *
     * @param [number] $admin_id  管理员id
     * @param [number] $user_id   用户id
     * @param [string] $old_value  旧值
     * @param [string] $new_value   新值
     * @param [string] $comment   备注
     * @return number insert成功数
     */
    public static function saveToLog($admin_id, $user_id, $old_value, $new_value, $comment)
    {
        $admin_name = AdminModel::where(['id' => $admin_id])->value('name');
        return LogModel::insert([
            'admin_id'     => $admin_id,
            'admin_name'   => $admin_name,
            'user_id'      => $user_id,
            'ip'           => Input::ipAddr(),
            'old_value'    => $old_value,
            'new_value'    => $new_value,
            'comment'      => $comment,
            'created_time' => time()
        ]); 
    }

    /**
     * 合并为1级带二级的分类
     *
     * @param [array] $arr 需要合并的数组
     * @param [type] $level_1 1级字段
     * @param [type] $level_2 2级字段
     * @return array
     */
    public static function getLevelArray($arr, $level_1, $level_2)
    {
        $data = [];
        foreach ($arr as $val) {
            if ($val[$level_2] == 0) {
                if (isset($data[$val[$level_1]])) {
                    $data[$val[$level_1]] = array_merge($val, $data[$val[$level_1]]);
                } else {
                    $data[$val[$level_1]] = $val;
                }
            } else {
                $data[$val[$level_2]]['children'][] = $val;
            }
        }

        return $data;
    }

    // 获取加密的密码
    public static function getPassword($password)
    {
        return md5(md5($password) . C('crypt_key'));
    }

    //获取客户端
    public static function getClient($int)
    {
        switch ($int) {
            case '1':
                $client = '微信';
                break;
            case '2':
                $client = 'Android';
                break;
            case '3':
                $client = 'Ios';
                break;
            case '4':
                $client = 'H5';
                break;
            default:
                $client = '未知';
                break;
        }

        return $client;
    }

    /**
     * 验证日期格式数据  :年月日
     *
     * @param [type] $date
     * @return void
     */
    public static function checkDateFormat($date)
    {
        //匹配日期格式
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //检测是否为日期
            if (checkdate($parts[2], $parts[3], $parts[1]))
                return true;
            else
                return false;
        } else
            return false;
    }

    //获取两个日期之间所有日期
    public static function getDatesBetweenTwoDays($startDate, $endDate)
    {
        $dates = [];
        if (strtotime($startDate) > strtotime($endDate)) {
            //如果开始日期大于结束日期，直接return 防止下面的循环出现死循环
            return $dates;
        } elseif ($startDate == $endDate) {
            //开始日期与结束日期是同一天时
            array_push($dates, $startDate);
            return $dates;
        } else {
            array_push($dates, $startDate);
            $currentDate = $startDate;
            do {
                $nextDate = date('Y-m-d', strtotime($currentDate . ' +1 days'));
                array_push($dates, $nextDate);
                $currentDate = $nextDate;
            } while ($endDate != $currentDate);
            return $dates;
        }
    }

    /**
     * 获取本周所有日期
     * time 时间
     * format 格式化
     */
    public static function get_week($time, $format = "Y-m-d")
    {
        $week = date('w', $time);
        $weekname = array('星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
        //星期日排首位
        if (empty($week)) {
            $week = 0;
        }
        $data = [];
        for ($i = 0; $i <= 6; $i++) {
            $data[$i]['date'] = date($format, strtotime('+' . $i - $week . ' days', $time));
            $data[$i]['week'] = $weekname[$i];
        }
        return $data;
    }
    /**
     * 获取某星期的开始时间和结束时间
     * time 时间
     * first 表示每周星期一为开始日期 0表示每周日为开始日期
     */
    public static function getWeekMyActionAndEnd($time = '', $first = 0)
    {
        //当前日期
        if (!$time) $time = time();
        $sdefaultDate = date("Y-m-d", $time);
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        //本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        return array("week_start" => $week_start, "week_end" => $week_end);
    }
    
    //当前时间 微秒级别
    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        $microtime = date("YmdHis") . substr((float) $usec, 2, 3);
        return $microtime;
    }

    //PHP中文名加密
    public static function encryptName($name) {
        $encrypt_name = '';
        //判断是否包含中文字符
        if(preg_match("/[\x{4e00}-\x{9fa5}]+/u", $name)) {
            //按照中文字符计算长度
            $len = mb_strlen($name, 'UTF-8');
            //echo '中文';
            if($len >= 3){
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $encrypt_name = mb_substr($name, 0, 1, 'UTF-8') .str_repeat('*',$len-2). mb_substr($name, -1, 1, 'UTF-8');
            } elseif($len === 2) {
                //两个字符
                $encrypt_name = mb_substr($name, 0, 1, 'UTF-8') . '*';
            }
        } else {
            //按照英文字串计算长度
            $len = strlen($name);
            //echo 'English';
            if($len >= 3) {
                //三个字符或三个字符以上掐头取尾，中间用*代替
                $encrypt_name = substr($name, 0, 1)  .str_repeat('*',$len-2). substr($name, -1);
            } elseif($len === 2) {
                //两个字符
                $encrypt_name = substr($name, 0, 1) . '*';
            }
        }
        return $encrypt_name;
    }

    //计算年龄
    public static function birthday2($birthday){
        if (is_int($birthday)) {
            date('Y-m-d', $birthday);
        }
        list($year,$month,$day) = explode("-",$birthday);
        $year_diff = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff  = date("d") - $day;
        if ($day_diff < 0 || $month_diff < 0)
         $year_diff--;
        return $year_diff;
      }

    public static function getFirstChar($str) {
        if (empty($str)) {
            return '';
        }
     
        $fir = $fchar = ord($str[0]);
        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str[0]);
        }
     
        $s1 = @iconv('UTF-8', 'gb2312', $str);
        $s2 = @iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        if (!isset($s[0]) || !isset($s[1])) {
            return '';
        }
     
        $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
     
        if (is_numeric($str)) {
            return $str;
        }
     
        if (($asc >= -20319 && $asc <= -20284) || $fir == 'A') {
            return 'A';
        }
        if (($asc >= -20283 && $asc <= -19776) || $fir == 'B') {
            return 'B';
        }
        if (($asc >= -19775 && $asc <= -19219) || $fir == 'C') {
            return 'C';
        }
        if (($asc >= -19218 && $asc <= -18711) || $fir == 'D') {
            return 'D';
        }
        if (($asc >= -18710 && $asc <= -18527) || $fir == 'E') {
            return 'E';
        }
        if (($asc >= -18526 && $asc <= -18240) || $fir == 'F') {
            return 'F';
        }
        if (($asc >= -18239 && $asc <= -17923) || $fir == 'G') {
            return 'G';
        }
        if (($asc >= -17922 && $asc <= -17418) || $fir == 'H') {
            return 'H';
        }
        if (($asc >= -17417 && $asc <= -16475) || $fir == 'J') {
            return 'J';
        }
        if (($asc >= -16474 && $asc <= -16213) || $fir == 'K') {
            return 'K';
        }
        if (($asc >= -16212 && $asc <= -15641) || $fir == 'L') {
            return 'L';
        }
        if (($asc >= -15640 && $asc <= -15166) || $fir == 'M') {
            return 'M';
        }
        if (($asc >= -15165 && $asc <= -14923) || $fir == 'N') {
            return 'N';
        }
        if (($asc >= -14922 && $asc <= -14915) || $fir == 'O') {
            return 'O';
        }
        if (($asc >= -14914 && $asc <= -14631) || $fir == 'P') {
            return 'P';
        }
        if (($asc >= -14630 && $asc <= -14150) || $fir == 'Q') {
            return 'Q';
        }
        if (($asc >= -14149 && $asc <= -14091) || $fir == 'R') {
            return 'R';
        }
        if (($asc >= -14090 && $asc <= -13319) || $fir == 'S') {
            return 'S';
        }
        if (($asc >= -13318 && $asc <= -12839) || $fir == 'T') {
            return 'T';
        }
        if (($asc >= -12838 && $asc <= -12557) || $fir == 'W') {
            return 'W';
        }
        if (($asc >= -12556 && $asc <= -11848) || $fir == 'X') {
            return 'X';
        }
        if (($asc >= -11847 && $asc <= -11056) || $fir == 'Y') {
            return 'Y';
        }
        if (($asc >= -11055 && $asc <= -10247) || $fir == 'Z') {
            return 'Z';
        }
     
        return '';
    }
}
