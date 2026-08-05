<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">ความเร็วในการตรวจของผู้เชี่ยวชาญ</h1>
            <p class="text-sm text-on-surface/60 mt-1">งานที่รอตรวจและเวลาเฉลี่ยในการตรวจของผู้เชี่ยวชาญแต่ละคน</p>
        </div>
    </div>

    @if ($openQueueCount > 0)
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-100">
            <span class="material-symbols-outlined text-amber-500">info</span>
            <p class="text-sm text-amber-700">
                มีงานรอตรวจ <strong>{{ $openQueueCount }}</strong> ชิ้น ในโมดูลที่ยังไม่ได้มอบหมายผู้เชี่ยวชาญเฉพาะเจาะจง (ผู้เชี่ยวชาญทุกคนตรวจได้)
            </p>
        </div>
    @endif

    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">ผู้เชี่ยวชาญ</flux:table.column>
                <flux:table.column>รอตรวจ</flux:table.column>
                <flux:table.column>ตรวจแล้ว</flux:table.column>
                <flux:table.column class="pe-6">เวลาเฉลี่ยในการตรวจ</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($experts as $row)
                    <flux:table.row wire:key="expert-{{ $row['expert']->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                    {{ $row['expert']->initials() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface leading-none">{{ $row['expert']->fullName() }}</p>
                                    <p class="text-[10px] text-on-surface/40 truncate">{{ $row['expert']->email }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row['pending_count'] > 0)
                                <flux:badge color="amber" size="sm">{{ $row['pending_count'] }} รายการ</flux:badge>
                            @else
                                <span class="text-xs text-on-surface/30">-</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm font-bold text-green-600">{{ $row['completed_count'] }}</span>
                            <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">รายการ</span>
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            @if ($row['avg_turnaround_hours'] === null)
                                <span class="text-xs text-on-surface/30">ยังไม่มีข้อมูล</span>
                            @elseif ($row['avg_turnaround_hours'] >= 24)
                                <span class="text-sm font-bold text-on-surface">{{ round($row['avg_turnaround_hours'] / 24, 1) }} วัน</span>
                            @else
                                <span class="text-sm font-bold text-on-surface">{{ $row['avg_turnaround_hours'] }} ชม.</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">fact_check</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ยังไม่มีผู้เชี่ยวชาญในระบบ</h3>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
