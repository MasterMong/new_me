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
    <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable>ชื่อคอร์ส</flux:table.column>
                <flux:table.column>ผู้สร้าง</flux:table.column>
                <flux:table.column>ผู้ลงทะเบียน</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($courses as $course)
                    <flux:table.row wire:key="course-{{ $course->id }}">
                        <flux:table.cell>
                            <div>
                                <p class="font-medium text-on-surface">{{ $course->title }}</p>
                                @if ($course->duration_hours)
                                    <p class="text-xs text-on-surface/50 mt-0.5">{{ $course->duration_hours }} ชั่วโมง</p>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/70">
                            {{ $course->creator?->fullName() ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="blue" size="sm">
                                {{ number_format($course->enrollments_count) }} คน
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($course->is_published)
                                <flux:badge color="green" size="sm">เผยแพร่</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">ฉบับร่าง</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:navigate :href="route('admin.courses.edit', $course)">แก้ไข</flux:menu.item>
                                    <flux:menu.item
                                        wire:click="togglePublish({{ $course->id }})"
                                        wire:confirm="{{ $course->is_published ? 'ยืนยันการยกเลิกเผยแพร่คอร์สนี้?' : 'ยืนยันการเผยแพร่คอร์สนี้?' }}"
                                        :icon="$course->is_published ? 'eye-slash' : 'eye'"
                                    >
                                        {{ $course->is_published ? 'ยกเลิกเผยแพร่' : 'เผยแพร่' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        icon="trash"
                                        variant="danger"
                                        wire:click="deleteCourse({{ $course->id }})"
                                        wire:confirm="ยืนยันการลบคอร์ส '{{ $course->title }}'? การกระทำนี้ไม่สามารถย้อนกลับได้"
                                    >
                                        ลบ
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-on-surface/50 py-12">
                            <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface/30">book</span>
                            ไม่พบคอร์สที่ตรงกับเงื่อนไข
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
