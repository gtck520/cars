<?php

namespace king\lib;

class File
{
    public static function getFiles($dir)
    {
        static $result;
        foreach (new \DirectoryIterator($dir) as $file_info) {
            if (!$file_info->isDot()) {
                $file_path = $dir . '/' . $file_info->getFilename();
                if (is_file($file_path)) {
                    $result[] = $file_path;
                }

                if ($file_info->isDir()) {
                    self::getFiles($file_info->getPathname());
                }
            }
        }
        return $result;
    }


    public static function getExt($file)
    {
        $file_info = pathinfo($file);
        return $file_info['extension'];
    }

    public static function readFile($file)
    {
        return file_get_contents($file);
    }

    public static function writeFile($path, $data, $mode = 'wb')
    {
        if (!$fp = fopen($path, $mode)) {
            return false;
        }

        flock($fp, LOCK_EX);
        for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($data, $written))) === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return is_int($result);
    }

    public static function changeExt($file, $new_ext)
    {
        $ext = self::getExt($file);
        $new_file = str_replace('.' . $ext, '.' . $new_ext, $file);
        rename($file, $new_file);
    }

}