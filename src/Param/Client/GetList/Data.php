<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\GetList;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $dir;
    public string $order;
    public string $desc;
    public int $start;
    public int $limit;
    public string $web;
    public bool $folder;
    public bool $showEmpty;

    protected function keyHandler($key)
    {
        if ($key == 'showEmpty') {
            return 'showempty';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'folder') {
            return $value ? 1 : 0;
        }

        if ($key == 'showEmpty') {
            return $value ? 1 : 0;
        }

        return parent::valueHandle($key, $value);
    }
}
