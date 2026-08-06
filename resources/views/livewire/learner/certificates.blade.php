<div class="p-6 max-w-6xl mx-auto">
    {{-- Header & Stats --}}
    <div class="mb-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <flux:heading size="xl" class="mb-2">เกียรติบัตรและความสำเร็จ</flux:heading>
                <flux:subheading>รวมความภาคภูมิใจและเส้นทางการพัฒนาตนเองของคุณ</flux:subheading>
            </div>
            
            <div class="flex gap-4">
                <div class="px-6 py-4 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col items-center min-w-[120px]">
                    <span class="text-3xl font-black text-primary">{{ $stats['total_earned'] }}</span>
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-1">ได้รับแล้ว</span>
                </div>
                <div class="px-6 py-4 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col items-center min-w-[120px]">
                    <span class="text-3xl font-black text-zinc-400">{{ $stats['in_progress'] }}</span>
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest mt-1">กำลังเรียน</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Certificates Gallery --}}
    <div class="mb-16">
        <div class="flex items-center gap-3 mb-8">
            <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">workspace_premium</span>
            </div>
            <flux:heading size="lg">เกียรติบัตรที่คุณได้รับ</flux:heading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($certificates as $cert)
                <div class="group bg-white dark:bg-zinc-900 rounded-[2rem] border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                    {{-- Cert Preview --}}
                    <div class="aspect-[1.414/1] bg-zinc-100 dark:bg-zinc-800 relative flex items-center justify-center p-8 overflow-hidden">
                        <div class="absolute inset-0 opacity-10 pointer-events-none">
                            <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="0.5" />
                                <circle cx="50" cy="50" r="30" stroke="currentColor" stroke-width="0.5" />
                            </svg>
                        </div>
                        <div class="relative z-10 text-center">
                            <span class="material-symbols-outlined text-[64px] text-primary/40 mb-4">school</span>
                            <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-[0.2em] mb-1">CERTIFICATE OF COMPLETION</p>
                            <p class="text-xs font-serif italic text-zinc-600 dark:text-zinc-400">issued to {{ auth()->user()->fullName() }}</p>
                        </div>
                        
                        <div class="absolute inset-0 bg-primary/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3 backdrop-blur-sm">
                            @if($cert->pdf_url)
                                <flux:button variant="primary" size="sm" class="bg-white text-primary hover:bg-zinc-50 border-0" href="{{ $cert->pdf_url }}" target="_blank">
                                    <flux:icon.eye variant="mini" class="mr-2" />
                                    ดูออนไลน์
                                </flux:button>
                            @else
                                <flux:badge color="zinc" size="sm">กำลังเตรียมไฟล์</flux:badge>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        <flux:heading size="lg" class="mb-2 line-clamp-1">{{ $cert->course->title }}</flux:heading>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">วันที่ได้รับ: {{ $cert->issued_date->format('d M Y') }}</span>
                            <span class="font-bold text-primary">{{ round($cert->final_score_pct) }}%</span>
                        </div>
                        <div class="mt-6">
                            @if($cert->pdf_url)
                                <flux:button variant="primary" class="w-full" href="{{ $cert->pdf_url }}" download>
                                    <flux:icon.arrow-down-tray variant="mini" class="mr-2" />
                                    ดาวน์โหลด PDF
                                </flux:button>
                            @else
                                <flux:button variant="subtle" class="w-full" disabled>
                                    <flux:icon.arrow-path variant="mini" class="mr-2" />
                                    ไฟล์ PDF กำลังเตรียมการ
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-900/50 rounded-[2.5rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                    <div class="size-20 mb-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                        <span class="material-symbols-outlined text-[40px]">card_membership</span>
                    </div>
                    <flux:heading size="lg" class="mb-2">ยังไม่มีเกียรติบัตร</flux:heading>
                    <flux:subheading>เรียนให้จบและสอบผ่านเกณฑ์เพื่อรับเกียรติบัตรใบแรกของคุณ</flux:subheading>
                </div>
            @endforelse
        </div>
    </div>

    {{-- In Progress Section --}}
    <div>
        <div class="flex items-center gap-3 mb-8">
            <div class="size-10 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                <span class="material-symbols-outlined">trending_up</span>
            </div>
            <flux:heading size="lg">คอร์สที่กำลังดำเนินการ</flux:heading>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($ongoingCourses as $ongoing)
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 flex items-center gap-6 group hover:border-primary/30 transition-all">
                    <div class="size-20 rounded-2xl overflow-hidden shrink-0">
                        <img src="{{ $ongoing->course->thumbnail_url ?? 'https://placehold.co/200' }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <flux:heading size="md" class="mb-3 line-clamp-1">{{ $ongoing->course->title }}</flux:heading>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-zinc-500 font-medium">ความคืบหน้า</span>
                                <span class="text-primary font-bold">{{ $ongoing->progress_percent }}%</span>
                            </div>
                            <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full transition-all duration-1000" style="width: {{ $ongoing->progress_percent }}%"></div>
                            </div>
                        </div>
                    </div>
                    <flux:button variant="ghost" icon="arrow-right" href="{{ route('learn.courses.show', $ongoing->course) }}" wire:navigate />
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-zinc-500 italic">
                    ไม่มีคอร์สที่กำลังเรียนอยู่ในขณะนี้
                </div>
            @endforelse
        </div>
    </div>
</div>
