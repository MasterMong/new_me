<div class="p-6 space-y-8 max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="size-20 rounded-[2rem] bg-primary shadow-xl shadow-primary/20 flex items-center justify-center text-on-primary text-3xl font-bold font-headline">
                {{ $user->initials() }}
            </div>
            <div>
                <h1 class="text-3xl font-bold font-headline text-on-surface tracking-tight">สรุปความก้าวหน้า</h1>
                <p class="text-on-surface/60 font-medium">ติดตามการเรียนรู้และความสำเร็จของคุณ</p>
            </div>
        </div>
        <div class="flex gap-3">
            <flux:button variant="ghost" icon="printer" class="hidden sm:inline-flex">พิมพ์รายงาน</flux:button>
            <flux:button variant="primary" icon="academic-cap" wire:navigate :href="route('learn.dashboard')">เรียนต่อ</flux:button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[2rem] border border-outline-variant/30 shadow-sm space-y-1">
            <p class="text-xs font-bold text-on-surface/40 uppercase tracking-widest">คอร์สทั้งหมด</p>
            <p class="text-3xl font-bold text-on-surface">{{ $stats['total_enrolled'] }}</p>
            <div class="text-[10px] text-on-surface/30">ลงทะเบียนเรียน</div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-outline-variant/30 shadow-sm space-y-1">
            <p class="text-xs font-bold text-on-surface/40 uppercase tracking-widest">เรียนจบแล้ว</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            <div class="text-[10px] text-on-surface/30">ได้รับเกียรติบัตร</div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-outline-variant/30 shadow-sm space-y-1">
            <p class="text-xs font-bold text-on-surface/40 uppercase tracking-widest">กำลังดำเนินการ</p>
            <p class="text-3xl font-bold text-amber-500">{{ $stats['in_progress'] }}</p>
            <div class="text-[10px] text-on-surface/30">อยู่ระหว่างการเรียน</div>
        </div>
        <div class="bg-white p-6 rounded-[2rem] border border-outline-variant/30 shadow-sm space-y-1">
            <p class="text-xs font-bold text-on-surface/40 uppercase tracking-widest">เฉลี่ยรวม</p>
            <p class="text-3xl font-bold text-primary">{{ round($stats['avg_progress']) }}%</p>
            <div class="h-1.5 w-full bg-surface rounded-full overflow-hidden mt-2">
                <div class="h-full bg-primary rounded-full" style="width: {{ $stats['avg_progress'] }}%"></div>
            </div>
        </div>
    </div>

    {{-- Course List --}}
    <div class="bg-white rounded-[2.5rem] border border-outline-variant/30 overflow-hidden shadow-sm">
        <div class="px-8 py-6 border-b border-outline-variant/30 flex items-center justify-between bg-surface/30">
            <h3 class="text-xl font-bold font-headline text-on-surface">รายละเอียดรายวิชา</h3>
            <div class="flex items-center gap-2">
                <flux:badge color="zinc" size="sm">เรียงตามล่าสุด</flux:badge>
            </div>
        </div>
        
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-8 py-4">หลักสูตร</flux:table.column>
                <flux:table.column>ความคืบหน้า</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column>วันที่เริ่ม</flux:table.column>
                <flux:table.column class="pe-8"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($enrollments as $enrollment)
                    <flux:table.row wire:key="row-{{ $enrollment->id }}" class="hover:bg-primary/5 transition-colors group">
                        <flux:table.cell class="ps-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-2xl overflow-hidden shrink-0 border border-outline-variant/10">
                                    <img src="{{ $enrollment->course->thumbnail_url ?? 'https://placehold.co/100x100?text=Course' }}" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface leading-tight truncate group-hover:text-primary transition-colors">{{ $enrollment->course->title }}</p>
                                    <p class="text-[10px] text-on-surface/40 mt-1 uppercase font-bold tracking-wider">{{ $enrollment->course->modules->count() }} Modules</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="w-full max-w-[160px] space-y-1.5">
                                <div class="flex justify-between items-center text-[10px] font-bold">
                                    <span class="text-on-surface/40 uppercase tracking-tighter">Progress</span>
                                    <span class="text-primary">{{ $enrollment->progress_percent }}%</span>
                                </div>
                                <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full bg-primary rounded-full transition-all duration-1000" style="width: {{ $enrollment->progress_percent }}%"></div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($enrollment->progress_percent === 100)
                                <flux:badge color="green" size="sm" icon="check-circle">สำเร็จแล้ว</flux:badge>
                            @elseif ($enrollment->progress_percent > 0)
                                <flux:badge color="amber" size="sm" icon="clock">กำลังเรียน</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">ยังไม่เริ่ม</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-[11px] font-bold text-on-surface/50">
                            {{ $enrollment->enrolled_at->translatedFormat('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="pe-8 text-right">
                            <flux:button variant="ghost" size="sm" icon="chevron-right" wire:navigate :href="route('learn.courses.show', $enrollment->course)" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center opacity-30">
                                <span class="material-symbols-outlined text-[48px]">history</span>
                                <p class="mt-2 font-bold uppercase tracking-widest text-xs">No activity found</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
