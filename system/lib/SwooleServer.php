<?php

namespace king\lib;

use king\core\Instance;
use king\core\Error;
use king\lib\Log;
use king\core\Loader;
use king\lib\SwooleHandle;
use Swoole;

class SwooleServer extends Instance
{
    protected $pid_file;
    protected $config;

    public function __construct()
    {
        $pid_path = APP_PATH . 'log/';
        $this->config = ['host' => '0.0.0.0','port' => '9501', 'worker_num' => 8, 'process_name_pre' => 'swoole_process_'];
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

    public function run($opera = 'start')
    {
        switch ($opera) {
            case 'start':
                $pid = $this->getPid();
                if ($pid && Swoole\Process::kill($pid, 0)) {
                    Error::showError('swoole http server process already exist!');
                    exit;
                }

                $app = SwooleHandle::getClass();
                var_dump($app);
                $this->server = new Swoole\Http\Server($this->config['host'], $this->config['port']);
                $this->server->on('workerstart', function(SwooleHandle $handle) {
                    $handle->onWorkStart($this->server->work_id);
                });
                $this->server->on('request', array($app, 'onRequest'));
                $this->server->start();
                break;

            case 'stop':
                if (!$pid = $this->getPid()) {
                    Error::showError('swoole http server process not started!');
                }

                if (Swoole\Process::kill((int)$pid)) {
                    echo 'swoole http server process close successful!';
                } else {
                    echo 'swoole http server process close failed!';
                }
                break;

            default:
                Error::showError('operation method does not exist!');
        }

    }

    private function getPid()
    {
        return file_exists($this->pid_file) ? file_get_contents($this->pid_file) : false;
    }
}