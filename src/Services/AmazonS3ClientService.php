<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\S3\ObjectUploader;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

class AmazonS3ClientService extends UploadFiles
{
    private const BUCKET = 'mariusz-test';
    private const REGION = 'us-east-1';
    private const ACL = 'private';

    public function putFiles($file, $destinationFolder)
    {
        if (is_object($file) || is_string($file)) {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => self::REGION,
                'credentials' => [
                    'key' => $this->getParams()->get('access_key'),
                    'secret' => $this->getParams()->get('secret_key')
                ]
            ]);

            $inputSource = fopen($file, 'rb');
            $outputSource = '';
            switch ($destinationFolder) {
                case 'thumbnails':
                    $outputSource .= './thumbnails/' . $file->getFileName();
                break;
                case 'images-thumbnails':
                    $outputSource .= './images/thumbnails/' . $file->getFileName();
                break;
            }

            $uploader = new ObjectUploader(
                $s3Client,
                self::BUCKET,
                $outputSource,
                $inputSource,
                self::ACL
            );

            do {
                try {
                    $result = $uploader->upload();
                    if ($result["@metadata"]["statusCode"] === 200) {
                        print 'upload file ' . $file->getFileName() . ' is successfully.' . PHP_EOL;
                    }
                } catch (MultipartUploadException $e) {
                    rewind($inputSource);
                    $uploader = new MultipartUploader($s3Client, $inputSource, [
                        'state' => $e->getState(),
                        'acl' => self::ACL,
                    ]);
                }
            } while (!isset($result));
            fclose($inputSource);
        }
    }
}