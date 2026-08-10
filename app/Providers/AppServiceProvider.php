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
                $client->setClientId($config['clientId'] ?? config('services.google.client_id') ?? env('GOOGLE_DRIVE_CLIENT_ID'));
                $client->setClientSecret($config['clientSecret'] ?? config('services.google.client_secret') ?? env('GOOGLE_DRIVE_CLIENT_SECRET'));
                
                $refreshToken = $config['refreshToken'] ?? config('services.google.refresh_token') ?? env('GOOGLE_DRIVE_REFRESH_TOKEN');
                if (!empty($refreshToken)) {
                    $client->refreshToken($refreshToken);
                }

                $service = new \Google\Service\Drive($client);
                $folderId = $config['folder'] ?? config('services.google.folder_id') ?? env('GOOGLE_DRIVE_FOLDER_ID');
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
