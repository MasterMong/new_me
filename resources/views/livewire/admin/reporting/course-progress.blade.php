<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">ความก้าวหน้าตามหลักสูตร</h1>
            <p class="text-sm text-on-surface/60 mt-1">ติดตามภาพรวมการเข้าเรียนและการสอบผ่านของแต่ละหลักสูตร</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4">
        <flux:input 
            wire:model.live.debounce.300ms="search" 
            placeholder="ค้นหาชื่อหลักสูตร..." 
            icon="magnifying-glass"
            class="flex-1"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($courses as $course)
            <div wire:key="course-{{ $course->id }}" class="bg-white rounded-3xl border border-outline-variant/30 overflow-hidden shadow-sm hover:shadow-md transition-all group">
                <div class="aspect-video relative">
                    @if ($course->thumbnail_url)
                        <img src="{{ $course->thumbnail_url }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-primary/5 flex items-center justify-center text-primary/20">
                            <span class="material-symbols-outlined text-[64px]">book_5</span>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-5">
                        <h3 class="text-white font-bold leading-tight line-clamp-2">{{ $course->title }}</h3>
                    </div>
                </div>
                
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-surface p-3 rounded-2xl">
                            <p class="text-[10px] font-bold text-on-surface/40 uppercase tracking-wider">ลงทะเบียนแล้ว</p>
                            <p class="text-xl font-bold text-on-surface">{{ number_format($course->enrollments_count) }}</p>
                        </div>
                        <div class="bg-surface p-3 rounded-2xl">
                            <p class="text-[10px] font-bold text-on-surface/40 uppercase tracking-wider">สำเร็จแล้ว</p>
                            <p class="text-xl font-bold text-green-600">
                                {{ number_format($course->enrollments()->whereNotNull('completed_at')->count()) }}
                            </p>
                        </div>
                    </div>

                    <flux:button variant="primary" class="w-full" wire:click="selectCourse({{ $course->id }})">
                        ดูรายละเอียดเชิงลึก
                        <flux:icon name="arrow-right" variant="micro" class="ms-2" />
                    </flux:button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $courses->links() }}
    </div>
</div>
