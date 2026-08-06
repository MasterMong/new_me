<div class="p-6 max-w-5xl mx-auto">
    {{-- Course Header --}}
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-primary mb-4">
                <flux:icon.arrow-left variant="mini" class="cursor-pointer" href="{{ route('learn.dashboard') }}" wire:navigate />
                <flux:text class="text-primary font-semibold">กลับไปยังแดชบอร์ด</flux:text>
            </div>
            <flux:heading size="xl" class="mb-2">{{ $course->title }}</flux:heading>
            <flux:subheading>ติดตามความคืบหน้าการเรียนของคุณและเข้าสู่บทเรียนถัดไป</flux:subheading>
        </div>
        
        <div class="bg-primary/5 px-6 py-4 rounded-2xl border border-primary/10 flex items-center gap-4">
            <div class="size-12 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[28px]">analytics</span>
            </div>
            <div>
                <flux:text class="text-xs font-bold text-primary uppercase tracking-wider">ภาพรวมความสำเร็จ</flux:text>
                <flux:heading size="lg">{{ $enrollment->progress_percent ?? 0 }}%</flux:heading>
            </div>
        </div>
    </div>

    {{-- Learning Timeline --}}
    <div class="relative space-y-8">
        {{-- Vertical Line Connector --}}
        <div class="absolute left-6 top-8 bottom-8 w-0.5 bg-zinc-200 dark:bg-zinc-800 -z-10"></div>

        {{-- Pre-Test Section --}}
        @if($preTest)
            <div class="relative flex gap-6">
                <div @class([
                    'size-12 rounded-full border-4 flex items-center justify-center shrink-0 z-10 transition-colors duration-500',
                    'bg-green-500 border-green-100 text-white' => $preTest->attempts()->where('user_id', auth()->id())->exists(),
                    'bg-white dark:bg-zinc-900 border-primary text-primary shadow-lg shadow-primary/20' => !$preTest->attempts()->where('user_id', auth()->id())->exists(),
                ])>
                    @if($preTest->attempts()->where('user_id', auth()->id())->exists())
                        <flux:icon.check variant="mini" />
                    @else
                        <span class="material-symbols-outlined text-[20px]">assignment</span>
                    @endif
                </div>
                
                <div class="flex-1 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm transition-all hover:shadow-md">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <flux:badge variant="primary" class="mb-2">แบบทดสอบก่อนเรียน (Pre-test)</flux:badge>
                            <flux:heading size="lg">{{ $preTest->title }}</flux:heading>
                            <flux:subheading class="mt-1">ประเมินความรู้พื้นฐานก่อนเริ่มบทเรียน</flux:subheading>
                        </div>
                        <flux:button variant="primary" 
                                     href="{{ route('learn.assessments.show', $preTest) }}"
                                     class="shrink-0">
                            {{ $preTest->attempts()->where('user_id', auth()->id())->exists() ? 'ดูผลคะแนน' : 'เริ่มทำแบบทดสอบ' }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modules Section --}}
        @foreach($modules as $index => $module)
            <div class="relative flex gap-6">
                {{-- Status Indicator --}}
                <div @class([
                    'size-12 rounded-full border-4 flex items-center justify-center shrink-0 z-10 transition-all duration-500',
                    'bg-green-500 border-green-100 text-white' => $module->is_completed,
                    'bg-primary border-primary/20 text-white shadow-lg shadow-primary/20' => !$module->is_completed && $module->is_accessible,
                    'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400' => !$module->is_accessible,
                ])>
                    @if($module->is_completed)
                        <flux:icon.check variant="mini" />
                    @elseif(!$module->is_accessible)
                        <flux:icon.lock-closed variant="mini" />
                    @else
                        <flux:text class="text-white font-bold">{{ $index + 1 }}</flux:text>
                    @endif
                </div>

                <div @class([
                    'flex-1 p-6 rounded-3xl border transition-all duration-300',
                    'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-md' => $module->is_accessible,
                    'bg-zinc-50 dark:bg-zinc-900/30 border-transparent grayscale' => !$module->is_accessible,
                ])>
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <flux:badge variant="neutral">โมดูล {{ $module->module_number }}</flux:badge>
                                @if(!$module->is_accessible)
                                    <span class="text-xs font-semibold text-zinc-500 flex items-center gap-1">
                                        <flux:icon.lock-closed variant="micro" />
                                        ล็อค
                                    </span>
                                @endif
                            </div>
                            <flux:heading size="lg" class="mb-2">{{ $module->title }}</flux:heading>
                            <flux:subheading class="line-clamp-2">{{ $module->description }}</flux:subheading>
                            
                            @if($module->is_accessible)
                                <div class="mt-4 flex items-center gap-4">
                                    <div class="flex-1 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full transition-all duration-700" style="width: {{ $module->progress_percent }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-zinc-500">{{ $module->progress_percent }}%</span>
                                </div>
                            @endif

                            @if($module->pre_test || $module->post_test)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if($module->pre_test)
                                        @php $preTestAttempted = $module->pre_test->attempts->isNotEmpty(); @endphp
                                        <flux:button
                                            size="sm"
                                            :variant="$preTestAttempted ? 'ghost' : 'primary'"
                                            href="{{ route('learn.assessments.show', $module->pre_test) }}"
                                        >
                                            <flux:icon.document-text variant="micro" class="me-1" />
                                            {{ $preTestAttempted ? 'ดูผลก่อนเรียน' : 'ทำแบบทดสอบก่อนเรียน' }}
                                        </flux:button>
                                    @endif
                                    @if($module->post_test)
                                        @php
                                            $postTestPassed = $module->post_test->attempts->contains(fn($a) => $a->status === \App\Enums\TestAttemptStatus::Passed);
                                            $postTestUnlocked = $module->progress_percent === 100;
                                        @endphp
                                        <flux:button
                                            size="sm"
                                            :variant="$postTestPassed ? 'ghost' : 'primary'"
                                            :disabled="!$postTestUnlocked"
                                            href="{{ $postTestUnlocked ? route('learn.assessments.show', $module->post_test) : '#' }}"
                                        >
                                            <flux:icon.trophy variant="micro" class="me-1" />
                                            {{ $postTestPassed ? 'ดูผลหลังเรียน' : 'ทำแบบทดสอบหลังเรียน' }}
                                        </flux:button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 min-w-[140px]">
                            <flux:button 
                                :variant="$module->is_accessible ? 'primary' : 'subtle'" 
                                :disabled="!$module->is_accessible"
                                href="{{ $module->is_accessible ? route('learn.courses.play', ['course' => $course->id, 'module' => $module->id]) : '#' }}"
                                class="w-full"
                            >
                                @if($module->is_completed)
                                    เรียนอีกครั้ง
                                @elseif($module->progress_percent > 0)
                                    เรียนต่อ
                                @else
                                    เริ่มบทเรียน
                                @endif
                            </flux:button>

                            @if(!$module->is_accessible)
                                <flux:tooltip position="bottom" align="center">
                                    <x-slot name="content">
                                        {{ __('กรุณาเรียนผ่านโมดูลก่อนหน้าเพื่อปลดล็อค') }}
                                    </x-slot>
                                    <flux:button variant="ghost" size="sm" class="w-full text-xs text-zinc-500">
                                        <flux:icon.information-circle variant="micro" class="mr-1" />
                                        ดูเงื่อนไข
                                    </flux:button>
                                </flux:tooltip>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Post-Test Section --}}
        @if($postTest)
            @php
                $allModulesCompleted = $modules->every(fn($m) => $m->is_completed);
            @endphp
            <div class="relative flex gap-6 opacity-{{ $allModulesCompleted ? '100' : '50' }}">
                <div @class([
                    'size-12 rounded-full border-4 flex items-center justify-center shrink-0 z-10',
                    'bg-primary border-primary/20 text-white' => $allModulesCompleted,
                    'bg-zinc-100 border-zinc-200 text-zinc-400' => !$allModulesCompleted,
                ])>
                    <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                </div>
                
                <div class="flex-1 bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <flux:badge variant="neutral" class="mb-2">แบบทดสอบหลังเรียน (Post-test)</flux:badge>
                            <flux:heading size="lg">{{ $postTest->title }}</flux:heading>
                            <flux:subheading class="mt-1">ประเมินผลการเรียนรู้สรุปทุกโมดูลเพื่อรับเกียรติบัตร</flux:subheading>
                        </div>
                        <flux:button 
                            :variant="$allModulesCompleted ? 'primary' : 'subtle'" 
                            :disabled="!$allModulesCompleted"
                            href="{{ $allModulesCompleted ? route('learn.assessments.show', $postTest) : '#' }}"
                            class="shrink-0"
                        >
                            เริ่มทำแบบทดสอบ
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
