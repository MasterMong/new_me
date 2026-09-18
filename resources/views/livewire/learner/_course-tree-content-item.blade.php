@php
    $isCurrentModule = $treeModule->id === $module->id;
    $isActive = $isCurrentModule && $activeContent->id === $treeContent->id;
@endphp

@if(!$treeContent->is_accessible)
    <div
        class="flex items-center gap-2 px-2 py-2 rounded-lg opacity-50 cursor-not-allowed"
        tabindex="-1"
        aria-disabled="true"
    >
        <flux:icon.lock-closed variant="micro" class="size-4 shrink-0 text-on-surface-variant" />
        <span class="text-xs text-on-surface-variant truncate">{{ $treeContent->title }}</span>
    </div>
@elseif($isCurrentModule)
    <button
        type="button"
        wire:click="selectContent({{ $treeContent->id }})"
        @class([
            'w-full flex items-center gap-2 px-2 py-2 rounded-lg text-left transition-colors',
            'bg-primary/10' => $isActive,
            'hover:bg-surface-container-high' => !$isActive,
        ])
    >
        <flux:icon
            :icon="$contentTypeIcon($treeContent->content_type)"
            variant="micro"
            @class(['size-4 shrink-0', 'text-primary' => $isActive, 'text-on-surface-variant' => !$isActive])
        />
        <span @class([
            'text-xs truncate',
            'text-primary font-medium' => $isActive,
            'text-on-surface' => !$isActive,
        ])>{{ $treeContent->title }}</span>
        @if($treeContent->is_completed)
            <flux:icon.check variant="micro" class="size-3.5 shrink-0 text-green-600 ms-auto" />
        @endif
    </button>
@else
    <a
        href="{{ route('learn.courses.play', [$course, $treeModule, $treeContent]) }}"
        wire:navigate
        class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-surface-container-high transition-colors"
    >
        <flux:icon
            :icon="$contentTypeIcon($treeContent->content_type)"
            variant="micro"
            class="size-4 shrink-0 text-on-surface-variant"
        />
        <span class="text-xs text-on-surface truncate">{{ $treeContent->title }}</span>
        @if($treeContent->is_completed)
            <flux:icon.check variant="micro" class="size-3.5 shrink-0 text-green-600 ms-auto" />
        @endif
    </a>
@endif
