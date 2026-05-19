<div
    wire:ignore
    x-data="richEditor({
        model: '{{ $model }}',
        initialValue: @js($value ?? ''),
        placeholder: '{{ $placeholder ?? '' }}'
    })"
>
    <div x-ref="quill"></div>
</div>
