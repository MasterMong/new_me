<div class="space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500 mb-1">
            <a href="{{ route('expert.dashboard') }}" class="hover:text-primary transition-colors" wire:navigate>แดชบอร์ด</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('expert.submissions.index', $attempt->assessment->module_id) }}" class="hover:text-primary transition-colors" wire:navigate>รายการใบงาน</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span>ตรวจใบงาน</span>
        </div>
        <flux:heading size="xl" level="1">ตรวจใบงานของ {{ $attempt->user->fullName() }}</flux:heading>
        <p class="text-zinc-500 text-sm mt-1">โมดูล: {{ $attempt->assessment->module->title }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">assignment</span>
                    <flux:heading size="lg">คำตอบของผู้เรียน</flux:heading>
                </div>
                
                <div class="space-y-8">
                    @foreach($attempt->answers as $index => $answer)
                        @if(in_array($answer->question->type->value, ['essay', 'file_upload']))
                            <div class="border-b border-zinc-100 dark:border-zinc-800 pb-6 last:border-0 last:pb-0">
                                <div class="font-medium text-zinc-900 dark:text-white mb-2">
                                    {{ $index + 1 }}. {{ $answer->question->content }}
                                </div>
                                
                                @if($answer->question->type->value === 'essay')
                                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                                        {{ $answer->answer_text ?? '(ไม่มีคำตอบ)' }}
                                    </div>
                                @elseif($answer->question->type->value === 'file_upload')
                                    @if($answer->file_path)
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($answer->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors text-sm">
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
