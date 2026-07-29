<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Masbug\Flysystem\GoogleDriveAdapter;
use League\Flysystem\Filesystem as Flysystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            Storage::extend('google', function ($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId'] ?? env('GOOGLE_DRIVE_CLIENT_ID'));
                $client->setClientSecret($config['clientSecret'] ?? env('GOOGLE_DRIVE_CLIENT_SECRET'));
                $client->refreshToken($config['refreshToken'] ?? env('GOOGLE_DRIVE_REFRESH_TOKEN'));

                $service = new \Google\Service\Drive($client);
                $folderId = $config['folder'] ?? env('GOOGLE_DRIVE_FOLDER_ID');
                $folder = (!empty($folderId) && $folderId !== 'root' && $folderId !== '.') ? $folderId : null;
                $adapter = new GoogleDriveAdapter($service, $folder);
                $driver = new Flysystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter, $config);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Google Storage Boot Warning: ' . $e->getMessage());
        }
    }
}
