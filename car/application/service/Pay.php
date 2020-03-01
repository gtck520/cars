<?php

namespace app\service;

use app\helper\DingDing;
use app\model\PayRecords as PayRecordsModel;
use app\model\UserBalanceRecords as UserBalanceRecordsModel;
use Yansongda\Pay\Pay as PayTool;
use app\service\User as UserService;
use app\cache\User as UserCache;
use Symfony\Component\HttpFoundation\Response as ToolResponse;
use Yansongda\Supports\Collection as ToolCollection;
use app\model\UserProfile as UserProfileModel;
use king\lib\Log;
use king\lib\Response;

class Pay
{
    /**
     * 获取相应pay对象
     *
     * @param String $pay_type  wechat  ||  alipay
     * @param String $method
     * @return Object 
     */
    public static function getPay($pay_type, $method = '')
    {
        if ($pay_type == 'wechat') {
            $config = C('pay.wechat_' . $method);
        } else {
            $config = C('pay.' . $pay_type);
        }
        return PayTool::$pay_type($config);
    }

    /**
     * 发送内容 
     *
     * @param number $union_no 订单号码
     * @param number $total_price  商品价格  扩展包 alipay 单位是 元  微信 单位是分
     * @param String $pay_type  wechat  ||  alipay
     * @return Array  
     */
    public static function payContent($union_no, $total_price, $pay_type)
    {
        if ($pay_type == 'alipay') {
            $data = [
                'out_trade_no' => $union_no,
                'subject' => '商の脉-订单付款',
                'total_amount' => sprintf("%.2f", $total_price)
            ];
        } else {
            $data = [
                'out_trade_no' => $union_no,
                'body' => '商の脉-订单付款',
                'total_fee' => $total_price * 100
            ];
        }
        return $data;
    }

    /**
     * 发送请求
     *
     * @param Object $pay  支付对象
     * @param Array  $content  发送内容
     * @param String $pay_type  alipay || wechat
     * @param String $method  使用方法 web 支付  app 支付等
     * @return void
     */
    public static function send($pay, $content, $pay_type, $method)
    {
        try {
            $pay = $pay->$method($content);
            if ($pay_type == 'alipay' && in_array($method, ['web', 'app', 'wap'])) {
                return $pay->send();
            }

            if ($pay_type == 'wechat' && in_array($method, ['wap', 'app'])) {
                return $pay->send();
            }
            return $pay;
        } catch (\Exception $e) {
            Response::SendResponseJson(400, $e->getMessage());
        }
    }

    //转换结果
    public static function parseResult($response)
    {
        if ($response instanceof ToolResponse) {
            $end = [
                'code' => $response->getStatusCode(),
                'data' => $response->getContent()
            ];
        } elseif ($response instanceof ToolCollection) {
            $end = [
                'code' => $response->statusCode,
                'data' => $response->content
            ];
        }

        return $end;
    }

    //订单支付
    public static function pay($union_no, $total_price, $pay_type, $method)
    {
        $pay = self::getPay($pay_type, $method);
        $content = self::payContent($union_no, $total_price, $pay_type);
        $end = self::send($pay, $content, $pay_type, $method);
        return self::parseResult($end);
    }

    //订单查找
    public static function find($trade_no, $pay_type, $method)
    {
        $pay = self::getPay($pay_type, $method);
        if ($pay_type == 'alipay') {
            $order = $pay->find(['trade_no' => $trade_no]);
        } elseif ($pay_type == 'wechat') {
            $order = $pay->find(['transaction_id' => $trade_no]);
        }
        return ['code' => 200, 'data' => $order];
    }

