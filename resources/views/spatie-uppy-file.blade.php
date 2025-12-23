@php /** @var \GianTiaga\MoonshineFile\Fields\SpatieUppyFile $element */ @endphp
<div
        x-cloak
        x-data='{
        value: @json($value ?: []),
        allowedFileTypes: @json($element->allowedFileTypes()),
        maxNumberOfFiles: {{ $element->countFiles() }},
        endpoint: "{{ route('gt-moonshine-file.upload') }}",
        csrfToken: "{{ csrf_token() }}",
    }'
        x-init="
        const uppy = new Uppy.Uppy({
            locale: {{ str_contains(moonshineConfig()->getLocale(), 'ru') ? 'Uppy.locales.ru_RU' : 'undefined' }},
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
        });
    "
>
    <div x-ref="uppyElement"></div>

    <div>
        <input
                {{ $attributes->merge([
                    'type' => 'text',
                    'style' => 'opacity: 0; z-index: -1; appearance: none; position: relative; top: -40px;',
                    'class' => '',
                    ':value' => "value.length ? JSON.stringify(value) : ''"
                ]) }}
        />
    </div>

    <template x-if="value.length > 0">
        <x-moonshine::form.label>
            @lang('Uploaded Files')
        </x-moonshine::form.label>
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

                        <button type="button" class="mt-2 btn btn-error" @click="value.splice(index, 1)" style="padding: 0.25rem 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>

                            @lang('Remove')
                        </button>
                    </div>
                </template>

                <template x-if="item.mime_type?.search('image') === -1">
                    <div class="dropzone-item dropzone-item-file justify-between">
                        <span class="dropzone-file-icon">
                            <x-moonshine::icon
                                    icon="document"
                                    size="6"
                            />
                        </span>
                        <a :href="item.original_url" class="dropzone-file-name" x-text="item.name" target="_blank"></a>
                        <button type="button" @click="value.splice(index, 1)">
                            @lang('Remove')
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
