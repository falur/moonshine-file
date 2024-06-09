<?php

declare(strict_types=1);

namespace GianTiaga\MoonshineFile\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Media extends SpatieMedia implements HasMedia
{
    use InteractsWithMedia;
}
