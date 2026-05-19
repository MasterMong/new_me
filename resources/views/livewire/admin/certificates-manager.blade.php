<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface">จัดการเกียรติบัตร</h1>
            <p class="text-sm text-on-surface/60 mt-1">รายการเกียรติบัตรที่ออกให้กับผู้เรียนที่ผ่านเกณฑ์</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="ค้นหาเลขที่ใบประกาศ, ชื่อผู้เรียน หรือชื่อคอร์ส..."
                icon="magnifying-glass"
                clearable
            />
        </div>
    </div>

    {{-- Table --}}
    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">เลขที่ใบประกาศ</flux:table.column>
                <flux:table.column>ผู้เรียน</flux:table.column>
                <flux:table.column>คอร์สเรียน</flux:table.column>
                <flux:table.column>คะแนน (ร้อยละ)</flux:table.column>
                <flux:table.column>วันที่ออกให้</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($certificates as $cert)
                    <flux:table.row wire:key="cert-{{ $cert->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <span class="font-mono text-xs font-bold text-primary bg-primary/5 px-2 py-1 rounded border border-primary/10">
                                {{ $cert->certificate_number }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-3 py-1">
                                <div class="size-8 shrink-0 rounded-full bg-surface-container flex items-center justify-center text-on-surface/60 font-bold text-xs border border-outline-variant/30">
                                    {{ $cert->user->initials() }}
                                </div>
                                <span class="font-semibold text-on-surface">{{ $cert->full_name_on_cert }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="text-on-surface/70 font-medium">
                            {{ $cert->course->title }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge color="green" size="sm" class="font-bold">
                                {{ number_format($cert->final_score_pct, 1) }}%
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-on-surface/50 text-sm font-medium">
                            {{ $cert->issued_date->translatedFormat('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <div class="flex justify-end">
                                <flux:button
                                    as="a"
                                    href="{{ $cert->pdf_url }}"
                                    target="_blank"
                                    variant="ghost"
                                    size="sm"
                                    icon="arrow-down-tray"
                                    class="rounded-full hover:bg-surface-container"
                                    title="Download PDF"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon text-amber-400/20">workspace_premium</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบประวัติการออกเกียรติบัตร</h3>
                                <p class="text-sm text-on-surface/40 mt-1">ลองเปลี่ยนคำค้นหาใหม่อีกครั้ง</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if ($certificates->hasPages())
        <div class="flex justify-center">
            {{ $certificates->links() }}
        </div>
    @endif
</div>
