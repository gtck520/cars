<?php
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('FCPATH', __DIR__ . DS);
chdir(__DIR__);
require FCPATH . '../../system/bootstrap.php';
