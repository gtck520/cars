<?php

namespace king\lib;

use king\core\Error;
use Cron\CronExpression;
use king\core\Loader;
use king\core\Instance;

class Console extends Instance
{
    private $cli;
    public $file;
    public $crons;

    public function __construct()
    {
        $this->cli = C('cron.*');
    }

    public function run()
    {
        if (isset($this->cli['crons']) && count($this->cli['crons']) > 0) {
            ob_start();
            foreach ($this->cli['crons'] as $schedule => $func) {
                if (CronExpression::factory((string)$schedule)->isDue()) {
                    Loader::run($func);
                }
            }

            $output = ob_get_contents();
            if (strlen($output) > 0) {
                $output .= "\r\n";
                $this->writeJob(date('Y-m-d H:i:s') . '--' . $output);
            }
            ob_end_clean();
        }
    }

    private function writeJob($job, $file = '')
    {
        $file = $this->file ?: $this->cli['cron_file'];
        $this->writeFile($file, $job);
    }

    protected function writeFile($path, $data, $mode = 'a+')
    {
        if (!$fp = fopen($path, $mode)) {
            return false;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }
}
