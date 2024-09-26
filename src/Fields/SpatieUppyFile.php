<?php

declare(strict_types=1);

namespace GianTiaga\MoonshineFile\Fields;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Closure;
use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\UI\Components\Files;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Fields\Field;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @return array
     */
    public function getAssets(): array
    {
        return [
            Css::make('https://releases.transloadit.com/uppy/v3.24.3/uppy.min.css'),
            Js::make('https://releases.transloadit.com/uppy/v3.24.3/uppy.min.js'),
            Js::make('https://releases.transloadit.com/uppy/locales/v3.3.1/ru_RU.min.js'),
        ];
    }

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

    /**
     * @param HasMedia $data
     * @return mixed
     */
    protected function resolveAfterApply(mixed $data): mixed
    {
        /** @var Collection<array-key, Media> $existingMedia */
        $existingMedia = $data->getMedia($this->getColumn());

        $pureValue = $this->getRequestValue() ?: '[]';

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
            $media->move($data, $this->getColumn());
        }

        $existingMedia
            ->whereNotIn('id', $ids)
            ->each(fn (Media $media) => $media->delete());

        return $data;
    }

    protected function resolveOnApply(): ?Closure
    {
        return fn ($item) => $item;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     */
    protected function resolvePreview(): View|string
    {
        /** @var HasMedia $model */
        $model = $this->getData()->getOriginal();
        /** @var Collection<array-key, Media> $mediaCollection */
        $mediaCollection = $model->getMedia($this->getColumn());

        if ($mediaCollection->isEmpty()) {
            return '';
        }

        /** @var Media $firstMedia */
        $firstMedia = $mediaCollection->first();
        $urls = $this->countFiles > 1
            ? $mediaCollection->map(fn (Media $media) => $media->getUrl())->toArray()
            : [$firstMedia->getUrl()];

        $isCollectionHasNotImage = $mediaCollection
            ->contains(fn (Media $media) => !str_contains($media->mime_type, 'image'));

        if ($isCollectionHasNotImage) {
            $view = Files::make(
                files: $urls,
                download: false,
            )->render();
        } else {
            $view = Thumbnails::make($urls)
                ->render();
        }

        /** @var string $view */
        return $view;
    }

    protected function prepareFill(array $raw = [], ?DataWrapperContract $casted = null): mixed
    {
        $model = $casted->getOriginal();

        if ($model instanceof HasMedia) {
            return $model->getMedia($this->getColumn())->toArray();
        }

        return parent::prepareFill($raw, $casted);
    }

    protected function viewData(): array
    {
        return [
            'element' => $this,
        ];
    }
}
