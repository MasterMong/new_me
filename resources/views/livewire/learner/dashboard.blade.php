<div class="p-6 space-y-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="mb-1">ยินดีต้อนรับ, {{ auth()->user()->first_name }}</flux:heading>
            <flux:subheading>มาเรียนรู้และพัฒนาศักยภาพไปด้วยกันวันนี้</flux:subheading>
        </div>
        <flux:button variant="ghost" icon="magnifying-glass" href="{{ route('courses.index') }}" wire:navigate>
            ค้นหาคอร์สใหม่
        </flux:button>
    </div>

    @if($activeEnrollment)
        {{-- Active course: the one thing the learner is actually working on right now, given its own full-width treatment rather than competing as a card among cards. --}}
        <div>
            <p class="text-sm font-semibold text-on-surface/60 mb-3">กำลังเรียนอยู่</p>

            <div class="flex flex-col md:flex-row bg-surface-container-lowest rounded-2xl border border-outline-variant/40 overflow-hidden">
                <div class="relative md:w-80 aspect-video md:aspect-auto shrink-0">
                    <img src="{{ $activeEnrollment->course->thumbnail_url ?? 'https://placehold.co/600x400?text=Course' }}"
                         alt="{{ $activeEnrollment->course->title }}"
                         class="w-full h-full object-cover">
                </div>

                <div class="flex-1 p-6 md:p-8 flex flex-col justify-center gap-4">
                    <div>
                        <flux:heading size="lg" class="font-headline mb-1">
                            {{ $activeEnrollment->course->title }}
                        </flux:heading>
                        <p class="text-sm text-on-surface/60">
                            โมดูลที่ {{ $activeEnrollment->completed_module_count + 1 }} จาก {{ $activeEnrollment->total_module_count }}
                        </p>
                    </div>

                    <div class="space-y-2 max-w-md">
                        <div class="flex justify-between items-center text-xs font-semibold">
                            <span class="text-on-surface/50">ความคืบหน้า</span>
                            <span class="text-primary">{{ $activeEnrollment->progress_percent }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-surface-container-high rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all duration-700"
                                 style="width: {{ $activeEnrollment->progress_percent }}%"></div>
                        </div>
                    </div>

                    <div>
                        <flux:button variant="primary" href="{{ route('courses.show', $activeEnrollment->course) }}" wire:navigate>
                            เรียนต่อ
                            <flux:icon.arrow-right variant="mini" />
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($otherEnrollments->isNotEmpty())
        <div>
            <p class="text-sm font-semibold text-on-surface/60 mb-3">
                {{ $activeEnrollment ? 'คอร์สอื่นที่ลงทะเบียนไว้' : 'คอร์สที่ลงทะเบียนไว้' }}
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($otherEnrollments as $enrollment)
                    <a href="{{ route('courses.show', $enrollment->course) }}" wire:navigate
                       class="group flex flex-col rounded-xl border border-outline-variant/40 bg-surface-container-lowest overflow-hidden hover:border-primary/40 transition-colors">
                        <div class="relative aspect-video overflow-hidden">
                            <img src="{{ $enrollment->course->thumbnail_url ?? 'https://placehold.co/600x400?text=Course' }}"
                                 alt="{{ $enrollment->course->title }}"
                                 class="w-full h-full object-cover">
                            @if($enrollment->progress_percent >= 100)
                                <div class="absolute top-3 right-3 flex items-center gap-1 rounded-full bg-secondary-container px-2.5 py-1 text-xs font-semibold text-on-secondary-container">
                                    <span class="material-symbols-outlined text-[14px]">verified</span>
                                    เรียนจบแล้ว
                                </div>
                            @endif
                        </div>

                        <div class="p-4 flex flex-col flex-1 gap-3">
                            <flux:heading size="sm" class="line-clamp-2 leading-snug">
                                {{ $enrollment->course->title }}
                            </flux:heading>

                            <div class="mt-auto space-y-1.5">
                                <div class="h-1 w-full bg-surface-container-high rounded-full overflow-hidden">
                                    <div class="h-full bg-primary/70 rounded-full"
                                         style="width: {{ $enrollment->progress_percent }}%"></div>
                                </div>
                                <p class="text-xs text-on-surface/50">
                                    โมดูลที่ {{ min($enrollment->completed_module_count + 1, $enrollment->total_module_count) }} จาก {{ $enrollment->total_module_count }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(! $hasEnrollments)
        <div class="py-20 flex flex-col items-center justify-center bg-surface-container-lowest rounded-2xl border border-dashed border-outline-variant/60">
            <div class="size-16 mb-6 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[32px]">school</span>
            </div>
            <flux:heading size="lg" class="mb-2">ยังไม่มีคอร์สที่คุณลงทะเบียนไว้</flux:heading>
            <flux:subheading class="mb-6">เริ่มต้นเส้นทางการเรียนรู้ของคุณได้แล้ววันนี้</flux:subheading>
            <flux:button variant="primary" href="{{ route('courses.index') }}" wire:navigate>
                ดูคอร์สทั้งหมด
            </flux:button>
        </div>
    @endif
</div>
