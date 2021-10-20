<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\GetQuota;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public bool $checkFree;
    public bool $checkExpire;

    protected function keyHandler($key)
    {
        if ($key == 'checkFree') {
            return 'checkfree';
        }

        if ($key == 'checkExpire') {
            return 'checkexpire';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'checkFree') {
            return $value ? 1 : 0;
        }

        if ($key == 'checkExpire') {
            return $value ? 1 : 0;
        }

        return parent::valueHandle($key, $value);
    }
}
