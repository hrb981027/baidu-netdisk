<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\OneUpload;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $path;
    public bool $isDir = false;
    public int $rType = 0;
    public string $localPath;
}
