<?php

namespace king\lib;

use king\lib\File;

class Dir
{
    private $path;

    public static function getClass($path)
    {
        return new Dir($path);
    }

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function changeDirExt($new_ext)
    {
        $files = File::getFiles($this->path);
        foreach ($files as $file) {
            File::changeExt($file, $new_ext);
        }
    }
}
