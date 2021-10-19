<?php

declare(strict_types=1);

namespace Hrb981027\BaiduNetdisk;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Hrb981027\BaiduNetdisk\Exception\InvalidClientException;
use Hrb981027\BaiduNetdisk\Param\Client\OneUpload\Data as OneUploadData;
use Hrb981027\BaiduNetdisk\Param\Client\PreCreate\Data as PreCreateData;
use Hrb981027\BaiduNetdisk\Param\Client\Upload\Data as UploadData;
use Hrb981027\BaiduNetdisk\Param\Client\Create\Data as CreateData;
use Hyperf\Guzzle\ClientFactory;

class Client
{
    const PRE_CREATE_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';
    const UPLOAD_ENDPOINT = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2';
    const CREATE_ENDPOINT = 'https://pan.baidu.com/rest/2.0/xpan/file';

    protected GuzzleHttpClient $client;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create();
    }

    /**
     * @throws InvalidClientException
     */
    public function preCreate(string $accessToken, PreCreateData $data)
    {
        try {
            $response = $this->client->post(self::PRE_CREATE_ENDPOINT, [
                'query' => [
                    'method' => 'precreate',
                    'access_token' => $accessToken
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
    public function upload(string $accessToken, UploadData $data)
    {
        try {
            $response = $this->client->post(self::UPLOAD_ENDPOINT, [
                'query' => array_merge([
                    'access_token' => $accessToken
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
    public function create(string $accessToken, CreateData $data)
    {
        try {
            $response = $this->client->post(self::CREATE_ENDPOINT, [
                'query' => [
                    'method' => 'create',
                    'access_token' => $accessToken
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
    public function oneUpload(string $accessToken, OneUploadData $data)
    {
        if ($data->isDir) {
            return $this->create($accessToken, new CreateData([
                'path' => $data->path,
                'size' => '0',
                'is_dir' => true,
                'r_type' => $data->rType
            ]));
        }

        $fileDataList = $this->splitFile($data->localPath, 4);

        $preCreateResult = $this->preCreate($accessToken, new PreCreateData([
            'path' => $data->path,
            'is_dir' => false,
            'size' => (string)filesize($data->localPath),
            'r_type' => $data->rType,
            'block_list' => array_column($fileDataList, 'md5')
        ]));

        foreach ($fileDataList as $key => $item) {
            $this->upload($accessToken, new UploadData([
                'path' => $data->path,
                'upload_id' => $preCreateResult['uploadid'],
                'part_seq' => $key,
                'file' => $item['path']
            ]));
        }

        return $this->create($accessToken, new CreateData([
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
}
