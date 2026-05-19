<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── LEFT COLUMN: Main form sections ── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Section 1: Basic Info --}}
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                    <span class="material-symbols-outlined text-[20px] text-primary">auto_stories</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold font-headline text-on-surface">ข้อมูลหลัก</h2>
                    <p class="text-xs text-on-surface/50">ชื่อและคำอธิบายที่ผู้เรียนจะเห็น</p>
                </div>
            </div>

            <div class="space-y-5">
                <flux:field>
                    <flux:label>ชื่อคอร์ส <span class="text-error">*</span></flux:label>
                    <div x-data="{ count: {{ strlen($title) }} }">
                        <flux:input
                            wire:model="title"
                            x-on:input="count = $event.target.value.length"
                            placeholder="ระบุชื่อคอร์ส..."
                            maxlength="255"
                        />
                        <p class="text-xs text-right text-on-surface/40 mt-1" x-text="`${count}/255`"></p>
                    </div>
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>คำอธิบายคอร์ส</flux:label>
                    <flux:textarea
                        wire:model="description"
                        placeholder="อธิบายเนื้อหา เป้าหมาย และประโยชน์ที่ผู้เรียนจะได้รับ..."
                        rows="5"
                    />
                    <flux:description>แสดงในหน้าแนะนำคอร์สสำหรับผู้เรียนและหน้าสาธารณะ</flux:description>
                    <flux:error name="description" />
                </flux:field>
            </div>
        </div>

        {{-- Section 2: Course Details --}}
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                    <span class="material-symbols-outlined text-[20px] text-primary">tune</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold font-headline text-on-surface">รายละเอียดคอร์ส</h2>
                    <p class="text-xs text-on-surface/50">เงื่อนไขและข้อกำหนดของหลักสูตร</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <flux:field>
                    <flux:label>ระยะเวลา (ชั่วโมง)</flux:label>
                    <flux:input
                        wire:model="durationHours"
                        type="number"
                        step="0.5"
                        min="0.5"
                        placeholder="เช่น 3.5"
                    />
                    <flux:description>ระยะเวลาโดยประมาณที่ผู้เรียนต้องใช้</flux:description>
                    <flux:error name="durationHours" />
                </flux:field>

                <flux:field>
                    <flux:label>เกณฑ์ผ่าน (%) <span class="text-error">*</span></flux:label>
                    <flux:input
                        wire:model="passingScorePct"
                        type="number"
                        min="0"
                        max="100"
                        placeholder="70"
                    />
                    <flux:description>คะแนนขั้นต่ำเพื่อสำเร็จหลักสูตร</flux:description>
                    <flux:error name="passingScorePct" />
                </flux:field>
            </div>
        </div>

        {{-- Section 3: Assessment & Testing --}}
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                    <span class="material-symbols-outlined text-[20px] text-primary">quiz</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold font-headline text-on-surface">การทดสอบและการประเมิน</h2>
                    <p class="text-xs text-on-surface/50">ตั้งค่าการประเมินผลผู้เรียน</p>
                </div>
            </div>

            <div class="space-y-4">
                <flux:field variant="inline">
                    <flux:switch wire:model.live="hasTest" />
                    <flux:label>มีแบบทดสอบ</flux:label>
                    <flux:description>คอร์สนี้มีการประเมินผลด้วยแบบทดสอบปรนัยหรืออัตนัย</flux:description>
                </flux:field>

                @if ($hasTest)
                    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 mt-2">
                        <div class="flex items-start gap-2 mb-3">
                            <span class="material-symbols-outlined text-[18px] text-amber-600 mt-0.5" style="font-variation-settings: 'FILL' 1;">info</span>
                            <p class="text-xs text-amber-700">เปิดใช้งานเมื่อคอร์สมีข้อสอบอัตนัยหรืองานที่ต้องตรวจโดยผู้เชี่ยวชาญ</p>
                        </div>
                        <flux:field variant="inline">
                            <flux:switch wire:model="requireReview" />
                            <flux:label>ต้องให้ผู้เชี่ยวชาญตรวจ</flux:label>
                            <flux:description>คำตอบแบบเขียน/ไฟล์จะส่งให้ผู้เชี่ยวชาญตรวจภายใน 3 วันทำการ</flux:description>
                        </flux:field>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN: Sidebar ── --}}
    <div class="space-y-4 lg:sticky lg:top-6 lg:self-start">

        {{-- Publishing card --}}
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-5">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/20">
                <span class="material-symbols-outlined text-[18px] text-primary">rocket_launch</span>
                <h3 class="text-sm font-semibold font-headline text-on-surface">การเผยแพร่</h3>
            </div>

            {{-- Status indicator --}}
            <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-xl bg-surface">
                <div class="size-2 rounded-full transition-colors {{ $isPublished ? 'bg-green-500' : 'bg-zinc-400' }}"></div>
                <span class="text-sm font-medium text-on-surface">
                    {{ $isPublished ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                </span>
            </div>

            <flux:field variant="inline">
                <flux:switch wire:model.live="isPublished" />
                <flux:label>เผยแพร่คอร์ส</flux:label>
            </flux:field>
            <p class="text-xs text-on-surface/50 mt-2">
                {{ $isPublished ? 'ผู้เรียนสามารถมองเห็นและลงทะเบียนได้' : 'คอร์สนี้ยังไม่แสดงต่อผู้เรียน' }}
            </p>
        </div>

        {{-- Course summary (edit only) --}}
        @if (isset($course))
            <div class="rounded-2xl border border-outline-variant/30 bg-white p-5">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-outline-variant/20">
                    <span class="material-symbols-outlined text-[18px] text-primary">info</span>
                    <h3 class="text-sm font-semibold font-headline text-on-surface">สรุปคอร์ส</h3>
                </div>

                <dl class="space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-on-surface/50">ผู้ลงทะเบียน</dt>
                        <dd class="text-sm font-semibold text-on-surface">{{ number_format($enrollmentCount ?? 0) }} คน</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-on-surface/50">จำนวนโมดูล</dt>
                        <dd class="text-sm font-semibold text-on-surface">{{ number_format($moduleCount ?? 0) }} โมดูล</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-on-surface/50">วันที่สร้าง</dt>
                        <dd class="text-sm text-on-surface">{{ $course->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-on-surface/50">ผู้สร้าง</dt>
                        <dd class="text-sm text-on-surface truncate max-w-[120px] text-right">{{ $course->creator?->fullName() ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 pt-3 border-t border-outline-variant/20">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        class="w-full"
                        wire:navigate
                        :href="route('admin.courses.modules', $course)"
                    >
                        <span class="material-symbols-outlined text-[16px]">view_list</span>
                        จัดการโมดูล ({{ $moduleCount ?? 0 }})
                    </flux:button>
                </div>
            </div>
        @endif

    </div>

</div>