    /**
     * 订单退款
     * status = 0 只退款模式  
     * @param [type] $req
     * @return void
     */
    public static function refund($req)
    {
        
        $pay_trade_no = $req['pay_trade_no'];
        $trade_no     = $req['trade_no'];
        $amount       = sprintf("%.2f", $req['amount']);
        $status       = $req['status'];
        $type         = $req['type'];

        if ($status) {
            $pay_records_info   = PayRecordsModel::where(['trade_no' => $trade_no])->find();
            if (!$pay_records_info || $pay_records_info['status'] < 1) {
                return ['code' => 400, 'data' => '该订单不存在或者已退款'];
            }

            if ($amount > $pay_records_info['pay_amount']) {
                return ['code' => 400, 'data' => '退款金额不符,请检查'];
            }
        }
        $pay      = self::getPay($type);
        $order    = [
            'trade_no'       => $pay_trade_no,
            'refund_amount'  => $amount,
        ];
        $result = $pay->refund($order);

        if ($result->code == 10000) {
            if ($status) {
                PayRecordsModel::startTrans();
                try {
                    $balance_change_result= self::balanceChange($pay_records_info, $amount, "$type 退款", '-1');

                    PayRecordsModel::endTrans();
                } catch (\Exception $e) {
                    PayRecordsModel::rollback();
                    DingDing::sendMsg('alipay 余额变动异常:' . $e->getMessage());
                    Log::write(date("Y-m-d H:i:s") . 'alipay 余额变动异常:' . $e->getMessage());
                }

                if ($balance_change_result) {
                    $user_money = UserProfileModel::where(['user_id' => $pay_records_info['user_id']])->value(['money']);
                    UserCache::setCache($order['user_id'], $user_money);
                    return ['code' => 200, 'data' => ''];
                }
            }
        } else {
            return ['code' => 400, 'data' => $result->msg];
        }
        return ['code' => 200, 'data' => $result];
    }

    //支付通知
    public static function notify($pay_type, $method = '')
    {
        // 测试数据
        // $data = [
        //     'out_trade_no' => '20190821100020664',
        //     'trade_no' => '123456456455',
        //     'total_amount' => 1.00,
        //     'trade_status' => 'TRADE_SUCCESS',
        // ];
        $pay = self::getPay($pay_type, $method);
        $data = $pay->verify();
        $order_no = $data['out_trade_no'];

        //获取订单状态 只有为 0 待支付才修改
        $order = PayRecordsModel::where(['trade_no' => $order_no])->find();
        if (!$order) {
            DingDing::sendMsg(date("Y-m-d H:i:s") . "$order_no :无此订单");
            Log::write(date("Y-m-d H:i:s") . "$order_no :无此订单");
        }
        
        if ($order['status'] > 0) {
            return $pay->success()->send();
        }

        if ($pay_type == 'alipay') {
            $trade_no = $data['trade_no'];
            $total_amount = $data['total_amount'];
            if (in_array($data['trade_status'],['TRADE_SUCCESS','TRADE_FINISHED'])) {
                $check = 1;
            } else {
                $check = 0;
            }
        } else {
            $trade_no = $data['transaction_id'];
            $total_amount = $data['total_fee'] / 100;
            if ($data["return_code"] == "SUCCESS" && $data["result_code"] == "SUCCESS") {
                $check = 1;
            } else {
                $check = 0;
            }
        }

        $total_amount = sprintf($total_amount, 2);
        //订单金额
        $fee = sprintf($order['pay_amount'], 2);
        
        if ($data && $check && $fee == $total_amount) {
            PayRecordsModel::startTrans();
            try {
                $res = self::balanceChange($order, $total_amount, "$pay_type 充值", '1', $trade_no);

                PayRecordsModel::endTrans();
            } catch (\Exception $e) {
                PayRecordsModel::rollback();
                DingDing::sendMsg($pay_type . ' 错误信息:' . $e->getMessage());
                Log::write(date("Y-m-d H:i:s") . $pay_type . '错误信息:' . $e->getMessage());
            }

            if ($res) {
                $user_money = UserProfileModel::where(['user_id' => $order['user_id']])->value(['money']);
                UserCache::setCache($order['user_id'], $user_money);
                return $pay->success()->send();
            }
        }
        
    }

    /**
     * 红包变动操作
     *
     * @param Array  $order 数据库订单数据
     * @param Int $total_amount  变动金额
     * @param String $notes  备注
     * @param Int $status  订单状态
     * @param String $trade_no  第三方交易号码
     * @return Boolean
     */
    public static function balanceChange($order, $total_amount, $title, $status, $trade_no = '')
    {
        Log::write(date("Y-m-d H:i:s") . '进入余额变动操作:' . print_r($order, true), 'pay.log', 'pay');

        $user_info = UserProfileModel::where(['id' => $order['user_id']])->find();
        $order_data = self::getOrderData($status, $trade_no);
        $money_arr = self::getMoney($status, $user_info, $total_amount);
        $res = self::update($order, $money_arr, $title, $order_data, $user_info);
        if (!$res) {
            return false;
        }
        return true;
    }

