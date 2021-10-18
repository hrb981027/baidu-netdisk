<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Hrb981027\BaiduNetdisk\Exception\InvalidOAuth2Exception;
use Hrb981027\BaiduNetdisk\Param\OAuth2\GetAccessTokenByAuthorizationCode\Data as GetAccessTokenByAuthorizationCodeData;
use Hrb981027\BaiduNetdisk\Param\OAuth2\GetAccessTokenByRefreshToken\Data as GetAccessTokenByRefreshTokenData;
use Hrb981027\BaiduNetdisk\Param\OAuth2\GetAuthorizeUrl\Data as GetAuthorizeUrlData;
use Hyperf\Guzzle\ClientFactory;

class OAuth2
{
    const AUTHORIZE_ENDPOINT = 'http://openapi.baidu.com/oauth/2.0/authorize';
    const TOKEN_ENDPOINT = 'http://openapi.baidu.com/oauth/2.0/token';

    protected GuzzleHttpClient $client;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create();
    }

    public function getAuthorizeUrl(GetAuthorizeUrlData $data)
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $data->clientId,
            'redirect_uri' => $data->redirectUri,
            'scope' => 'basic,netdisk',
            'display' => $data->display,
            'state' => $data->state,
            'force_login' => $data->forceLogin
        ];

        return self::AUTHORIZE_ENDPOINT . '?' . http_build_query($params);
    }

    /**
     * @throws InvalidOAuth2Exception
     */
    public function getAccessTokenByAuthorizationCode(GetAccessTokenByAuthorizationCodeData $data)
    {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $data->code,
            'client_id' => $data->clientId,
            'client_secret' => $data->clientSecret,
            'redirect_uri' => $data->redirectUri
        ];

        try {
            $response = $this->client->get(self::TOKEN_ENDPOINT, [
                'query' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidOAuth2Exception($responseContents['error_description'] ?? '网络错误');
            }

            throw new InvalidOAuth2Exception('网络错误');
        }
    }

    /**
     * @throws InvalidOAuth2Exception
     */
    public function getAccessTokenByRefreshToken(GetAccessTokenByRefreshTokenData $data)
    {
        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $data->refreshToken,
            'client_id' => $data->clientId,
            'client_secret' => $data->clientSecret
        ];

        try {
            $response = $this->client->get(self::TOKEN_ENDPOINT, [
                'query' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidOAuth2Exception($responseContents['error_description'] ?? '网络错误');
            }

            throw new InvalidOAuth2Exception('网络错误');
        }
    }
}
