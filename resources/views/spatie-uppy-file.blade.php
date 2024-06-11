@php /** @var \App\MoonShine\Fields\SpatieUppyFile $element */ @endphp
@php
    $column = $element->column();
    $parent = $element->parent();
    $elData = match (true) {
        $parent instanceof \MoonShine\Fields\Json => $parent,
        default => $element,
    };
@endphp

<div
        x-cloak
        x-data='{
        value: @json(json_decode($element->value()) ?: []),
        allowedFileTypes: @json($element->allowedFileTypes()),
        maxNumberOfFiles: {{ $element->countFiles() }},
        endpoint: "{{ route('gt-moonshine-file.upload') }}",
        csrfToken: "{{ csrf_token() }}",
    }'
        x-init="
    const uppy = new Uppy.Uppy({
            locale: Uppy.locales.ru_RU,
            restrictions: {
                allowedFileTypes: allowedFileTypes,
                maxNumberOfFiles: maxNumberOfFiles,
            },
        })
        .use(Uppy.Dashboard, {
            inline: true,
            target: $refs.uppyElement,
            width: '100%',
        })
        .use(Uppy.ImageEditor, {
            target: Uppy.Dashboard
        })
        .use(Uppy.XHRUpload, {
            endpoint: endpoint,
            headers: {
                'X-CSRF-Token': csrfToken,
            },
        });

    uppy.on('upload-success', function (_, resp) {
        if (maxNumberOfFiles === 1 && !!resp.body.id) {
            value = [resp.body];
        } else if (resp.body.id) {
            value.push(resp.body);
        }
    });">
    <div x-ref="uppyElement"></div>

    <div style="margin-bottom: -40px;">
        <input
                {{ $element->attributes()->merge([
                    'type' => 'text',
                    'style' => 'opacity: 0; z-index: -1; appearance: none; position: relative; top: -40px;',
                    'name' => $element->name(),
                ]) }}
                :value="value.length ? JSON.stringify(value) : ''"
        />
    </div>

    <template x-if="value.length > 0">
        <div class="mt-5">
            <b>Загруженные файлы</b>
        </div>
    </template>
    <div class="flex flex-wrap gap-2 mt-2" :class="value[0]?.mime_type?.search('image') === -1 ? 'flex-col' : 'flex-row'">
        <template x-for="(item, index) in value">
            <div>
                <template x-if="item.mime_type?.search('image') !== -1">
                    <div class="flex flex-col"
                         style="align-items: flex-end"
                    >
                        <buttom
                                type="button"
                                @click.stop="$dispatch('img-popup', {open: true, src: item.original_url })"
                        >
                            <img
                                    style="object-fit: cover; width: 200px; height: 120px;"
                                    class="rounded-lg"
                                    :src="item.original_url"
                                    :alt="item.name"
                            >
                        </buttom>

                        <button type="button" @click="value.splice(index, 1)">
                            Удалить
                        </button>
                    </div>
                </template>
                <template x-if="item.mime_type?.search('image') === -1">
                    <div class="dropzone-item dropzone-item-file justify-between">
                        <span class="dropzone-file-icon">
                            <x-moonshine::icon
                                    icon="heroicons.document"
                                    size="6"
                            />
                        </span>
                        <a :href="item.original_url" class="dropzone-file-name" x-text="item.name" target="_blank"></a>
                        <button type="button" @click="value.splice(index, 1)">
                            Удалить
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
