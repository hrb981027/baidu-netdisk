<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk\Param\OAuth2\GetAuthorizeUrl;

use Hrb981027\BaiduNetdisk\Param\Param;

class Data extends Param
{
    public string $clientId;
    public string $redirectUri;
    public string $display = 'page';
    public string $state = '';
    public int $forceLogin = 1;
}