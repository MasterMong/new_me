<div class="space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-1">
            <a href="{{ route('expert.dashboard') }}" class="hover:text-primary transition-colors" wire:navigate>แดชบอร์ด</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('expert.reports.index') }}" class="hover:text-primary transition-colors" wire:navigate>รายงานผลการเรียน</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>ผลการเรียนรายบุคคล</span>
        </div>
        <flux:heading size="xl" level="1">ผลการเรียนของ {{ $user->fullName() }}</flux:heading>
        <p class="text-zinc-500 text-sm mt-1">{{ $user->email }}</p>
    </div>

    <div class="space-y-6">
        <flux:heading size="lg">ประวัติการส่งใบงาน (เฉพาะโมดูลที่รับผิดชอบ)</flux:heading>

        <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-zinc-500 uppercase bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium">วันที่ส่ง</th>
                            <th scope="col" class="px-6 py-4 font-medium">โมดูล</th>
                            <th scope="col" class="px-6 py-4 font-medium text-center">คะแนน</th>
                            <th scope="col" class="px-6 py-4 font-medium">สถานะ</th>
                            <th scope="col" class="px-6 py-4 font-medium text-right">รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($attempts as $attempt)
                            <tr class="bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4 text-zinc-500">
                                    {{ $attempt->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                                    @if($attempt->attempt_number > 1)
                                        <span class="ml-1 text-xs text-zinc-400">(ครั้งที่ {{ $attempt->attempt_number }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ $attempt->assessment->module->title }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($attempt->total_score !== null)
                                        {{ $attempt->total_score }} / {{ $attempt->max_score }}
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($attempt->status->value === 'pending_review')
                                        <flux:badge color="warning">รอตรวจ</flux:badge>
                                    @elseif($attempt->status->value === 'passed')
                                        <flux:badge color="success">ผ่าน</flux:badge>
                                    @elseif($attempt->status->value === 'revision_needed')
                                        <flux:badge color="danger">รอแก้ไข</flux:badge>
                                    @else
                                        <flux:badge color="zinc">{{ $attempt->status->value }}</flux:badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <flux:button size="sm" variant="{{ $attempt->status->value === 'pending_review' ? 'primary' : 'outline' }}" href="{{ route('expert.submissions.review', $attempt->id) }}" wire:navigate>
                                        {{ $attempt->status->value === 'pending_review' ? 'ตรวจใบงาน' : 'ดูผลการตรวจ' }}
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-zinc-500">
                                    ผู้เรียนยังไม่มีประวัติการส่งใบงานในโมดูลที่คุณรับผิดชอบ
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
