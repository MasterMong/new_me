<div class="p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('learn.dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary mb-4">
            <flux:icon.arrow-left variant="mini" />
            กลับไปยังแดชบอร์ด
        </a>
        <flux:heading size="xl" class="mb-1">{{ $course->title }}</flux:heading>
        <flux:subheading class="mb-4">ติดตามความคืบหน้าการเรียนของคุณและเข้าสู่บทเรียนถัดไป</flux:subheading>

        <div class="flex items-center gap-3 max-w-sm">
            <div class="flex-1 h-2 bg-surface-container-high rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-700" style="width: {{ $enrollment->progress_percent ?? 0 }}%"></div>
            </div>
            <span class="text-sm font-semibold text-on-surface/70 shrink-0">
                {{ $completedModuleCount }} จาก {{ $modules->count() }} โมดูล
            </span>
        </div>
    </div>

    {{-- Spotlight: the one thing to do right now --}}
    @if($nextStep && $nextStep['type'] === 'locked')
        <div class="mb-10 flex gap-4 rounded-2xl border border-error/30 bg-error-container/40 p-6">
            <div class="size-10 shrink-0 rounded-full bg-error text-on-error flex items-center justify-center">
                <flux:icon.lock-closed variant="mini" />
            </div>
            <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-on-error-container mb-1">โมดูลนี้ถูกล็อค</p>
                    <flux:heading size="lg">{{ $nextStep['title'] }}</flux:heading>
                    <p class="text-sm text-on-surface/60 mt-0.5">{{ $nextStep['subtitle'] }}</p>
                </div>
                <flux:button
                    variant="danger"
                    wire:click="restartModule({{ $nextStep['restartModuleId'] }})"
                    wire:confirm="ความคืบหน้าทั้งหมดในโมดูลนี้ (เนื้อหา แบบทดสอบก่อนเรียน และแบบทดสอบหลังเรียน) จะถูกล้าง และเริ่มใหม่ตั้งแต่ต้น ยืนยันหรือไม่?"
                    class="shrink-0"
                >
                    {{ $nextStep['cta'] }}
                </flux:button>
            </div>
        </div>
    @elseif($nextStep)
        <div class="mb-10 flex gap-4 rounded-2xl border border-secondary-container bg-secondary-container/10 p-6">
            <div class="size-10 shrink-0 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container">
                @if($nextStep['type'] === 'done')
                    <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                @elseif($nextStep['type'] === 'review')
                    <flux:icon.star variant="mini" />
                @else
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                @endif
            </div>
            <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-on-secondary-container mb-1">ก้าวต่อไปของคุณ</p>
                    <flux:heading size="lg">{{ $nextStep['title'] }}</flux:heading>
                    <p class="text-sm text-on-surface/60 mt-0.5">{{ $nextStep['subtitle'] }}</p>
                </div>
                <flux:button variant="primary" href="{{ $nextStep['href'] }}" wire:navigate class="shrink-0">
                    {{ $nextStep['cta'] }}
                    <flux:icon.arrow-right variant="mini" />
                </flux:button>
            </div>
        </div>
    @else
        <div class="mb-10 flex items-center gap-4 rounded-2xl border border-outline-variant/40 bg-surface-container-lowest p-6">
            <div class="size-10 shrink-0 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[20px]">hourglass_top</span>
            </div>
            <div>
                <flux:heading size="lg">รอผลการตรวจ</flux:heading>
                <p class="text-sm text-on-surface/60 mt-0.5">คุณทำครบทุกขั้นตอนแล้ว กำลังรอผลการตรวจจากผู้เชี่ยวชาญ</p>
            </div>
        </div>
    @endif

    {{-- Full path --}}
    <div>
        <p class="text-sm font-semibold text-on-surface/60 mb-3">เส้นทางการเรียนทั้งหมด</p>

        <div class="rounded-2xl border border-outline-variant/40 bg-surface-container-lowest divide-y divide-outline-variant/30 overflow-hidden">
            {{-- Pre-test row --}}
            @if($preTest)
                @php
                    $preTestAttempted = $preTest->attempts()->where('user_id', auth()->id())->exists();
                    $preTestIsCurrent = $nextStep && ($nextStep['key'] ?? null) === 'assessment:'.$preTest->id;
                @endphp
                <a href="{{ route('learn.assessments.show', $preTest) }}" wire:navigate
                   class="flex items-center gap-4 px-5 py-4 hover:bg-primary/[0.03] transition-colors {{ $preTestIsCurrent ? 'bg-secondary-container/10' : '' }}">
                    <div @class([
                        'size-9 shrink-0 rounded-full flex items-center justify-center',
                        'bg-primary text-on-primary' => $preTestAttempted,
                        'bg-secondary-container text-on-secondary-container' => ! $preTestAttempted,
                    ])>
                        @if($preTestAttempted)
                            <flux:icon.check variant="mini" />
                        @else
                            <flux:icon.document-text variant="mini" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate">{{ $preTest->title }}</p>
                        <p class="text-xs text-on-surface/50">แบบทดสอบก่อนเรียนของหลักสูตร</p>
                    </div>
                    <span class="text-xs font-semibold text-primary shrink-0">
                        {{ $preTestAttempted ? 'ดูผลคะแนน' : 'เริ่มทำแบบทดสอบ' }}
                    </span>
                </a>
            @endif

            {{-- Module rows --}}
            @foreach($modules as $module)
                @php
                    $isCurrent = $nextStep && ($nextStep['key'] ?? null) === 'module:'.$module->id;
                @endphp
                <div @class([
                    'flex items-center gap-4 px-5 py-4 transition-colors',
                    'bg-secondary-container/10' => $isCurrent,
                    'hover:bg-primary/[0.03]' => $module->is_accessible,
                ])>
                    <div @class([
                        'size-9 shrink-0 rounded-full flex items-center justify-center font-bold text-sm',
                        'bg-primary text-on-primary' => $module->is_completed,
                        'bg-secondary-container text-on-secondary-container' => ! $module->is_completed && $module->is_accessible,
                        'bg-surface-container-high text-on-surface/40' => ! $module->is_accessible,
                    ])>
                        @if($module->is_completed)
                            <flux:icon.check variant="mini" />
                        @elseif(! $module->is_accessible)
                            <flux:icon.lock-closed variant="mini" />
                        @else
                            {{ $module->module_number }}
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate">โมดูล {{ $module->module_number }}: {{ $module->title }}</p>
                        <p class="text-xs {{ $module->is_accessible ? 'text-on-surface/50' : 'text-on-surface/40' }}">
                            @if($module->is_completed)
                                เรียนจบแล้ว
                            @elseif($module->is_accessible)
                                {{ $module->next_action['subtitle'] }}
                            @else
                                <flux:icon.lock-closed variant="micro" class="inline -mt-0.5" />
                                {{ $module->lock_reason }}
                            @endif
                        </p>
                    </div>

                    @if($module->is_completed)
                        <a href="{{ route('learn.courses.play', ['course' => $course->id, 'module' => $module->id]) }}" wire:navigate
                           class="text-xs font-semibold text-primary shrink-0">
                            เรียนอีกครั้ง
                        </a>
                    @elseif($module->is_accessible)
                        <a href="{{ $module->next_action['href'] }}" wire:navigate
                           class="text-xs font-semibold text-primary shrink-0">
                            {{ $module->next_action['label'] }}
                        </a>
                    @endif
                </div>
            @endforeach

            {{-- Post-test row --}}
            @if($postTest)
                @php
                    $allModulesCompleted = $modules->every(fn($m) => $m->is_completed);
                    $postTestPassed = $postTest->attempts->contains(fn($a) => $a->status === \App\Enums\TestAttemptStatus::Passed);
                    $postTestIsCurrent = $nextStep && ($nextStep['key'] ?? null) === 'assessment:'.$postTest->id;
                @endphp
                @if($allModulesCompleted)
                    <a href="{{ route('learn.assessments.show', $postTest) }}" wire:navigate
                       class="flex items-center gap-4 px-5 py-4 hover:bg-primary/[0.03] transition-colors {{ $postTestIsCurrent ? 'bg-secondary-container/10' : '' }}">
                        <div @class([
                            'size-9 shrink-0 rounded-full flex items-center justify-center',
                            'bg-primary text-on-primary' => $postTestPassed,
                            'bg-secondary-container text-on-secondary-container' => ! $postTestPassed,
                        ])>
                            @if($postTestPassed)
                                <flux:icon.check variant="mini" />
                            @else
                                <flux:icon.trophy variant="mini" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate">{{ $postTest->title }}</p>
                            <p class="text-xs text-on-surface/50">แบบทดสอบหลังเรียนสรุปหลักสูตร</p>
                        </div>
                        <span class="text-xs font-semibold text-primary shrink-0">
                            {{ $postTestPassed ? 'ดูผลคะแนน' : 'เริ่มทำแบบทดสอบ' }}
                        </span>
                    </a>
                @else
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="size-9 shrink-0 rounded-full bg-surface-container-high text-on-surface/40 flex items-center justify-center">
                            <flux:icon.lock-closed variant="mini" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold truncate">{{ $postTest->title }}</p>
                            <p class="text-xs text-on-surface/40">
                                <flux:icon.lock-closed variant="micro" class="inline -mt-0.5" />
                                ต้องเรียนจบทุกโมดูลก่อน
                            </p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
