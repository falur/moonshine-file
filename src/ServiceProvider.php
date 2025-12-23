<?php

declare(strict_types=1);

namespace GianTiaga\MoonshineFile;

use GianTiaga\MoonshineFile\Http\Controllers\FileUploadController;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'gt-moonshine-file'
        );

        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');

        \Illuminate\Support\Facades\Route::post(
            'gt-moonshine-file/upload',
            FileUploadController::class,
        )->name('gt-moonshine-file.upload');
    }
}
