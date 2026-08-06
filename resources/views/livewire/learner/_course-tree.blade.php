@php
    $contentTypeIcon = fn (\App\Enums\ContentType $type) => match ($type) {
        \App\Enums\ContentType::Video => 'play-circle',
        \App\Enums\ContentType::Document => 'document-text',
        \App\Enums\ContentType::Link => 'link',
        \App\Enums\ContentType::Test => 'clipboard-document-check',
    };
@endphp

<div class="w-96 bg-surface-container-low border-l border-outline-variant flex flex-col shrink-0 overflow-hidden">
    <div class="p-6 border-b border-outline-variant shrink-0">
        <flux:heading class="text-on-surface mb-1">เนื้อหาในคอร์ส</flux:heading>
        <flux:subheading class="text-on-surface-variant">{{ $tree->count() }} โมดูล</flux:subheading>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar py-2">
        {{-- Course Pre-Test --}}
        @if($coursePreTest)
            @php $preTestDone = $coursePreTest->attempts()->where('user_id', auth()->id())->exists(); @endphp
            <a
                href="{{ route('learn.assessments.show', $coursePreTest) }}"
                wire:navigate
                class="flex items-center gap-3 px-4 py-3 mx-2 rounded-xl hover:bg-surface-container-high transition-colors"
            >
                <div @class([
                    'size-7 rounded-full flex items-center justify-center shrink-0',
                    'bg-green-500 text-white' => $preTestDone,
                    'bg-primary/10 text-primary' => !$preTestDone,
                ])>
                    @if($preTestDone)
                        <flux:icon.check variant="micro" />
                    @else
                        <flux:icon.document-text variant="micro" />
                    @endif
                </div>
                <span class="text-sm font-medium text-on-surface truncate">แบบทดสอบก่อนเรียน</span>
            </a>
        @endif

        {{-- Modules --}}
        @foreach($tree as $treeModule)
            <ui-disclosure
                class="block mx-2 my-1"
                @if(in_array($treeModule->id, $expandedModuleIds)) open @endif
            >
                <button
                    type="button"
                    wire:click="toggleModule({{ $treeModule->id }})"
                    @class([
                        'w-full flex items-center gap-3 px-2 py-2 rounded-xl text-left transition-colors',
                        'bg-primary/5' => $treeModule->id === $module->id,
                        'hover:bg-surface-container-high' => $treeModule->id !== $module->id,
                    ])
                >
                    <flux:icon.chevron-down variant="micro" class="hidden size-3! shrink-0 text-on-surface-variant group-data-open/disclosure-button:block" />
                    <flux:icon.chevron-right variant="micro" class="block size-3! shrink-0 text-on-surface-variant group-data-open/disclosure-button:hidden" />

                    <div @class([
                        'size-7 rounded-full flex items-center justify-center shrink-0 text-xs font-bold',
                        'bg-green-500 text-white' => $treeModule->is_completed,
                        'bg-primary text-on-primary' => !$treeModule->is_completed && $treeModule->is_accessible,
                        'bg-surface-container-highest text-on-surface-variant' => !$treeModule->is_accessible,
                    ])>
                        @if($treeModule->is_completed)
                            <flux:icon.check variant="micro" />
                        @elseif(!$treeModule->is_accessible)
                            <flux:icon.lock-closed variant="micro" />
                        @else
                            {{ $treeModule->module_number }}
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-on-surface truncate">{{ $treeModule->title }}</p>
                        @if($treeModule->is_accessible)
                            <p class="text-[11px] text-on-surface-variant">{{ $treeModule->progress_percent }}% เสร็จสมบูรณ์</p>
                        @endif
                    </div>
                </button>

                <div
                    class="relative hidden space-y-[2px] ps-9 pe-2 py-1 data-open:block"
                    @if(in_array($treeModule->id, $expandedModuleIds)) data-open @endif
                >
                    <div class="absolute inset-y-[3px] start-0 ms-[18px] w-px bg-outline-variant"></div>

                    @if($treeModule->pre_test)
                        @php $modulePreTestDone = $treeModule->pre_test->attempts->isNotEmpty(); @endphp
                        <a
                            href="{{ route('learn.assessments.show', $treeModule->pre_test) }}"
                            wire:navigate
                            class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-surface-container-high transition-colors"
                        >
                            <flux:icon.document-text variant="micro" class="size-4 shrink-0 text-on-surface-variant" />
                            <span class="text-xs text-on-surface-variant truncate">แบบทดสอบก่อนเรียนของโมดูล</span>
                            @if($modulePreTestDone)
                                <flux:icon.check variant="micro" class="size-3.5 shrink-0 text-green-600 ms-auto" />
                            @endif
                        </a>
                    @endif

                    @foreach($treeModule->contents as $treeContent)
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
                                href="{{ route('learn.courses.play', [$course, $treeModule]) }}"
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
                    @endforeach

                    @if($treeModule->post_test)
                        @php
                            $modulePostTestDone = $treeModule->post_test->attempts->contains(fn ($a) => $a->status === \App\Enums\TestAttemptStatus::Passed);
                            $modulePostTestUnlocked = $treeModule->progress_percent === 100;
                        @endphp
                        @if($modulePostTestUnlocked)
                            <a
                                href="{{ route('learn.assessments.show', $treeModule->post_test) }}"
                                wire:navigate
                                class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-surface-container-high transition-colors"
                            >
                                <flux:icon.trophy variant="micro" class="size-4 shrink-0 text-on-surface-variant" />
                                <span class="text-xs text-on-surface truncate">แบบทดสอบหลังเรียนของโมดูล</span>
                                @if($modulePostTestDone)
                                    <flux:icon.check variant="micro" class="size-3.5 shrink-0 text-green-600 ms-auto" />
                                @endif
                            </a>
                        @else
                            <div class="flex items-center gap-2 px-2 py-2 rounded-lg opacity-50 cursor-not-allowed" tabindex="-1" aria-disabled="true">
                                <flux:icon.lock-closed variant="micro" class="size-4 shrink-0 text-on-surface-variant" />
                                <span class="text-xs text-on-surface-variant truncate">แบบทดสอบหลังเรียนของโมดูล</span>
                            </div>
                        @endif
                    @endif
                </div>
            </ui-disclosure>
        @endforeach

        {{-- Course Post-Test --}}
        @if($coursePostTest)
            @php
                $allModulesCompleted = $tree->every(fn ($m) => $m->is_completed);
                $postTestDone = $coursePostTest->attempts()->where('user_id', auth()->id())->exists();
            @endphp
            @if($allModulesCompleted)
                <a
                    href="{{ route('learn.assessments.show', $coursePostTest) }}"
                    wire:navigate
                    class="flex items-center gap-3 px-4 py-3 mx-2 rounded-xl hover:bg-surface-container-high transition-colors"
                >
                    <div @class([
                        'size-7 rounded-full flex items-center justify-center shrink-0',
                        'bg-green-500 text-white' => $postTestDone,
                        'bg-primary/10 text-primary' => !$postTestDone,
                    ])>
                        @if($postTestDone)
                            <flux:icon.check variant="micro" />
                        @else
                            <flux:icon.trophy variant="micro" />
                        @endif
                    </div>
                    <span class="text-sm font-medium text-on-surface truncate">แบบทดสอบหลังเรียน</span>
                </a>
            @else
                <div class="flex items-center gap-3 px-4 py-3 mx-2 rounded-xl opacity-50 cursor-not-allowed" tabindex="-1" aria-disabled="true">
                    <div class="size-7 rounded-full flex items-center justify-center shrink-0 bg-surface-container-highest text-on-surface-variant">
                        <flux:icon.lock-closed variant="micro" />
                    </div>
                    <span class="text-sm font-medium text-on-surface-variant truncate">แบบทดสอบหลังเรียน</span>
                </div>
            @endif
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--color-outline-variant);
        border-radius: 10px;
    }
</style>
