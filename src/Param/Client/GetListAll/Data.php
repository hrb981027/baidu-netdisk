<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\GetListAll;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $path;
    public string $order;
    public string $desc;
    public int $start;
    public int $limit;
    public bool $recursion;
    public int $ctime;
    public int $mtime;
    public bool $web;

    protected function valueHandle($key, $value)
    {
        if ($key == 'recursion') {
            return $value ? 1 : 0;
        }

        if ($key == 'web') {
            return $value ? 1 : 0;
        }

        return parent::valueHandle($key, $value);
    }
}
