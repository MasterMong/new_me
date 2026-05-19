<div class="p-6 space-y-6 max-w-5xl">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <flux:button variant="ghost" icon="arrow-left" wire:navigate :href="route('admin.courses.edit', $course)" />
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-on-surface/50 mb-0.5">
                    <span>จัดการคอร์ส</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="truncate">{{ $course->title }}</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span>โมดูล</span>
                </div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold font-headline text-on-surface">จัดการโมดูล</h1>
                    <flux:badge color="blue" size="sm">{{ $modules->count() }} โมดูล</flux:badge>
                </div>
            </div>
        </div>
        <flux:button variant="primary" wire:click="openCreateModule">
            <span class="material-symbols-outlined text-[18px]">add</span>
            เพิ่มโมดูล
        </flux:button>
    </div>

    {{-- Empty state --}}
    @if ($modules->isEmpty())
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-16 text-center">
            <div class="flex size-16 items-center justify-center rounded-2xl bg-primary/10 mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px] text-primary">view_week</span>
            </div>
            <p class="font-semibold text-on-surface">ยังไม่มีโมดูลในคอร์สนี้</p>
            <p class="text-sm text-on-surface/50 mt-1 mb-5">โมดูลคือบทเรียนหลักที่แบ่งเนื้อหาของคอร์สออกเป็นส่วนๆ</p>
            <flux:button variant="primary" wire:click="openCreateModule">
                <span class="material-symbols-outlined text-[18px]">add</span>
                เพิ่มโมดูลแรก
            </flux:button>
        </div>
    @endif

    {{-- Module cards --}}
    <div class="space-y-5">
        @foreach ($modules as $module)
            <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden"
                 wire:key="module-{{ $module->id }}">

                {{-- Module card header --}}
                <div class="flex items-start justify-between gap-4 p-5">
                    <div class="flex items-start gap-4 min-w-0">
                        {{-- Number badge --}}
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold text-sm font-headline">
                            {{ $module->module_number }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-on-surface text-base leading-tight">{{ $module->title }}</p>
                            @if ($module->description)
                                <p class="text-sm text-on-surface/50 mt-0.5 truncate max-w-lg">{{ $module->description }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if ($module->is_required)
                                    <flux:badge color="blue" size="sm">บังคับเรียน</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">ไม่บังคับ</flux:badge>
                                @endif
                                @if ($module->requires_expert_review)
                                    <flux:badge color="amber" size="sm">ต้องตรวจโดยผู้เชี่ยวชาญ</flux:badge>
                                @endif
                                <span class="text-xs text-on-surface/40">
                                    {{ $module->contents->count() }} เนื้อหา
                                    &middot;
                                    ทดสอบได้ {{ $module->max_test_attempts }} ครั้ง
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <flux:button size="sm" variant="ghost" wire:click="openCreateContent({{ $module->id }})">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            เพิ่มเนื้อหา
                        </flux:button>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEditModule({{ $module->id }})">
                                    แก้ไขโมดูล
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item
                                    icon="trash"
                                    variant="danger"
                                    wire:click="deleteModule({{ $module->id }})"
                                    wire:confirm="ยืนยันการลบโมดูล '{{ $module->title }}'? เนื้อหาทั้งหมดจะถูกลบด้วย"
                                >
                                    ลบโมดูล
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>
                </div>

                {{-- Content table --}}
                @if ($module->contents->isNotEmpty())
                    <div class="border-t border-outline-variant/20">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column class="ps-5 w-24">ประเภท</flux:table.column>
                                <flux:table.column>ชื่อเนื้อหา</flux:table.column>
                                <flux:table.column>ลิงก์ / ไฟล์</flux:table.column>
                                <flux:table.column class="w-24">ระยะเวลา</flux:table.column>
                                <flux:table.column class="w-28">การมองเห็น</flux:table.column>
                                <flux:table.column class="w-12"></flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach ($module->contents as $content)
                                    <flux:table.row wire:key="content-{{ $content->id }}">
                                        <flux:table.cell class="ps-5">
                                            @php
                                                [$typeColor, $typeLabel, $typeIcon] = match($content->content_type) {
                                                    \App\Enums\ContentType::Video    => ['red',   'วิดีโอ',  'play_circle'],
                                                    \App\Enums\ContentType::Document => ['blue',  'เอกสาร', 'description'],
                                                    \App\Enums\ContentType::Link     => ['green', 'ลิงก์',   'link'],
                                                };
                                            @endphp
                                            <flux:badge color="{{ $typeColor }}" size="sm">{{ $typeLabel }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="font-medium text-on-surface">
                                            {{ $content->title }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($content->file_url)
                                                <a href="{{ $content->file_url }}" target="_blank"
                                                   class="flex items-center gap-1 text-sm text-primary hover:underline max-w-xs truncate">
                                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                                    <span class="truncate">{{ $content->file_url }}</span>
                                                </a>
                                            @else
                                                <span class="text-on-surface/40 text-sm">-</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell class="text-on-surface/60 text-sm">
                                            {{ $content->duration_minutes ? number_format($content->duration_minutes, 0).' นาที' : '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($content->groupAccess->isEmpty())
                                                <flux:badge color="green" size="sm">ทุกกลุ่ม</flux:badge>
                                            @else
                                                <flux:tooltip>
                                                    <flux:badge color="amber" size="sm">
                                                        {{ $content->groupAccess->count() }} กลุ่ม
                                                    </flux:badge>
                                                    <flux:tooltip.content>
                                                        {{ $content->groupAccess->map(fn($a) => $a->group?->name)->filter()->join(', ') }}
                                                    </flux:tooltip.content>
                                                </flux:tooltip>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:dropdown>
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="pencil" wire:click="openEditContent({{ $content->id }})">
                                                        แก้ไข
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item
                                                        icon="trash"
                                                        variant="danger"
                                                        wire:click="deleteContent({{ $content->id }})"
                                                        wire:confirm="ยืนยันการลบเนื้อหา '{{ $content->title }}'?"
                                                    >
                                                        ลบ
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @else
                    <div class="border-t border-outline-variant/20 flex items-center justify-center gap-2 px-6 py-5 text-sm text-on-surface/40">
                        <span class="material-symbols-outlined text-[18px]">inbox</span>
                        ยังไม่มีเนื้อหา —
                        <button wire:click="openCreateContent({{ $module->id }})"
                                class="text-primary hover:underline font-medium">
                            เพิ่มเนื้อหาแรก
                        </button>
                    </div>
                @endif

            </div>
        @endforeach
    </div>


    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MODULE MODAL                                           --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <flux:modal wire:model="showModuleModal" class="w-full max-w-lg">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                <span class="material-symbols-outlined text-[20px] text-primary">view_week</span>
            </div>
            <flux:heading size="lg">
                {{ $editingModuleId ? 'แก้ไขโมดูล' : 'เพิ่มโมดูลใหม่' }}
            </flux:heading>
        </div>
        <flux:text class="mb-6 ps-12">
            {{ $editingModuleId ? 'แก้ไขข้อมูลโมดูล' : 'กรอกข้อมูลเพื่อสร้างโมดูลใหม่ในคอร์สนี้' }}
        </flux:text>

        <div class="space-y-4">
            <flux:field>
                <flux:label>ชื่อโมดูล <span class="text-error">*</span></flux:label>
                <flux:input wire:model="moduleTitle" placeholder="ระบุชื่อโมดูล..." />
                <flux:error name="moduleTitle" />
            </flux:field>

            <flux:field>
                <flux:label>คำอธิบาย</flux:label>
                <flux:textarea wire:model="moduleDescription" placeholder="อธิบายเนื้อหาของโมดูลนี้..." rows="3" />
                <flux:error name="moduleDescription" />
            </flux:field>

            <flux:field>
                <flux:label>จำนวนครั้งสูงสุดในการทดสอบ <span class="text-error">*</span></flux:label>
                <flux:input wire:model="moduleMaxTestAttempts" type="number" min="1" max="10" />
                <flux:description>ผู้เรียนสามารถทำแบบทดสอบซ้ำได้สูงสุดกี่ครั้ง</flux:description>
                <flux:error name="moduleMaxTestAttempts" />
            </flux:field>

            <div class="rounded-xl bg-surface p-4 space-y-3">
                <flux:field variant="inline">
                    <flux:switch wire:model="moduleIsRequired" />
                    <flux:label>บังคับเรียน</flux:label>
                    <flux:description>ผู้เรียนต้องผ่านโมดูลนี้เพื่อรับใบประกาศ</flux:description>
                </flux:field>

                <flux:field variant="inline">
                    <flux:switch wire:model="moduleRequiresExpertReview" />
                    <flux:label>ต้องให้ผู้เชี่ยวชาญตรวจ</flux:label>
                    <flux:description>คำตอบจะถูกส่งให้ผู้เชี่ยวชาญตรวจสอบ</flux:description>
                </flux:field>
            </div>
        </div>

        <div class="flex gap-3 mt-6 justify-end">
            <flux:button variant="ghost" wire:click="$set('showModuleModal', false)">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="saveModule"
                wire:loading.attr="disabled" wire:target="saveModule">
                <span wire:loading.remove wire:target="saveModule">
                    {{ $editingModuleId ? 'บันทึกการเปลี่ยนแปลง' : 'สร้างโมดูล' }}
                </span>
                <span wire:loading wire:target="saveModule">กำลังบันทึก...</span>
            </flux:button>
        </div>
    </flux:modal>


    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- CONTENT MODAL                                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <flux:modal wire:model="showContentModal" class="w-full max-w-lg">
        <div class="flex items-center gap-3 mb-1">
            <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                <span class="material-symbols-outlined text-[20px] text-primary">article</span>
            </div>
            <flux:heading size="lg">
                {{ $editingContentId ? 'แก้ไขเนื้อหา' : 'เพิ่มเนื้อหาใหม่' }}
            </flux:heading>
        </div>
        <flux:text class="mb-6 ps-12">
            {{ $editingContentId ? 'แก้ไขข้อมูลเนื้อหา' : 'กรอกข้อมูลเพื่อเพิ่มเนื้อหาในโมดูล' }}
        </flux:text>

        <div class="space-y-4">
            {{-- Content type --}}
            <flux:field>
                <flux:label>ประเภทเนื้อหา <span class="text-error">*</span></flux:label>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    @foreach ([['video', 'play_circle', 'วิดีโอ', 'red'], ['document', 'description', 'เอกสาร', 'blue'], ['link', 'link', 'ลิงก์', 'green']] as [$val, $icon, $label, $color])
                        <button
                            wire:click="$set('contentType', '{{ $val }}')"
                            type="button"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 p-3 text-sm font-medium transition-all
                                {{ $contentType === $val
                                    ? 'border-primary bg-primary/5 text-primary'
                                    : 'border-outline-variant/40 text-on-surface/60 hover:border-primary/40 hover:bg-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <flux:error name="contentType" />
            </flux:field>

            {{-- Title --}}
            <flux:field>
                <flux:label>ชื่อเนื้อหา <span class="text-error">*</span></flux:label>
                <flux:input wire:model="contentTitle" placeholder="ระบุชื่อเนื้อหา..." />
                <flux:error name="contentTitle" />
            </flux:field>

            {{-- URL --}}
            <flux:field>
                <flux:label>
                    {{ $contentType === 'video' ? 'URL วิดีโอ' : ($contentType === 'document' ? 'URL เอกสาร / ไฟล์' : 'URL ลิงก์') }}
                </flux:label>
                <flux:input wire:model="contentFileUrl" placeholder="https://..." />
                <flux:error name="contentFileUrl" />
            </flux:field>

            {{-- Duration (video only visually, still saveable for others) --}}
            <flux:field>
                <flux:label>ระยะเวลา (นาที)</flux:label>
                <flux:input wire:model="contentDurationMinutes" type="number" step="0.5" min="0" placeholder="เช่น 15" />
                <flux:error name="contentDurationMinutes" />
            </flux:field>

            {{-- Group visibility --}}
            <div class="rounded-xl border border-outline-variant/30 overflow-hidden">
                <div class="flex items-center gap-2 px-4 py-3 bg-surface border-b border-outline-variant/20">
                    <span class="material-symbols-outlined text-[18px] text-on-surface/60">visibility</span>
                    <p class="text-sm font-medium text-on-surface">การมองเห็น</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-on-surface/50 mb-3">
                        ไม่เลือกกลุ่ม = ทุกกลุ่มมองเห็น &nbsp;·&nbsp; เลือกกลุ่ม = จำกัดเฉพาะกลุ่มที่เลือกเท่านั้น
                    </p>
                    @forelse ($allGroups as $group)
                        <div class="flex items-center gap-3 py-2 {{ ! $loop->last ? 'border-b border-outline-variant/10' : '' }}">
                            <flux:checkbox
                                wire:model="selectedGroupIds"
                                :value="(string) $group->id"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-on-surface">{{ $group->name }}</p>
                                @if ($group->description)
                                    <p class="text-xs text-on-surface/50 truncate">{{ $group->description }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center gap-2 text-sm text-on-surface/40">
                            <span class="material-symbols-outlined text-[18px]">group_off</span>
                            ยังไม่มีกลุ่มผู้เรียน —
                            <a wire:navigate :href="route('admin.groups.index')" class="text-primary hover:underline">
                                สร้างกลุ่มก่อน
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-6 justify-end">
            <flux:button variant="ghost" wire:click="$set('showContentModal', false)">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="saveContent"
                wire:loading.attr="disabled" wire:target="saveContent">
                <span wire:loading.remove wire:target="saveContent">
                    {{ $editingContentId ? 'บันทึกการเปลี่ยนแปลง' : 'เพิ่มเนื้อหา' }}
                </span>
                <span wire:loading wire:target="saveContent">กำลังบันทึก...</span>
            </flux:button>
        </div>
    </flux:modal>

</div>
