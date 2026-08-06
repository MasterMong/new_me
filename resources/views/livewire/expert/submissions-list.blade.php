<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500 mb-1">
                <a href="{{ route('expert.dashboard') }}" class="hover:text-primary transition-colors" wire:navigate>แดชบอร์ด</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span>รายการใบงาน</span>
            </div>
            <flux:heading size="xl" level="1">โมดูล: {{ $module->title }}</flux:heading>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        <div class="w-full sm:w-1/3">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="ค้นหาชื่อ, อีเมล..." icon="magnifying-glass" />
        </div>
        <div class="w-full sm:w-1/4">
            <flux:select wire:model.live="statusFilter" placeholder="ทุกสถานะ">
                <flux:select.option value="">ทุกสถานะ</flux:select.option>
                <flux:select.option value="pending_review">รอตรวจ</flux:select.option>
                <flux:select.option value="passed">ผ่าน</flux:select.option>
                <flux:select.option value="revision_needed">รอแก้ไข</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-zinc-500 uppercase bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-medium">ผู้เรียน</th>
                        <th scope="col" class="px-6 py-4 font-medium">วันที่ส่ง</th>
                        <th scope="col" class="px-6 py-4 font-medium">สถานะ</th>
                        <th scope="col" class="px-6 py-4 font-medium text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($submissions as $submission)
                        <tr class="bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $submission->user->fullName() }}</div>
                                <div class="text-xs text-zinc-500">{{ $submission->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $submission->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                                @if($submission->attempt_number > 1)
                                    <span class="ml-1 text-xs text-zinc-400">(ครั้งที่ {{ $submission->attempt_number }})</span>
                                @endif
                                @if($submission->status->value === 'pending_review' && $submission->submitted_at)
                                    @php($waitingDays = (int) $submission->submitted_at->diffInDays(now()))
                                    <div class="mt-1">
                                        <flux:badge size="sm" color="{{ $waitingDays > 3 ? 'danger' : 'zinc' }}">
                                            รอมาแล้ว {{ $waitingDays }} วัน
                                        </flux:badge>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($submission->status->value === 'pending_review')
                                    <flux:badge color="warning">รอตรวจ</flux:badge>
                                @elseif($submission->status->value === 'passed')
                                    <flux:badge color="success">ผ่าน</flux:badge>
                                @elseif($submission->status->value === 'revision_needed')
                                    <flux:badge color="danger">รอแก้ไข</flux:badge>
                                @else
                                    <flux:badge color="zinc">{{ $submission->status->value }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button size="sm" variant="{{ $submission->status->value === 'pending_review' ? 'primary' : 'outline' }}" href="{{ route('expert.submissions.review', $submission->id) }}" wire:navigate>
                                    {{ $submission->status->value === 'pending_review' ? 'ตรวจใบงาน' : 'ดูผลการตรวจ' }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
                                ไม่พบรายการใบงานที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
