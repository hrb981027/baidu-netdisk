<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\Manger;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public int $async;
    public array $fileList;
    public string $ondup;

    protected function keyHandler($key)
    {
        if ($key == 'fileList') {
            return 'filelist';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'fileList') {
            return json_encode($value);
        }

        return parent::valueHandle($key, $value);
    }
}
