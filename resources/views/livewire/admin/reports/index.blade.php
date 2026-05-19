<div class="p-6 space-y-8">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold font-headline text-on-surface">รายงานภาพรวม</h1>
        <p class="text-sm text-on-surface/60 mt-1">สถิติการเรียนและผลลัพธ์ในระบบ ME-Learning</p>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-primary text-on-primary p-5 shadow">
            <p class="text-3xl font-bold font-headline">{{ number_format($totalUsers) }}</p>
            <p class="text-sm text-on-primary/70 mt-1">ผู้ใช้ทั้งหมด</p>
        </div>
        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 shadow-sm">
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($totalEnrollments) }}</p>
            <p class="text-sm text-on-surface/60 mt-1">การลงทะเบียน</p>
        </div>
        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 shadow-sm">
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($totalCertificates) }}</p>
            <p class="text-sm text-on-surface/60 mt-1">ใบประกาศนียบัตร</p>
        </div>
        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 shadow-sm">
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($pendingReviews) }}</p>
            <p class="text-sm text-on-surface/60 mt-1">รอตรวจโดยผู้เชี่ยวชาญ</p>
        </div>
    </div>

    {{-- Course Completion Rates --}}
    <div>
        <h2 class="text-lg font-bold font-headline text-on-surface mb-4">อัตราการสำเร็จรายคอร์ส</h2>

        <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>คอร์ส</flux:table.column>
                    <flux:table.column>ผู้ลงทะเบียน</flux:table.column>
                    <flux:table.column>สำเร็จแล้ว</flux:table.column>
                    <flux:table.column>ใบประกาศ</flux:table.column>
                    <flux:table.column>อัตราสำเร็จ</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($courseStats as $stat)
                        <flux:table.row wire:key="stat-{{ $loop->index }}">
                            <flux:table.cell class="font-medium text-on-surface">{{ $stat['title'] }}</flux:table.cell>
                            <flux:table.cell class="text-on-surface/70">{{ number_format($stat['enrollments']) }}</flux:table.cell>
                            <flux:table.cell class="text-on-surface/70">{{ number_format($stat['completed']) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="amber" size="sm">
                                    {{ number_format($stat['certificates']) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-surface rounded-full h-2 w-24">
                                        <div
                                            class="bg-primary h-2 rounded-full transition-all"
                                            style="width: {{ $stat['rate'] }}%"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium text-on-surface w-10 text-right">
                                        {{ $stat['rate'] }}%
                                    </span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-on-surface/50 py-12">
                                ยังไม่มีข้อมูล
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
