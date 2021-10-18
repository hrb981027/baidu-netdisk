<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\Client\Upload;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    protected string $method = 'upload';
    protected string $type = 'tmpfile';
    public string $path;
    public string $uploadId;
    public int $partSeq;
    public string $file;

    public function toArray(): array
    {
        $result = parent::toArray();

        if (isset($result['file'])) {
            unset($result['file']);
        }

        return $result;
    }

    protected function keyHandler($key)
    {
        if ($key == 'uploadId') {
            return 'uploadid';
        }

        if ($key == 'partSeq') {
            return 'partseq';
        }

        return parent::keyHandler($key);
    }

    protected function valueHandle($key, $value)
    {
        if ($key == 'path') {
            return urlencode($value);
        }

        return parent::valueHandle($key, $value);
    }
}
