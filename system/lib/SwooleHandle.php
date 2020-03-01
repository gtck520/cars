<?php

namespace king\lib;

use king\core\Instance;
use king\core\Error;
use king\lib\Log;
use Swoole;

class SwooleHandle extends Instance
{
    protected $pid_file;
    protected $config;

    public function __construct()
    {
        $pid_path = APP_PATH . 'log/';
        $this->config = ['host' => '9501','port' => 9501, 'worker_num' => 8, 'process_name_pre' => 'swoole_process_'];
        $config = C('swoole.*');
        foreach ($config as $key => $value) {
            if (isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }

        if (isset($config['host'])) {
            $this->host = $config['host'];
        }

        if (isset($config['port'])) {
            $this->host = $config['port'];
        }

        $this->pid_file = $pid_path . ($config['port'] ?? 'swoole.log');
    }

    public function onStart()
    {
        swoole_set_process_name($this->config['process_name_pre'] . 'master');
        $pid = $this->server->master_pid . ' ' . $this->server->manager_pid;
        file_put_contents($this->pid_file, $pid);
    }

    public function onWorkStart($server, $work_id)
    {
        if ($work_id >= $this->config['worker_num']) {
            swoole_set_process_name($this->config['process_name_pre'] . '-task');
        } else {
            swoole_set_process_name($this->config['process_name_pre'] . '-event');
        }

        define('DS', DIRECTORY_SEPARATOR);
        define('FCPATH', __DIR__ . DS);
        require FCPATH . '../bootstrap.php';
    }

    public function onRequest($request, $response)
    {
        $_SERVER = [];
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        if (isset($request->header)) {
            foreach ($request->header as $key => $value) {
                if (isset(self::$headerServerMapping[$key])) {
                    $_SERVER[self::$headerServerMapping[$key]] = $value;
                } else {
                    $key = str_replace('-', '_', $key);
                    $_SERVER[strtoupper('http_' . $key)] = $value;
                }
            }
        }

        if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        if (strpos($_SERVER['REQUEST_URI'], '?') === false && isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0
        ) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }

        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = $GLOBALS['argv'] ?? [];
            $_SERVER['argc'] = $GLOBALS['argc'] ?? 0;
        }

        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        $_COOKIE = [];
        if (isset($request->cookie)) {
            foreach ($request->cookie as $k => $v) {
                $_COOKIE[$k] = $v;
            }
        }

        $segs = Route::getSegs();
        if (is_array($segs)) {
            $class_name = $segs['class'];
            $method = $segs['method'] ?: 'index';
            $methods = explode('?', $method);
            Loader::$method = $methods[0];
            $class = new $class_name;
            if (method_exists($class, $methods[0])) {
                $function = $methods[0];
                ob_start();
                $output = $class->$function(...$segs['call_args']);
                ob_get_clean();
                return $response->end($output);
            } else {
                Error::showError('方法错误：' . $class_name . '.' . $methods[0]);
            }
        } else {
            Error::showError('方法获取失败：' . $segs);
        }
    }

    public function onShutdown()
    {
        if (file_exists($this->pid_file)) {
            unlink($this->pid_file);
        }
    }
}