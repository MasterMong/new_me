<div class="p-6 space-y-6 max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" wire:navigate :href="route('admin.courses.modules', $course)" />
            <div>
                <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">คลังแบบทดสอบ</h1>
                <p class="text-sm text-on-surface/60">{{ $course->title }}</p>
            </div>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateAssessment">
            สร้างแบบทดสอบใหม่
        </flux:button>
    </div>

    {{-- Assessment List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($assessments as $assessment)
            <div wire:key="assessment-{{ $assessment->id }}" class="bg-white rounded-3xl border border-outline-variant/30 overflow-hidden shadow-sm hover:shadow-md transition-all group">
                <div class="p-6 space-y-4">
                    @php
                        [$typeColor, $typeLabel, $typeIcon] = match ($assessment->type) {
                            \App\Enums\AssessmentType::PreTest => ['blue', 'ก่อนเรียน (Pre-test)', 'quiz'],
                            \App\Enums\AssessmentType::PostTest => ['purple', 'หลังเรียน (Post-test)', 'workspace_premium'],
                            \App\Enums\AssessmentType::ModuleTest => ['teal', 'ท้ายโมดูล', 'fact_check'],
                            \App\Enums\AssessmentType::Assignment => ['amber', 'ใบงาน (Assignment)', 'assignment'],
                        };
                    @endphp
                    <div class="flex items-start justify-between">
                        <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[28px]">{{ $typeIcon }}</span>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <flux:badge size="sm" :color="$typeColor">{{ $typeLabel }}</flux:badge>
                            <flux:badge size="sm" color="zinc">
                                {{ $assessment->module?->title ?? 'ทั้งคอร์ส' }}
                            </flux:badge>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-on-surface text-lg leading-tight group-hover:text-primary transition-colors">
                            {{ $assessment->title }}
                        </h3>
                        <p class="text-xs text-on-surface/40 mt-1 font-bold uppercase tracking-wider">
                            {{ $assessment->questions_count }} Questions &middot; Pass {{ $assessment->passing_score_pct }}%
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 py-2">
                        <div class="bg-surface p-2 rounded-xl border border-outline-variant/10">
                            <p class="text-[9px] font-bold text-on-surface/40 uppercase">Attempts</p>
                            <p class="text-sm font-bold text-on-surface">{{ $assessment->max_attempts }} times</p>
                        </div>
                        <div class="bg-surface p-2 rounded-xl border border-outline-variant/10">
                            <p class="text-[9px] font-bold text-on-surface/40 uppercase">Grading</p>
                            <p class="text-sm font-bold text-on-surface uppercase">{{ $assessment->grading_mode->value }}</p>
                        </div>
                    </div>

                    @if ($assessment->is_timed)
                        <div class="flex items-center gap-1.5 text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100 w-fit">
                            <flux:icon name="clock" variant="micro" />
                            จับเวลา {{ $assessment->time_limit_minutes }} นาที
                        </div>
                    @endif

                    <div class="flex items-center gap-2 pt-2">
                        <flux:button variant="ghost" size="sm" class="flex-1" wire:click="editAssessment({{ $assessment->id }})">
                            <flux:icon name="pencil" variant="micro" class="me-1" />
                            ตั้งค่า
                        </flux:button>
                        <flux:button variant="primary" size="sm" class="flex-1" wire:click="manageQuestions({{ $assessment->id }})">
                            <flux:icon name="list-bullet" variant="micro" class="me-1" />
                            จัดการโจทย์
                        </flux:button>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-lg" />
                            <flux:menu>
                                <flux:menu.item icon="trash" variant="danger" wire:click="deleteAssessment({{ $assessment->id }})" wire:confirm="ยืนยันการลบแบบทดสอบนี้?">
                                    ลบแบบทดสอบ
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-outline-variant/20 border-dashed">
                <span class="material-symbols-outlined text-[48px] text-on-surface/20">format_list_bulleted_add</span>
                <p class="mt-2 text-on-surface/40 font-medium">ยังไม่มีแบบทดสอบในคลัง</p>
                <flux:button variant="ghost" size="sm" class="mt-4" wire:click="openCreateAssessment">เริ่มสร้างแบบทดสอบแรก</flux:button>
            </div>
        @endforelse
    </div>

    {{-- Assessment Modal --}}
    <flux:modal wire:model="showAssessmentModal" class="w-full max-w-lg">
        <div class="flex items-center gap-3 mb-6">
            <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[20px]">edit_note</span>
            </div>
            <div>
                <flux:heading size="lg">{{ $editingAssessmentId ? 'แก้ไขแบบทดสอบ' : 'สร้างแบบทดสอบใหม่' }}</flux:heading>
                <flux:text size="sm">ระบุข้อมูลพื้นฐานและเกณฑ์การให้คะแนน</flux:text>
            </div>
        </div>

        <div class="space-y-4">
            <flux:field>
                <flux:label>ชื่อแบบทดสอบ <span class="text-error">*</span></flux:label>
                <flux:input wire:model="assessmentTitle" placeholder="เช่น แบบทดสอบก่อนเรียน บทที่ 1..." />
                <flux:error name="assessmentTitle" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>ประเภท</flux:label>
                    <flux:select wire:model="assessmentType">
                        <flux:select.option value="pre_test">ก่อนเรียน (Pre-test)</flux:select.option>
                        <flux:select.option value="post_test">หลังเรียน (Post-test)</flux:select.option>
                        <flux:select.option value="module_test">แบบทดสอบท้ายโมดูล</flux:select.option>
                        <flux:select.option value="assignment">ใบงาน (Assignment)</flux:select.option>
                    </flux:select>
                    <flux:error name="assessmentType" />
                </flux:field>

                <flux:field>
                    <flux:label>เกณฑ์การผ่าน (%)</flux:label>
                    <flux:input type="number" wire:model="assessmentPassingScore" min="0" max="100" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>ขอบเขต</flux:label>
                <flux:select wire:model="assessmentModuleId" placeholder="ทั้งคอร์ส (Course-wide)">
                    <flux:select.option value="">ทั้งคอร์ส (Course-wide)</flux:select.option>
                    @foreach ($courseModules as $module)
                        <flux:select.option :value="$module->id">
                            โมดูล {{ $module->module_number }}: {{ $module->title }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:description>เลือกโมดูลเพื่อให้แบบทดสอบนี้ผูกกับโมดูลนั้นโดยเฉพาะ</flux:description>
                <flux:error name="assessmentModuleId" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>จำนวนครั้งที่ทำได้</flux:label>
                    <flux:input type="number" wire:model="assessmentMaxAttempts" min="1" />
                </flux:field>

                <flux:field>
                    <flux:label>การตรวจคะแนน</flux:label>
                    <flux:select wire:model="assessmentGradingMode">
                        <flux:select.option value="auto">อัตโนมัติ (Auto)</flux:select.option>
                        <flux:select.option value="manual">ตรวจด้วยมือ (Manual)</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="rounded-xl bg-surface p-4 space-y-3">
                <flux:field variant="inline">
                    <flux:switch wire:model.live="assessmentIsTimed" />
                    <flux:label>จับเวลาทำข้อสอบ</flux:label>
                    <flux:description>ระบบจะส่งคำตอบอัตโนมัติเมื่อหมดเวลา</flux:description>
                </flux:field>

                @if ($assessmentIsTimed)
                    <div class="mt-3">
                        <flux:field>
                            <flux:label>เวลาที่กำหนด (นาที) <span class="text-error">*</span></flux:label>
                            <flux:input type="number" wire:model="assessmentTimeLimitMinutes" min="1" max="600" placeholder="เช่น 60" />
                            <flux:error name="assessmentTimeLimitMinutes" />
                        </flux:field>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex gap-3 mt-8 justify-end">
            <flux:button variant="ghost" wire:click="$set('showAssessmentModal', false)">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="saveAssessment">บันทึกข้อมูล</flux:button>
        </div>
    </flux:modal>

    {{-- Question List Modal --}}
    <flux:modal wire:model="showQuestionListModal" class="w-full max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">list_alt</span>
                </div>
                <div>
                    <flux:heading size="lg">จัดการโจทย์และคำตอบ</flux:heading>
                    <flux:text size="sm">มีโจทย์ทั้งหมด {{ count($questions) }} ข้อ ในแบบทดสอบนี้</flux:text>
                </div>
            </div>
            <flux:button variant="primary" size="sm" icon="plus" wire:click="openCreateQuestion">เพิ่มข้อคำถาม</flux:button>
        </div>

        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
            @forelse ($questions as $index => $question)
                <div wire:key="q-{{ $question->id }}" class="bg-surface p-5 rounded-2xl border border-outline-variant/30 group">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="size-8 shrink-0 rounded-lg bg-white border border-outline-variant/20 flex items-center justify-center font-bold text-sm text-on-surface/40">
                                {{ $index + 1 }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-on-surface leading-snug">{{ $question->question_text }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <flux:badge size="sm" color="zinc" class="text-[9px] uppercase tracking-tighter">
                                        {{ str_replace('_', ' ', $question->question_type->value) }}
                                    </flux:badge>
                                    <span class="text-[10px] font-bold text-on-surface/30 uppercase tracking-widest">{{ $question->points }} Points</span>
                                </div>
                                
                                @if ($question->question_type->value === 'multiple_choice')
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach ($question->choices as $choice)
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-[11px] {{ $choice->is_correct ? 'bg-green-50 border border-green-100 text-green-700' : 'bg-white border border-outline-variant/10 text-on-surface/60' }}">
                                                <span class="material-symbols-outlined text-[14px]">
                                                    {{ $choice->is_correct ? 'check_circle' : 'radio_button_unchecked' }}
                                                </span>
                                                <span class="truncate">{{ $choice->choice_text }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif ($question->question_type->value === 'short_answer')
                                    <div class="mt-3 flex items-center gap-2 px-3 py-1.5 rounded-xl text-[11px] bg-green-50 border border-green-100 text-green-700 w-fit">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        <span>{{ $question->correct_answer }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <div class="flex items-center border border-outline-variant/10 rounded-lg bg-white p-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveQuestion({{ $question->id }}, 'up')" :disabled="$loop->first" class="h-6 w-6" />
                                <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveQuestion({{ $question->id }}, 'down')" :disabled="$loop->last" class="h-6 w-6" />
                            </div>
                            <flux:button variant="ghost" size="sm" icon="pencil" class="rounded-lg h-8 w-8" wire:click="editQuestion({{ $question->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" class="rounded-lg h-8 w-8 text-error/60 hover:text-error" wire:click="deleteQuestion({{ $question->id }})" wire:confirm="ลบข้อคำถามนี้?" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-surface rounded-2xl p-12 text-center border-2 border-dashed border-outline-variant/30">
                    <span class="material-symbols-outlined text-[48px] text-on-surface/20">post_add</span>
                    <p class="mt-4 text-on-surface/40 font-bold">ยังไม่มีโจทย์ในแบบทดสอบนี้</p>
                    <flux:button variant="ghost" size="sm" class="mt-4" wire:click="openCreateQuestion">สร้างโจทย์ข้อแรก</flux:button>
                </div>
            @endforelse
        </div>

        <div class="flex mt-8 justify-end">
            <flux:button variant="ghost" wire:click="$set('showQuestionListModal', false)">ปิดหน้าต่าง</flux:button>
        </div>
    </flux:modal>

    {{-- Question Form Modal --}}
    <flux:modal wire:model="showQuestionFormModal" class="w-full max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[20px]">add_task</span>
            </div>
            <div>
                <flux:heading size="lg">{{ $editingQuestionId ? 'แก้ไขโจทย์' : 'เพิ่มโจทย์ใหม่' }}</flux:heading>
                <flux:text size="sm">ระบุคำถามและตั้งค่าคำตอบที่ถูกต้อง</flux:text>
            </div>
        </div>

        <div class="space-y-5">
            <flux:field>
                <flux:label>คำถาม <span class="text-error">*</span></flux:label>
                <flux:textarea wire:model="questionText" placeholder="ระบุโจทย์คำถาม..." rows="3" />
                <flux:error name="questionText" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>ประเภทคำถาม</flux:label>
                    <flux:select wire:model.live="questionType">
                        <flux:select.option value="multiple_choice">ปรนัย (Multiple Choice)</flux:select.option>
                        <flux:select.option value="essay">อัตนัย (Essay/Text)</flux:select.option>
                        <flux:select.option value="short_answer">ตอบสั้น (Short Answer)</flux:select.option>
                        <flux:select.option value="file_upload">อัปโหลดไฟล์ (File Upload)</flux:select.option>
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>คะแนน (Points)</flux:label>
                    <flux:input type="number" wire:model="questionPoints" min="1" />
                </flux:field>
            </div>

            {{-- Choices Section (Multiple Choice Only) --}}
            @if ($questionType === 'multiple_choice')
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <flux:label>ตัวเลือกคำตอบ <span class="text-error">*</span></flux:label>
                        <flux:button size="xs" variant="ghost" icon="plus" wire:click="addChoice">เพิ่มตัวเลือก</flux:button>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach ($choices as $index => $choice)
                            <div class="flex items-center gap-3 bg-surface p-3 rounded-2xl border border-outline-variant/20">
                                <flux:checkbox wire:model="choices.{{ $index }}.is_correct" class="shrink-0" />
                                <flux:input
                                    wire:model="choices.{{ $index }}.text"
                                    placeholder="ตัวเลือกที่ {{ $index + 1 }}"
                                    class="flex-1 !bg-transparent border-0 focus:ring-0 px-0"
                                />
                                <flux:button 
                                    size="xs" 
                                    variant="ghost" 
                                    icon="trash" 
                                    class="text-error/40 hover:text-error shrink-0" 
                                    wire:click="removeChoice({{ $index }})" 
                                    :disabled="count($choices) <= 1"
                                />
                            </div>
                        @endforeach
                    </div>
                    <flux:error name="choices.*.text" />
                </div>
            @elseif ($questionType === 'short_answer')
                <flux:field>
                    <flux:label>คำตอบที่ถูกต้อง <span class="text-error">*</span></flux:label>
                    <flux:input wire:model="questionCorrectAnswer" placeholder="ระบุคำตอบที่ถูกต้อง..." />
                    <flux:description>ระบบจะตรวจให้คะแนนอัตโนมัติโดยเทียบกับคำตอบนี้ (ไม่สนใจตัวพิมพ์เล็ก/ใหญ่และช่องว่างส่วนเกิน)</flux:description>
                    <flux:error name="questionCorrectAnswer" />
                </flux:field>
            @else
                <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl flex gap-3">
                    <span class="material-symbols-outlined text-amber-500">info</span>
                    <p class="text-xs text-amber-700 leading-relaxed">
                        โจทย์ประเภท <strong>{{ str_replace('_', ' ', $questionType) }}</strong> จะต้องได้รับการตรวจให้คะแนนโดยผู้เชี่ยวชาญ (Manual Grading) เท่านั้น
                    </p>
                </div>
            @endif
        </div>

        <div class="flex gap-3 mt-8 justify-end">
            <flux:button variant="ghost" wire:click="$set('showQuestionFormModal', false)">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="saveQuestion">บันทึกโจทย์</flux:button>
        </div>
    </flux:modal>
</div>
