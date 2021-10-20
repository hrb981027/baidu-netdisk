<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\Search;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $key;
    public string $dir;
    public bool $recursion;
    public int $page;
    public int $num;
    public bool $web;

    protected function valueHandle($key, $value)
    {
        if ($key == 'recursion') {
            return $value ? '1' : '0';
        }

        if ($key == 'web') {
            return $value ? 1 : 0;
        }

        return parent::valueHandle($key, $value);
    }
}
