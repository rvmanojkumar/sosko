<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;

class StorageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Storage::extend('s3', function ($app, $config) {
            $client = new S3Client([
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
                'bucket_endpoint' => $config['bucket_endpoint'] ?? false,
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
                'endpoint' => $config['endpoint'] ?? null,
            ]);
            
            $adapter = new AwsS3V3Adapter($client, $config['bucket']);
            
            return new Filesystem($adapter);
        });
    }
    
    public function register()
    {
        //
    }
}