    public static function getOrderData($status, $trade_no)
    {
        switch ($status) {
            case '-1':
                $order_data = [
                    'status' => '-1',
                    'updated_time' => time(),
                ];
                break;
            case '1':
                $order_data = [
                    'status' => '1',
                    'paid_time' => time(),
                    'updated_time' => time(),
                    'pay_trade_no' => $trade_no,
                ];
                break;
            default:
                # code...
                break;
        }
        return $order_data;
    }

    public static function getMoney($status, $userInfo, $total_amount)
    {
        try {
            $money = sprintf($userInfo['money'], 2);
            switch ($status) {
                case '-1':
                    $money -= $total_amount;
                    $money_arr = [
                        'amount' => -1 * $total_amount,
                    ];
                    break;
                case '1':
                    $money += $total_amount;
                    $money_arr = [
                        'amount' => $total_amount,
                    ];
                    
                    break;
                default:
                    # code...
                    break;
            }
            $money_arr['money'] = $money;
            return $money_arr;
        } catch (\Exception $e) {
            Log::write(date("Y-m-d H:i:s") . '错误信息:' . $e->getMessage());
        }
    }

    //变动操作
    public static function update($order, $money_arr, $title, $order_data, $user_info)
    {
        Log::write(date("Y-m-d H:i:s") . '进入余额更新操作' . print_r($order, true), 'pay.log', 'pay');
        
        $data = [
            'user_id'      => $user_info['id'],
            'to_user_id'   => $user_info['id'],
            'amount'       => $money_arr['amount'],
            'balance'      => $money_arr['money'],
            'title'        => $title,
            'notes'        => $title.$money_arr['amount'],
            'created_time' => time(),
        ];



        $balance_records_result = UserBalanceRecordsModel::insert($data);
        $pay_records_result = PayRecordsModel::where(['trade_no' => $order['trade_no']])->update($order_data);
        $userProfile_result = UserProfileModel::where(['id' => $user_info['id']])->update(['money +' => $money_arr['amount']]);

        Log::write(date("Y-m-d H:i:s") . '  ' . $order['trade_no'] . '更新操作返回结果：' . "[$balance_records_result,$pay_records_result,$userProfile_result]" , 'pay.log', 'pay');

        if ($balance_records_result && $pay_records_result && $userProfile_result) {
            return true;
        }

        return false;
    }

    //支付宝回调
    // public static function aliReturn()
    // {
    //     try {
    //         $pay = self::getPay('alipay','');
    //         $data = $pay->verify(); // 是的，验签就这么简单！
    //         $order_no = $data['out_trade_no'];

    //         //获取订单状态 只有为 0 待支付才修改
    //         $order = PayRecordsModel::where(['trade_no' => $order_no])->find();
    //         if (!$order) {
    //             throw new \Exception('出现异常无此订单:' . $order_no);
    //         }

    //         $trade_no = $data['trade_no'];
    //         $total_amount = $data['total_amount'];
    //         if ($data['trade_status'] == 'TRADE_SUCCESS' || $data['trade_status'] == 'TRADE_FINISHED') {
    //             $check = 1;
    //         } else {
    //             $check = 0;
    //         }

    //         $total_amount = sprintf($total_amount, 2);
    //         //订单金额
    //         $fee = sprintf($order['pay_amount'], 2);

    //         if ($data && $check && $fee == $total_amount) {
    //             $res = UserService::balanceChange($order, $total_amount, '支付宝付款', '1', $trade_no);
    //             if ($res) {
    //                 $jump = C('success_return_url');
    //             } else {
    //                 $jump = C('fail_return_url');
    //             }
    //             header('location:' . $jump);
    //         }
    //     } catch (\Exception $e) {
    //         DingDing::sendMsg('alipay 错误信息:' . $e->getMessage());
    //         Log::write(date("Y-m-d H:i:s") . 'alipay 错误信息:' . $e->getMessage());
    //     }
    // }
}
