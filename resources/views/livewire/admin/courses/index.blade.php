<div class="p-6 space-y-6">
    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.text>{{ session('status') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface">จัดการคอร์ส</h1>
            <p class="text-sm text-on-surface/60 mt-1">คอร์สทั้งหมดในระบบ ME-Learning</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:navigate :href="route('admin.courses.create')">
            เพิ่มคอร์สใหม่
        </flux:button>
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
                <flux:table.column class="ps-6" sortable>ชื่อคอร์ส</flux:table.column>
                <flux:table.column>ผู้สร้าง</flux:table.column>
                <flux:table.column>ผู้ลงทะเบียน</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($courses as $course)
                    <flux:table.row wire:key="course-{{ $course->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-4 py-1">
                                @if ($course->thumbnail_url)
                                    <div class="size-12 shrink-0 rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm">
                                        <img src="{{ $course->thumbnail_url }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="size-12 shrink-0 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center text-primary border border-primary/10">
                                        <span class="material-symbols-outlined text-[24px]">book</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-on-surface leading-tight">{{ $course->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if ($course->duration_hours)
                                            <span class="inline-flex items-center text-[11px] font-medium text-on-surface/40">
                                                <flux:icon name="clock" variant="micro" class="me-1" />
                                                {{ $course->duration_hours }} ชั่วโมง
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center text-[11px] font-medium text-on-surface/40">
                                            <flux:icon name="list-bullet" variant="micro" class="me-1" />
                                            {{ $course->modules_count ?? 0 }} Modules
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2 text-on-surface/70">
                                <div class="size-6 rounded-full bg-surface-container flex items-center justify-center text-[10px] font-bold">
                                    {{ substr($course->creator?->first_name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium">{{ $course->creator?->fullName() ?? '-' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-bold text-on-surface">{{ number_format($course->enrollments_count) }}</span>
                                <span class="text-[10px] text-on-surface/40 uppercase tracking-tighter font-bold">Students</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($course->is_published)
                                <flux:badge color="green" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">
                                    Published
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">
                                    Draft
                                </flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <div class="flex justify-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full hover:bg-surface-container" />
                                    <flux:menu class="min-w-40">
                                        <flux:menu.item icon="pencil" wire:navigate :href="route('admin.courses.edit', $course)">แก้ไขข้อมูล</flux:menu.item>
                                        <flux:menu.item
                                            wire:click="togglePublish({{ $course->id }})"
                                            wire:confirm="{{ $course->is_published ? 'ยืนยันการยกเลิกเผยแพร่คอร์สนี้?' : 'ยืนยันการเผยแพร่คอร์สนี้?' }}"
                                            :icon="$course->is_published ? 'eye-slash' : 'eye'"
                                        >
                                            {{ $course->is_published ? 'ยกเลิกเผยแพร่' : 'เผยแพร่คอร์ส' }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            icon="trash"
                                            variant="danger"
                                            wire:click="deleteCourse({{ $course->id }})"
                                            wire:confirm="ยืนยันการลบคอร์ส '{{ $course->title }}'? การกระทำนี้ไม่สามารถย้อนกลับได้"
                                        >
                                            ลบข้อมูล
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
