<?php

declare(strict_types=1);

namespace GianTiaga\MoonshineFile\Fields;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use MoonShine\Components\Files;
use MoonShine\Components\Thumbnails;
use MoonShine\Fields\Field;
use Closure;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SpatieUppyFile extends Field
{
    /**
     * @var string
     */
    protected string $view = 'gt-moonshine-file::spatie-uppy-file';

    /**
     * @var string[]
     */
    protected array $assets = [
        'https://releases.transloadit.com/uppy/v3.24.3/uppy.min.css',
        'https://releases.transloadit.com/uppy/v3.24.3/uppy.min.js',
        'https://releases.transloadit.com/uppy/locales/v3.3.1/ru_RU.min.js',
    ];

    /**
     * @var int
     */
    protected int $countFiles = 1;

    /**
     * @var array<array-key, string>
     */
    protected array $allowedFileTypes = ['*/*'];

    /**
     * @param  int|null  $value
     * @return int|$this
     */
    public function countFiles(?int $value = null): static|int
    {
        if (func_num_args() === 0) {
            return $this->countFiles;
        }

        /** @var int $value */
        $this->countFiles = $value;

        return $this;
    }

    /**
     * @param  array<array-key, string>|null $value
     * @return $this|array<array-key, string>
     */
    public function allowedFileTypes(?array $value = null): static|array
    {
        if (func_num_args() === 0) {
            return $this->allowedFileTypes;
        }

        /** @var array<array-key, string> $value */
        $this->allowedFileTypes = $value;

        return $this;
    }

    public function image(): static
    {
        $this->allowedFileTypes = [
            'image/*',
        ];

        return $this;
    }

    public function multiple(): static
    {
        $this->countFiles = 20;

        return $this;
    }

    protected function resolveAfterApply(mixed $data): mixed
    {
        /** @var HasMedia $data */

        /** @var Collection<array-key, Media> $existingMedia */
        $existingMedia = $data->getMedia($this->column());

        /** @var string $pureValue */
        $pureValue = $this->requestValue();
        if (!$pureValue) {
            return $data;
        }

        /** @var array<array-key, mixed> $value */
        $value = json_decode($pureValue, true);

        $ids = [];
        foreach ($value as $item) {
            $id = data_get($item, 'id');
            if (!$id) {
                continue;
            }
            $ids[] = $id;

            if ($existingMedia->contains('id', '=', $id)) {
                continue;
            }

            /** @var Media $media */
            $media = Media::findOrFail($id);
            $media->move($data, $this->column());
        }

        $existingMedia
            ->whereNotIn('id', $ids)
            ->each(fn (Media $media) => $media->delete());

        return $data;
    }

    protected function resolveOnApply(): ?Closure
    {
        return static fn ($item) => $item;
    }

    protected function resolvePreview(): View|string
    {
        /** @var HasMedia $model */
        $model = $this->getData();
        /** @var Collection<array-key, Media> $mediaCollection */
        $mediaCollection = $model->getMedia($this->column());

        if ($mediaCollection->isEmpty()) {
            return '';
        }

        /** @var Media $firstMedia */
        $firstMedia = $mediaCollection->first();

        $urls = $this->countFiles > 1
            ? $mediaCollection->map(fn (Media $media) => $media->getUrl())->toArray()
            : [$firstMedia->getUrl()];
        $names = fn (string $filename, int $index = 0) => $mediaCollection->get($index)?->name;

        $isCollectionHasNotImage = $mediaCollection
            ->contains(fn (Media $media) => !str_contains($media->mime_type, 'image'));

        /** @var string $view */
        $view = '';

        if ($isCollectionHasNotImage) {
            $view = Files::make(
                files: $urls,
                download: false,
                names: $names,
            )->render();
        } else {
            $view = Thumbnails::make(
                valueOrValues: $urls,
                names: $names,
            )->render();
        }

        /** @var string $view */
        return $view;
    }
}
