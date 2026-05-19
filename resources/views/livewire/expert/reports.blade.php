<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">รายงานผลการเรียน</flux:heading>
            <p class="text-zinc-500 text-sm mt-1">รายชื่อผู้เรียนที่ส่งใบงานในโมดูลที่คุณรับผิดชอบ</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        <div class="w-full sm:w-1/3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="ค้นหาชื่อ, อีเมลผู้เรียน..." icon="magnifying-glass" />
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-zinc-500 uppercase bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">ชื่อ-นามสกุล</th>
                        <th scope="col" class="px-6 py-4 font-medium text-center">ใบงานรอตรวจ</th>
                        <th scope="col" class="px-6 py-4 font-medium text-right">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($learners as $learner)
                        <tr class="bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $learner->fullName() }}</div>
                                <div class="text-xs text-zinc-500">{{ $learner->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($learner->pending_reviews_count > 0)
                                    <flux:badge color="warning">{{ $learner->pending_reviews_count }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button size="sm" variant="outline" href="{{ route('expert.reports.show', $learner->id) }}" wire:navigate>
                                    ดูผลรายบุคคล
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-zinc-500">
                                ไม่พบข้อมูลผู้เรียนที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($learners->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $learners->links() }}
            </div>
        @endif
    </div>
</div>
