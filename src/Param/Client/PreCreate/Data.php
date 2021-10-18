<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\PreCreate;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $path;
    public string $size;
    public bool $isDir;
    protected int $autoInit = 1;
    public int $rType = 0;
    public string $uploadId;
    public array $blockList;
    public string $contentMd5;
    public string $sliceMd5;
    public string $localCtime;
    public string $localMtime;

    protected function keyHandler($key)
    {
        if ($key == 'isDir') {
            return 'isdir';
        }

        if ($key == 'rType') {
            return 'rtype';
        }

        if ($key == 'autoInit') {
            return 'autoinit';
        }

        if ($key == 'uploadId') {
            return 'uploadid';
        }

        if ($key == 'contentMd5') {
            return 'content-md5';
        }

        if ($key == 'sliceMd5') {
            return 'slice-md5';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'path') {
            return urlencode($value);
        }

        if ($key == 'isDir') {
            return $value ? 1 : 0;
        }

        if ($key == 'blockList') {
            return json_encode($value);
        }

        return parent::valueHandle($key, $value);
    }
}
