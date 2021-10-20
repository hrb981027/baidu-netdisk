<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Hrb981027\BaiduNetdisk\Exception\InvalidClientException;
use Hrb981027\BaiduNetdisk\Param\Client\FileMetas\Data as FileMetasData;
use Hrb981027\BaiduNetdisk\Param\Client\GetList\Data as GetListData;
use Hrb981027\BaiduNetdisk\Param\Client\GetListAll\Data as GetListAllData;
use Hrb981027\BaiduNetdisk\Param\Client\GetQuota\Data as GetQuotaData;
use Hrb981027\BaiduNetdisk\Param\Client\Manger\Data as MangerData;
use Hrb981027\BaiduNetdisk\Param\Client\OneUpload\Data as OneUploadData;
use Hrb981027\BaiduNetdisk\Param\Client\PreCreate\Data as PreCreateData;
use Hrb981027\BaiduNetdisk\Param\Client\Search\Data as SearchData;
use Hrb981027\BaiduNetdisk\Param\Client\Upload\Data as UploadData;
use Hrb981027\BaiduNetdisk\Param\Client\Create\Data as CreateData;
use Hyperf\Guzzle\ClientFactory;

class Client
{
    const PRE_CREATE_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const UPLOAD_ENDPOINT = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2';
    const CREATE_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const FILE_METAS_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/multimedia';
    const SEARCH_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const LIST_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const LIST_ALL_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/multimedia';
    const MANAGER_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const USER_INFO_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/nas';
    const QUOTA_ENDPOINT = 'https://pan.baidu.com/api/quota';

    protected GuzzleHttpClient $client;
    public string $accessToken = '';

    public function __construct(ClientFactory $clientFactory, string $accessToken)
    {
        $this->client = $clientFactory->create();
        $this->accessToken = $accessToken;
    }

    /**
     * @throws InvalidClientException
     */
    public function preCreate(PreCreateData $data): array
    {
        try {
            $response = $this->client->post(self::PRE_CREATE_ENDPOINT, [
                'query' => [
                    'method' => 'precreate',
                    'access_token' => $this->accessToken
                ],
                'form_params' => $data->toArray()
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['error_msg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function upload(UploadData $data): array
    {
        try {
            $response = $this->client->post(self::UPLOAD_ENDPOINT, [
                'query' => array_merge([
                    'access_token' => $this->accessToken
                ], $data->toArray()),
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($data->file, 'rb')
                    ]
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['error_msg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function create(CreateData $data): array
    {
        try {
            $response = $this->client->post(self::CREATE_ENDPOINT, [
                'query' => [
                    'method' => 'create',
                    'access_token' => $this->accessToken
                ],
                'form_params' => $data->toArray()
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['error_msg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function oneUpload(OneUploadData $data): array
    {
        if ($data->isDir) {
            return $this->create(new CreateData([
                'path' => $data->path,
                'size' => '0',
                'is_dir' => true,
                'r_type' => $data->rType
            ]));
        }

        $fileDataList = $this->splitFile($data->localPath, 4);

        $preCreateResult = $this->preCreate(new PreCreateData([
            'path' => $data->path,
            'is_dir' => false,
            'size' => (string)filesize($data->localPath),
            'r_type' => $data->rType,
            'block_list' => array_column($fileDataList, 'md5')
        ]));

        foreach ($fileDataList as $key => $item) {
            $this->upload(new UploadData([
                'path' => $data->path,
                'upload_id' => $preCreateResult['uploadid'],
                'part_seq' => $key,
                'file' => $item['path']
            ]));
        }

        return $this->create(new CreateData([
            'path' => $data->path,
            'size' => (string)filesize($data->localPath),
            'is_dir' => false,
            'r_type' => $data->rType,
            'upload_id' => $preCreateResult['uploadid'],
            'block_list' => array_column($fileDataList, 'md5')
        ]));
    }

    protected function splitFile(string $path, int $size): array
    {
        $result = [];

        $fp = fopen($path, "rb");
        while (!feof($fp)) {
            $tempFilePath = '/tmp/' . generateUUID();

            $handle = fopen($tempFilePath, "wb");
            fwrite($handle, fread($fp, $size * 1024 * 1024));
            fclose($handle);
            unset($handle);

            $result[] = [
                'path' => $tempFilePath,
                'md5' => md5_file($tempFilePath)
            ];
        }
        fclose($fp);

        return $result;
    }

    /**
     * @throws InvalidClientException
     */
    public function fileMetas(FileMetasData $data): array
    {
        try {
            $response = $this->client->get(self::FILE_METAS_ENDPOINT, [
                'query' => array_merge([
                    'method' => 'filemetas',
                    'access_token' => $this->accessToken
                ], $data->toArray())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function search(SearchData $data): array
    {
        try {
            $response = $this->client->get(self::SEARCH_ENDPOINT, [
                'query' => array_merge([
                    'method' => 'search',
                    'access_token' => $this->accessToken
                ], $data->toArray())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function getList(GetListData $data): array
    {
        try {
            $response = $this->client->get(self::LIST_ENDPOINT, [
                'query' => array_merge([
                    'method' => 'list',
                    'access_token' => $this->accessToken
                ], $data->toArray())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function getListAll(GetListAllData $data): array
    {
        try {
            $response = $this->client->get(self::LIST_ALL_ENDPOINT, [
                'query' => array_merge([
                    'method' => 'listall',
                    'access_token' => $this->accessToken
                ], $data->toArray())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function manager(string $opera, MangerData $data): array
    {
        try {
            $response = $this->client->post(self::MANAGER_ENDPOINT, [
                'query' => [
                    'method' => 'filemanager',
                    'access_token' => $this->accessToken,
                    'opera' => $opera
                ],
                'form_params' => $data->toArray()
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function getUserInfo(): array
    {
        try {
            $response = $this->client->get(self::USER_INFO_ENDPOINT, [
                'query' => [
                    'method' => 'uinfo',
                    'access_token' => $this->accessToken
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }

    /**
     * @throws InvalidClientException
     */
    public function getQuota(GetQuotaData $data): array
    {
        try {
            $response = $this->client->get(self::QUOTA_ENDPOINT, [
                'query' => array_merge([
                    'access_token' => $this->accessToken
                ], $data->toArray())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseContents = json_decode($response->getBody()->getContents(), true);

                throw new InvalidClientException($responseContents['errmsg'] ?? '网络错误');
            }

            throw new InvalidClientException('网络错误');
        }
    }
}
