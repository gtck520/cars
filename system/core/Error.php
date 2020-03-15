<?php

namespace king\core;

use king\lib\Request;
use king\lib\Input;

class Error
{
    public static function register()
    {
        set_error_handler([__CLASS__, 'errorHandler']);
        set_exception_handler([__CLASS__, 'exceptionHandler']);
        register_shutdown_function([__CLASS__, 'shutdownHandler']);
    }


    public static function errorHandler(int $severity, string $message, string $file = null, int $line = null, $context = null)
    {
        if (error_reporting() & $severity) {
            $error_msg = 'PHP Error:' . $message . '，文件：' . $file . '，第' . $line . '行 ' . "\r\n";
            self::showError($error_msg);
        }
    }

    public static function exceptionHandler(\Throwable $exception)
    {
        $code = $exception->getCode();
        $error_msg = 'Exception Error ' . $code . ': ' . $exception->getMessage() . '，文件：' . $exception->getFile() . '，第' . $exception->getLine() . '行 ' . "\r\n";

        self::showError($error_msg);
    }

    public static function shutdownHandler()
    {
        $error = error_get_last();
        if (!is_null($error)) {
            if (in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
                $error_msg = 'Shutdown error:' . $error['file'] . '第' . $error['line'] . '行出错，错误内容：' . $error['message'] . "\r\n";
                self::showError($error_msg);
            }
        }
    }

    public static function showError($msg, $file = '', $line = '')
    {
        if (PHP_SAPI === 'cli') {
            echo $msg;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            $error_file = C('error_file') ?: 'error';
            require APP_PATH . 'view/common/' . $error_file . EXT;
            if (C('log_error') == true) {
                self::logError($msg);
            }
        }
        exit;
    }

    private static function logError($text, $file = '')
    {
        $log_str = date('Y-m-d H:i:s') . ' ' . ': ' . $text;
        $dir = APP_PATH . 'log/';
        if (is_dir($dir) && is_writeable($dir)) {
            $file = $file ? $dir . $file : $dir . date('Y-m-d') . '.log.php';
            if (is_file($file)) {
                chmod($file, 0664);
            }
            $log_full_str = '网址:' . ($_SERVER['SERVER_NAME'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '') . ' 访问者IP:' . Input::ipAddr() . ' ' . $log_str;
            error_log($log_full_str, 3, $file);
            if (C('log_api')) {
                $req = new Request(C('log_api'), 'post');
                $req->body = ['log' => $log_full_str];
                $req->sendRequest();
            }
        } else {
            die('log目录不可写');
        }
    }

    public static function debug($file, $number, $padding = 5)
    {
        if (!is_readable($file)) {
            return [];
        }
        $file = fopen($file, 'r');
        $line = 0;
        $range = ['start' => $number - $padding, 'end' => $number + $padding];
        $source = [];
        while (($row = fgets($file)) !== false) {
            if (++$line > $range['end']) {
                break;
            }

            if ($line >= $range['start']) {
                $source[$line] = $row;
            }
        }
        fclose($file);
        return $source;
    }
}