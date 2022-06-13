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

    public function putFiles($file)
    {
        if (is_object($file) || is_string($file)) {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => self::REGION,
                'credentials' => [
                    'key' => $this->params->get('access_key'),
                    'secret' => $this->params->get('secret_key')
                ]
            ]);

            $source = fopen($file, 'rb');
            $uploader = new ObjectUploader(
                $s3Client,
                self::BUCKET,
                basename($file),
                $source,
                self::ACL
            );

            do {
                try {
                    $result = $uploader->upload();
                    if ($result["@metadata"]["statusCode"] === 200) {
                        print 'upload file ' . $file->getFileName() . ' is successfully.' . PHP_EOL;
                    }
                } catch (MultipartUploadException $e) {
                    rewind($source);
                    $uploader = new MultipartUploader($s3Client, $source, [
                        'state' => $e->getState(),
                        'acl' => self::ACL,
                    ]);
                }
            } while (!isset($result));

            fclose($source);
        }
    }
}