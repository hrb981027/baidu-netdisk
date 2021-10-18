<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\OAuth2\GetAccessTokenByRefreshToken;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $refreshToken;
    public string $clientId;
    public string $clientSecret;
}