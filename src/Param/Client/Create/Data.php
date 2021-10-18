<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\Create;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $path;
    public string $size;
    public bool $isDir;
    public int $rType = 0;
    public string $uploadId;
    public array $blockList;
    public int $localCtime;
    public int $localMtime;
    public int $zipQuality;
    public int $zipSign;
    public int $isRevision;
    public int $mode;
    public string $exifInfo;

    protected function keyHandler($key)
    {
        if ($key == 'isDir') {
            return 'isdir';
        }

        if ($key == 'rType') {
            return 'rtype';
        }

        if ($key == 'uploadId') {
            return 'uploadid';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'isDir') {
            return $value ? '1' : '0';
        }

        if ($key == 'blockList') {
            return json_encode($value);
        }

        return parent::valueHandle($key, $value);
    }
}
