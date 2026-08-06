<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">แดชบอร์ดผู้เชี่ยวชาญ (Expert Dashboard)</flux:heading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-100 text-amber-600 rounded-lg">
                    <span class="material-symbols-outlined text-3xl">pending_actions</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">ใบงานรอตรวจทั้งหมด</div>
                    <div class="text-3xl font-semibold text-zinc-900 dark:text-white">{{ $totalPending }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-rose-100 text-rose-600 rounded-lg">
                    <span class="material-symbols-outlined text-3xl">warning</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">รอตรวจเกิน 3 วัน</div>
                    <div class="text-3xl font-semibold text-zinc-900 dark:text-white">{{ $totalOverdue }}</div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-lg">
                    <span class="material-symbols-outlined text-3xl">fact_check</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">จำนวนใบงานที่ตรวจแล้ว (ของคุณ)</div>
                    <div class="text-3xl font-semibold text-zinc-900 dark:text-white">{{ $totalCompleted }}</div>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:heading size="lg" level="2" class="mt-8 mb-4">โมดูลที่รับผิดชอบ</flux:heading>

    <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-zinc-500 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">โมดูล</th>
                        <th scope="col" class="px-6 py-4 font-medium">หลักสูตร</th>
                        <th scope="col" class="px-6 py-4 font-medium text-center">จำนวนใบงานรอตรวจ</th>
                        <th scope="col" class="px-6 py-4 font-medium text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($modules as $module)
                        <tr class="bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                {{ $module->title }}
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $module->course->title }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($module->pending_reviews_count > 0)
                                    <flux:badge color="warning" class="font-bold">{{ $module->pending_reviews_count }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">0</span>
                                @endif
                                @if($module->overdue_reviews_count > 0)
                                    <div class="text-xs text-rose-600 mt-1">{{ $module->overdue_reviews_count }} รายการเกิน 3 วัน</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button size="sm" variant="primary" href="{{ route('expert.submissions.index', $module->id) }}" wire:navigate>
                                    ตรวจสอบ
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
                                ไม่มีโมดูลที่ต้องการการตรวจจากผู้เชี่ยวชาญในขณะนี้
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
