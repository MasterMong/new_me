<div class="p-6 space-y-6">
    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.text>{{ session('status') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">จัดการหลักสูตร</h1>
            <p class="text-sm text-on-surface/60 mt-1">บริหารจัดการเนื้อหา บทเรียน และการเผยแพร่ในระบบ ME-Learning</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:navigate :href="route('admin.courses.create')" class="shadow-lg shadow-primary/20">
            สร้างหลักสูตรใหม่
        </flux:button>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
            $totalActive = \App\Models\Course::where('is_published', true)->count();
            $totalDraft = \App\Models\Course::where('is_published', false)->count();
            $totalEnrollments = \App\Models\Enrollment::count();
        @endphp
        <div class="bg-white p-4 rounded-2xl border border-outline-variant/30 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                <span class="material-symbols-outlined text-[24px]">rocket_launch</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-on-surface">{{ number_format($totalActive) }}</p>
                <p class="text-[10px] font-bold text-on-surface/40 uppercase tracking-wider">Published Courses</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-outline-variant/30 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-zinc-50 flex items-center justify-center text-zinc-400">
                <span class="material-symbols-outlined text-[24px]">draft</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-on-surface">{{ number_format($totalDraft) }}</p>
                <p class="text-[10px] font-bold text-on-surface/40 uppercase tracking-wider">Drafts</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-outline-variant/30 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[24px]">group</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-on-surface">{{ number_format($totalEnrollments) }}</p>
                <p class="text-[10px] font-bold text-on-surface/40 uppercase tracking-wider">Total Learners</p>
            </div>
        </div>
    </div>


    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="ค้นหาชื่อคอร์ส..."
                icon="magnifying-glass"
                clearable
            />
        </div>
        <flux:select wire:model.live="statusFilter" class="sm:w-44">
            <flux:select.option value="">ทุกสถานะ</flux:select.option>
            <flux:select.option value="1">เผยแพร่แล้ว</flux:select.option>
            <flux:select.option value="0">ฉบับร่าง</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">ข้อมูลหลักสูตร</flux:table.column>
                <flux:table.column>โครงสร้าง</flux:table.column>
                <flux:table.column>สถิติการเรียน</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>


            <flux:table.rows>
                @forelse ($courses as $course)
                    <flux:table.row wire:key="course-{{ $course->id }}" class="premium-table-row group">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-4 py-2">
                                @if ($course->thumbnail_url)
                                    <div class="size-14 shrink-0 rounded-2xl overflow-hidden border border-outline-variant/30 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                        <img src="{{ $course->thumbnail_url }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center text-primary border border-primary/10 group-hover:scale-105 transition-transform duration-300">
                                        <span class="material-symbols-outlined text-[28px]">book_5</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface text-base leading-snug group-hover:text-primary transition-colors">{{ $course->title }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <div class="flex items-center gap-1 text-[11px] font-bold text-on-surface/40">
                                            <div class="size-5 rounded-full bg-surface-container flex items-center justify-center text-[9px]">
                                                {{ substr($course->creator?->first_name ?? 'A', 0, 1) }}
                                            </div>
                                            {{ $course->creator?->fullName() ?? 'System' }}
                                        </div>
                                        <span class="text-on-surface/20 text-[10px]">•</span>
                                        <span class="text-[11px] font-medium text-on-surface/40">สร้างเมื่อ {{ $course->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-primary/40">view_module</span>
                                    <span class="text-sm font-bold text-on-surface">{{ $course->modules_count ?? 0 }}</span>
                                    <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Modules</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-primary/40">timer</span>
                                    <span class="text-sm font-bold text-on-surface">{{ $course->duration_hours ?? '-' }}</span>
                                    <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Hours</span>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-green-500/40">group</span>
                                    <span class="text-sm font-bold text-on-surface">{{ number_format($course->enrollments_count) }}</span>
                                    <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Learners</span>
                                </div>
                                @php
                                    $completionRate = $course->enrollments_count > 0 
                                        ? ($course->enrollments()->whereNotNull('completed_at')->count() / $course->enrollments_count) * 100 
                                        : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[16px] text-amber-500/40">verified</span>
                                    <span class="text-sm font-bold text-on-surface">{{ number_format($completionRate, 0) }}%</span>
                                    <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Completion</span>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($course->is_published)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 border border-green-100">
                                    <div class="size-1.5 rounded-full bg-green-500 animate-pulse"></div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-green-700">Published</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-zinc-100 border border-zinc-200">
                                    <div class="size-1.5 rounded-full bg-zinc-400"></div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Draft</span>
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <div class="flex justify-end gap-2">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil-square"
                                    class="rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                    wire:navigate
                                    :href="route('admin.courses.edit', $course)"
                                />
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full" />
                                    <flux:menu class="min-w-40">
                                        <flux:menu.item icon="pencil" wire:navigate :href="route('admin.courses.edit', $course)">แก้ไขข้อมูลหลักสูตร</flux:menu.item>
                                        <flux:menu.item icon="list-bullet" wire:navigate :href="route('admin.courses.modules', $course)">จัดการบทเรียน (Modules)</flux:menu.item>
                                        <flux:menu.item icon="academic-cap" wire:navigate :href="route('admin.courses.certificate-template', $course)">เทมเพลตเกียรติบัตร</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="togglePublish({{ $course->id }})"
                                            :icon="$course->is_published ? 'eye-slash' : 'eye'"
                                        >
                                            {{ $course->is_published ? 'ยกเลิกการเผยแพร่' : 'เผยแพร่สู่สาธารณะ' }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            icon="trash"
                                            variant="danger"
                                            wire:click="deleteCourse({{ $course->id }})"
                                            wire:confirm="ยืนยันการลบหลักสูตร '{{ $course->title }}'? ข้อมูลทั้งหมดจะถูกลบอย่างถาวร"
                                        >
                                            ลบหลักสูตรนี้
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">book</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบคอร์สที่ต้องการ</h3>
                                <p class="text-sm text-on-surface/40 mt-1">ลองเปลี่ยนคำค้นหาหรือตัวกรองใหม่อีกครั้ง</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if ($courses->hasPages())
        <div class="flex justify-center">
            {{ $courses->links() }}
        </div>
    @endif
</div>
