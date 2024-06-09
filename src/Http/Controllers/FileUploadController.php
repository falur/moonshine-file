<?php

declare(strict_types=1);

namespace GianTiaga\MoonshineFile\Http\Controllers;

use GianTiaga\MoonshineFile\Models\Media;
use Illuminate\Http\UploadedFile;
use MoonShine\MoonShineRequest;
use MoonShine\Http\Controllers\MoonShineController;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Symfony\Component\HttpFoundation\Response;

class FileUploadController extends MoonShineController
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function __invoke(MoonShineRequest $request): Response
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->file('file');

        /** @var Media $media */
        $media = (new Media())
            ->addMedia($uploadedFile)
            ->usingFileName(
                time()
                . md5($uploadedFile->getClientOriginalName())
                . '.'
                . $uploadedFile->getClientOriginalExtension()
            )
            ->toMediaCollection('upload');

        $media->model_type = Media::class;
        $media->model_id = 0;
        $media->save();

        /** @var string $pathGeneratorName */
        $pathGeneratorName = config('media-library.path_generator');
        /** @var PathGenerator $pathGenerator */
        $pathGenerator = app($pathGeneratorName);

        $uploadedFile->storeAs(
            $pathGenerator->getPath($media),
            $media->file_name,
            ['disk' => config('moonshine.disk')]
        );

        return $this->json(data: $media->toArray());
    }
}
