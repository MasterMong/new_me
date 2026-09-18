<div
    x-data="richEditor({
        model: '{{ $model }}',
        initialValue: @js($value ?? ''),
        placeholder: '{{ $placeholder ?? '' }}',
        enableImages: {{ ($imageModel ?? null) ? 'true' : 'false' }}
    })"
    @content-image-uploaded.window="onImageUploaded($event.detail.url)"
>
    <div wire:ignore>
        <div x-ref="quill"></div>
    </div>

    @if($imageModel ?? null)
        <input type="file" wire:model="{{ $imageModel }}" x-ref="imageInput" accept="image/*" class="hidden">
    @endif
</div>
