# Spatie Media + Uppy for upload files and images for Moonshine admin panel 

## Versions

* Verson 1.* for moonshine 2
* Verson 2.* for moonshine 3
* Verson 4.* for moonshine 4

## Install 
```shell
composer require gian_tiaga/moonshine-file
```

## Usage
- Install spatie laravel laravel-medialibrary
[https://spatie.be/docs/laravel-medialibrary
](https://spatie.be/docs/laravel-medialibrary)

- Make your models

- In your resource add
```php
SpatieUppyFile::make('Фото', 'photo')
    ->multiple()
    ->countFiles(5)
    ->image()
```

Yo can set allowed file types
```php
SpatieUppyFile::make('Фото', 'photo')
    ->allowedFileTypes('video/*')
```
![demo](images/1.jpg)

You can use this field in json
