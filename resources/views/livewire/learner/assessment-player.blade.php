<div class="min-h-[calc(100vh-64px)] bg-zinc-50 dark:bg-zinc-950 p-6">
    <div class="max-w-4xl mx-auto">
        @if(!$isFinished)
            @if($feedback = $this->activeRevisionFeedback())
                <div class="mb-6 p-6 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 flex gap-4">
                    <flux:icon.exclamation-triangle variant="micro" class="text-amber-600 shrink-0 mt-1" />
                    <div>
                        <p class="font-bold text-amber-700 dark:text-amber-400 mb-1">ผู้เชี่ยวชาญให้แก้ไขงานนี้</p>
                        <p class="text-amber-700/80 dark:text-amber-400/80 text-sm">{{ $feedback }}</p>
                    </div>
                </div>
            @endif

            {{-- Assessment Header --}}
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <div>
                    <flux:heading size="xl" class="text-zinc-900 dark:text-white mb-2">{{ $assessment->title }}</flux:heading>
                    <div class="flex items-center gap-4 text-sm text-zinc-500">
                        <span class="flex items-center gap-1">
                            <flux:icon.document-text variant="micro" />
                            {{ $totalQuestions }} ข้อ
                        </span>
                        <span class="flex items-center gap-1">
                            <flux:icon.clock variant="micro" />
                            ไม่จำกัดเวลา
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden md:block">
                        <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest">ความคืบหน้า</p>
                        <p class="text-lg font-bold text-primary">{{ $currentIndex + 1 }} / {{ $totalQuestions }}</p>
                    </div>
                    <div class="size-16 rounded-full border-4 border-primary/10 flex items-center justify-center relative">
                        <svg class="size-full -rotate-90 absolute inset-0">
                            <circle cx="32" cy="32" r="28" fill="none" stroke="currentColor" stroke-width="4" class="text-primary/10" />
                            <circle cx="32" cy="32" r="28" fill="none" stroke="currentColor" stroke-width="4" class="text-primary transition-all duration-500" 
                                    stroke-dasharray="175.92" stroke-dashoffset="{{ 175.92 - (175.92 * (($currentIndex + 1) / $totalQuestions)) }}" />
                        </svg>
                        <span class="text-sm font-bold text-primary">{{ round((($currentIndex + 1) / $totalQuestions) * 100) }}%</span>
                    </div>
                </div>
            </div>

            {{-- Question Card --}}
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden mb-8">
                {{-- Question Body --}}
                <div class="p-8 md:p-12">
                    <flux:heading size="lg" class="mb-8 leading-relaxed">
                        {{ $currentIndex + 1 }}. {{ $questions[$currentIndex]->question_text }}
                    </flux:heading>

                    @if($questions[$currentIndex]->question_type === \App\Enums\QuestionType::MultipleChoice)
                        <div class="space-y-4">
                            @foreach($questions[$currentIndex]->choices as $choice)
                                <button
                                    wire:click="selectChoice({{ $choice->id }})"
                                    @class([
                                        'w-full p-6 rounded-2xl border-2 text-left transition-all flex items-center gap-4 group',
                                        'border-primary bg-primary/5 ring-1 ring-primary/20' => ($answers[$questions[$currentIndex]->id] ?? null) == $choice->id,
                                        'border-zinc-100 dark:border-zinc-800 hover:border-primary/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => ($answers[$questions[$currentIndex]->id] ?? null) != $choice->id,
                                    ])
                                >
                                    <div @class([
                                        'size-6 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors',
                                        'border-primary bg-primary text-white' => ($answers[$questions[$currentIndex]->id] ?? null) == $choice->id,
                                        'border-zinc-300 dark:border-zinc-700 group-hover:border-primary/50' => ($answers[$questions[$currentIndex]->id] ?? null) != $choice->id,
                                    ])>
                                        @if(($answers[$questions[$currentIndex]->id] ?? null) == $choice->id)
                                            <div class="size-2 bg-white rounded-full"></div>
                                        @endif
                                    </div>
                                    <span @class([
                                        'text-lg font-medium',
                                        'text-primary' => ($answers[$questions[$currentIndex]->id] ?? null) == $choice->id,
                                        'text-zinc-700 dark:text-zinc-300' => ($answers[$questions[$currentIndex]->id] ?? null) != $choice->id,
                                    ])>
                                        {{ $choice->choice_text }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @elseif($questions[$currentIndex]->question_type === \App\Enums\QuestionType::ShortAnswer)
                        <flux:input
                            wire:model="essayAnswers.{{ $questions[$currentIndex]->id }}"
                            placeholder="พิมพ์คำตอบของคุณ..."
                        />
                    @elseif($questions[$currentIndex]->question_type === \App\Enums\QuestionType::Essay)
                        <flux:textarea
                            wire:model="essayAnswers.{{ $questions[$currentIndex]->id }}"
                            placeholder="พิมพ์คำตอบของคุณ..."
                            rows="8"
                        />
                    @elseif($questions[$currentIndex]->question_type === \App\Enums\QuestionType::FileUpload)
                        <div class="space-y-4">
                            @if($existingFileUrls[$questions[$currentIndex]->id] ?? null)
                                <div class="flex items-center gap-3 p-4 rounded-2xl bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900 text-sm">
                                    <flux:icon.document-check variant="micro" class="text-green-600" />
                                    <span class="text-green-700 dark:text-green-400">มีไฟล์ที่บันทึกไว้แล้ว —</span>
                                    <a href="{{ $existingFileUrls[$questions[$currentIndex]->id] }}" target="_blank" class="font-medium text-primary underline">ดูไฟล์</a>
                                </div>
                            @endif
                            <flux:input
                                type="file"
                                wire:model="uploadedFiles.{{ $questions[$currentIndex]->id }}"
                            />
                            @if($uploadedFiles[$questions[$currentIndex]->id] ?? null)
                                <p class="text-sm text-zinc-500">ไฟล์ใหม่: {{ $uploadedFiles[$questions[$currentIndex]->id]->getClientOriginalName() }} — กด "บันทึกร่าง" หรือ "ส่งคำตอบ" เพื่ออัปโหลด</p>
                            @endif
                            @error('uploadedFiles.'.$questions[$currentIndex]->id)
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- Navigation Footer --}}
                <div class="bg-zinc-50 dark:bg-zinc-800/50 p-6 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button variant="ghost" icon="arrow-left" wire:click="prevQuestion" :disabled="$currentIndex === 0">
                        ข้อก่อนหน้า
                    </flux:button>

                    <div class="flex items-center gap-3">
                        @if($questions[$currentIndex]->question_type !== \App\Enums\QuestionType::MultipleChoice)
                            <flux:button variant="ghost" icon="bookmark" wire:click="saveDraft">
                                บันทึกร่าง
                            </flux:button>
                        @endif
                        @if($currentIndex < $totalQuestions - 1)
                            <flux:button variant="primary" icon-trailing="arrow-right" wire:click="nextQuestion">
                                ข้อถัดไป
                            </flux:button>
                        @else
                            <flux:button variant="primary" class="bg-green-600 hover:bg-green-500 border-0 px-8" wire:click="finish">
                                ส่งคำตอบ
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Question Grid (Mobile/Quick Jump) --}}
            <div class="flex flex-wrap justify-center gap-2">
                @foreach($questions as $index => $q)
                    @php
                        $isAnswered = match ($q->question_type) {
                            \App\Enums\QuestionType::MultipleChoice => isset($answers[$q->id]),
                            \App\Enums\QuestionType::Essay, \App\Enums\QuestionType::ShortAnswer => filled($essayAnswers[$q->id] ?? null),
                            \App\Enums\QuestionType::FileUpload => filled($existingFileUrls[$q->id] ?? null) || filled($uploadedFiles[$q->id] ?? null),
                        };
                    @endphp
                    <button
                        wire:click="goToQuestion({{ $index }})"
                        @class([
                            'size-10 rounded-xl border flex items-center justify-center text-xs font-bold transition-all',
                            'bg-primary border-primary text-white shadow-lg shadow-primary/20 scale-110' => $currentIndex === $index,
                            'bg-green-100 border-green-200 text-green-700' => $isAnswered && $currentIndex !== $index,
                            'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-500 hover:border-primary' => !$isAnswered && $currentIndex !== $index,
                        ])
                    >
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        @else
            {{-- Results Summary --}}
            <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] border border-zinc-200 dark:border-zinc-800 shadow-xl overflow-hidden animate-in fade-in zoom-in duration-700">
                <div class="p-12 text-center">
                    @if($currentAttempt->status === \App\Enums\TestAttemptStatus::PendingReview)
                        <div class="size-24 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-8">
                            <flux:icon.clock variant="micro" class="size-16" />
                        </div>
                        <flux:heading size="xl" class="text-amber-600 mb-2">ส่งใบงานเรียบร้อยแล้ว</flux:heading>
                        <flux:subheading class="mb-10 text-lg">รอผู้เชี่ยวชาญตรวจ คุณจะได้รับแจ้งเตือนเมื่อผลการตรวจออก</flux:subheading>

                        <flux:button variant="primary" class="w-full md:w-auto px-10 h-12 text-lg" href="{{ route('learn.courses.show', $assessment->course_id) }}" wire:navigate>
                            กลับไปยังบทเรียน
                        </flux:button>
                    @elseif($currentAttempt->status === \App\Enums\TestAttemptStatus::RevisionNeeded)
                        <div class="size-24 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-8">
                            <flux:icon.exclamation-triangle variant="micro" class="size-16" />
                        </div>
                        <flux:heading size="xl" class="text-amber-600 mb-2">ผู้เชี่ยวชาญให้แก้ไขงานนี้</flux:heading>
                        @if($currentAttempt->expertReview?->feedback)
                            <flux:subheading class="mb-10 text-lg max-w-xl mx-auto">{{ $currentAttempt->expertReview->feedback }}</flux:subheading>
                        @endif

                        <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                            @if($this->canRetry())
                                <flux:button variant="primary" class="w-full md:w-auto px-10 h-12 text-lg" wire:click="retryAttempt">
                                    แก้ไขและส่งใหม่
                                </flux:button>
                            @else
                                <p class="text-sm text-zinc-500">ใช้สิทธิ์แก้ไขครบแล้ว กรุณาติดต่อผู้เชี่ยวชาญหรือผู้ดูแลระบบ</p>
                            @endif
                            <flux:button variant="ghost" class="w-full md:w-auto px-10 h-12 text-lg" href="{{ route('learn.courses.show', $assessment->course_id) }}" wire:navigate>
                                กลับไปยังบทเรียน
                            </flux:button>
                        </div>
                    @else
                        @if($currentAttempt->status === \App\Enums\TestAttemptStatus::Passed)
                            <div class="size-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                                <flux:icon.check-circle variant="micro" class="size-16" />
                            </div>
                            <flux:heading size="xl" class="text-green-600 mb-2">ยินดีด้วย! คุณสอบผ่าน</flux:heading>
                        @else
                            <div class="size-24 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-8">
                                <flux:icon.x-circle variant="micro" class="size-16" />
                            </div>
                            <flux:heading size="xl" class="text-red-600 mb-2">พยายามใหม่อีกครั้ง</flux:heading>
                        @endif

                        <flux:subheading class="mb-10 text-lg">คะแนนที่ได้ในการทดสอบครั้งนี้</flux:subheading>

                        <div class="grid grid-cols-2 gap-8 mb-12">
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-8 rounded-3xl border border-zinc-100 dark:border-zinc-800">
                                <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-2">คะแนนรวม</p>
                                <p class="text-5xl font-black text-zinc-900 dark:text-white">{{ round($score) }}%</p>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-8 rounded-3xl border border-zinc-100 dark:border-zinc-800">
                                <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-2">สถานะ</p>
                                <p @class([
                                    'text-2xl font-bold',
                                    'text-green-600' => $currentAttempt->status === \App\Enums\TestAttemptStatus::Passed,
                                    'text-red-600' => $currentAttempt->status !== \App\Enums\TestAttemptStatus::Passed,
                                ])>
                                    {{ $currentAttempt->status === \App\Enums\TestAttemptStatus::Passed ? 'ผ่านเกณฑ์' : 'ไม่ผ่านเกณฑ์' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                            <flux:button variant="primary" class="w-full md:w-auto px-10 h-12 text-lg" href="{{ route('learn.courses.show', $assessment->course_id) }}" wire:navigate>
                                กลับไปยังบทเรียน
                            </flux:button>
                            @if($currentAttempt->status !== \App\Enums\TestAttemptStatus::Passed)
                                @if($this->canRetry())
                                    <flux:button variant="ghost" class="w-full md:w-auto px-10 h-12 text-lg" wire:click="retryAttempt">
                                        ทำแบบทดสอบใหม่
                                    </flux:button>
                                @else
                                    <p class="text-sm text-zinc-500 w-full md:w-auto">ใช้สิทธิ์ทำแบบทดสอบครบแล้ว</p>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
