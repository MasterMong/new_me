<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-2">ยินดีต้อนรับ, {{ auth()->user()->first_name }}!</flux:heading>
            <flux:subheading>มาเรียนรู้และพัฒนาศักยภาพไปด้วยกันวันนี้</flux:subheading>
        </div>
        <flux:button variant="primary" icon="magnifying-glass" href="{{ route('courses.index') }}" wire:navigate>
            ค้นหาคอร์สใหม่
        </flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($enrollments as $enrollment)
            <div class="group relative flex flex-col bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden transition-all hover:shadow-2xl hover:shadow-primary/10 hover:-translate-y-1">
                {{-- Course Thumbnail --}}
                <div class="relative aspect-video overflow-hidden">
                    <img src="{{ $enrollment->course->thumbnail_url ?? 'https://placehold.co/600x400?text=Course' }}" 
                         alt="{{ $enrollment->course->title }}"
                         class="w-full h-full object-cover transition-transform group-hover:scale-110 duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-4 left-4">
                        <flux:badge variant="primary" class="bg-primary/90 backdrop-blur-md border-0 text-white">
                            {{ $enrollment->status->label() }}
                        </flux:badge>
                    </div>
                </div>

                {{-- Course Info --}}
                <div class="p-6 flex flex-col flex-1">
                    <flux:heading size="lg" class="mb-3 line-clamp-2 leading-tight">
                        {{ $enrollment->course->title }}
                    </flux:heading>
                    
                    <flux:text class="mb-6 line-clamp-2 text-sm opacity-70">
                        {{ $enrollment->course->description }}
                    </flux:text>

                    <div class="mt-auto space-y-4">
                        {{-- Progress Bar --}}
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs font-semibold">
                                <span class="text-zinc-500">ความคืบหน้า</span>
                                <span class="text-primary">{{ $enrollment->progress_percent }}%</span>
                            </div>
                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full transition-all duration-1000" 
                                     style="width: {{ $enrollment->progress_percent }}%"></div>
                            </div>
                        </div>

                        <flux:button variant="primary" class="w-full justify-between" 
                                     href="{{ route('courses.show', $enrollment->course) }}" wire:navigate>
                            <span>{{ $enrollment->progress_percent > 0 ? 'เรียนต่อ' : 'เริ่มต้นเรียน' }}</span>
                            <flux:icon.arrow-right variant="mini" />
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                <div class="size-20 mb-6 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[40px]">school</span>
                </div>
                <flux:heading size="lg" class="mb-2">ยังไม่มีคอร์สที่คุณลงทะเบียนไว้</flux:heading>
                <flux:subheading class="mb-6">เริ่มต้นเส้นทางการเรียนรู้ของคุณได้แล้ววันนี้</flux:subheading>
                <flux:button variant="primary" href="{{ route('courses.index') }}" wire:navigate>
                    ดูคอร์สทั้งหมด
                </flux:button>
            </div>
        @endforelse
    </div>
</div>
