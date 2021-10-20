<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\FileMetas;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public array $fsids;
    public string $path;
    public bool $thumb;
    public bool $dlink;
    public bool $extra;

    protected function valueHandle($key, $value)
    {
        if ($key == 'fsids') {
            return json_encode($value);
        }

        if ($key == 'thumb') {
            return $value ? 1 : 0;
        }

        if ($key == 'dlink') {
            return $value ? 1 : 0;
        }

        if ($key == 'extra') {
            return $value ? 1 : 0;
        }

        return parent::valueHandle($key, $value);
    }
}
