<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param;

class Param
{
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $key = camelize($key);
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(): array
    {
        $data = get_object_vars($this);

        $result = [];

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $value = $value->toArray();
            }

            if (is_array($value)) {
                foreach ($value as &$item) {
                    if (is_object($item)) {
                        $item = $item->toArray();
                    }
                }
                unset($item);
            }

            $result[$this->keyHandler($key)] = $this->valueHandle($key, $value);
        }

        return $result;
    }

    protected function keyHandler($key)
    {
        return uncamelize($key);
    }

    protected function valueHandle($key, $value)
    {
        return $value;
    }
}