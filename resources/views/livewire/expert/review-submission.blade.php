<div class="space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-1">
            <a href="{{ route('expert.dashboard') }}" class="hover:text-primary transition-colors" wire:navigate>แดชบอร์ด</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            @if($attempt->assessment->module_id)
                <a href="{{ route('expert.submissions.index', $attempt->assessment->module_id) }}" class="hover:text-primary transition-colors" wire:navigate>รายการใบงาน</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            @endif
            <span>ตรวจใบงาน</span>
        </div>
        <flux:heading size="xl" level="1">ตรวจใบงานของ {{ $attempt->user->fullName() }}</flux:heading>
        <p class="text-zinc-500 text-sm mt-1">
            {{ $attempt->assessment->module ? 'โมดูล: '.$attempt->assessment->module->title : $attempt->assessment->title }}
        </p>
    </div>

    <flux:card>
        <div class="flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-primary">badge</span>
            <flux:heading size="sm">ข้อมูลผู้เรียน</flux:heading>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            <div>
                <dt class="text-zinc-500">ตำแหน่ง</dt>
                <dd class="font-medium text-zinc-900 dark:text-white">
                    {{ $attempt->user->position?->name ?? $attempt->user->position_other ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-zinc-500">หน่วยงาน</dt>
                <dd class="font-medium text-zinc-900 dark:text-white">
                    {{ $attempt->user->affiliation?->name ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-zinc-500">ประสบการณ์</dt>
                <dd class="font-medium text-zinc-900 dark:text-white">
                    {{ $attempt->user->experience?->label() ?? '-' }}
                </dd>
            </div>
        </dl>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if($attempt->attempt_number > 1 && $previousAttempts->isNotEmpty())
                <flux:card>
                    <flux:heading size="sm" class="mb-3">ประวัติการส่งครั้งก่อนหน้า</flux:heading>
                    <div class="space-y-3">
                        @foreach($previousAttempts as $previous)
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium">ครั้งที่ {{ $previous->attempt_number }}</span>
                                    @if($previous->status->value === 'pending_review')
                                        <flux:badge size="sm" color="warning">รอตรวจ</flux:badge>
                                    @elseif($previous->status->value === 'passed')
                                        <flux:badge size="sm" color="success">ผ่าน</flux:badge>
                                    @elseif($previous->status->value === 'revision_needed')
                                        <flux:badge size="sm" color="danger">รอแก้ไข</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ $previous->status->value }}</flux:badge>
                                    @endif
                                </div>
                                @if($previous->expertReview)
                                    <p class="text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ $previous->expertReview->feedback }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif

            <flux:card>
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">assignment</span>
                    <flux:heading size="lg">คำตอบของผู้เรียน</flux:heading>
                </div>

                <div class="space-y-8">
                    @foreach($attempt->answers as $index => $answer)
                        @if(in_array($answer->question->question_type->value, ['essay', 'file_upload']))
                            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-6 last:border-0 last:pb-0">
                                <div class="font-medium text-zinc-900 dark:text-white mb-2">
                                    {{ $index + 1 }}. {{ $answer->question->question_text }}
                                </div>

                                @if($answer->question->question_type->value === 'essay')
                                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                                        {{ $answer->essay_text ?? '(ไม่มีคำตอบ)' }}
                                    </div>
                                @elseif($answer->question->question_type->value === 'file_upload')
                                    @if($answer->uploaded_file_url)
                                        <div class="mt-2">
                                            <a href="{{ $answer->uploaded_file_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors text-sm">
                                                <span class="material-symbols-outlined">download</span>
                                                ดาวน์โหลดไฟล์แนบ
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-sm text-zinc-500 italic">(ไม่มีไฟล์แนบ)</div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </flux:card>
        </div>

        <div class="lg:col-span-1">
            <flux:card>
                <flux:heading size="lg" class="mb-4">ประเมินผล</flux:heading>
                
                <form wire:submit="submitReview" class="space-y-5">
                    <flux:radio.group wire:model="status" label="สถานะการประเมิน" required>
                        <flux:radio value="passed" label="ผ่าน (Passed)" />
                        <flux:radio value="revision_needed" label="รอแก้ไข (Revision Needed)" />
                    </flux:radio.group>

                    <flux:input wire:model="score" label="คะแนน (เต็ม {{ $attempt->max_score }})" type="number" step="0.5" min="0" max="{{ $attempt->max_score }}" />
                    
                    <flux:textarea wire:model="feedback" label="ข้อเสนอแนะ / คำแนะนำ" required rows="4" placeholder="กรอกข้อเสนอแนะเพื่อให้ผู้เรียนนำไปปรับปรุง..." />

                    <div class="pt-2">
                        <flux:button type="submit" variant="primary" class="w-full">
                            บันทึกผลการตรวจ
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        </div>
    </div>
</div>
