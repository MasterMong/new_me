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
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" icon="document-text" wire:navigate :href="route('admin.courses.assessments', $course)">
                คลังแบบทดสอบ
            </flux:button>
            <flux:button variant="primary" wire:click="openCreateModule">
                <span class="material-symbols-outlined text-[18px]">add</span>
                เพิ่มโมดูล
            </flux:button>
        </div>
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
                        {{-- Thumbnail or Number badge --}}
                        @if ($module->thumbnail_url)
                            <div class="size-16 shrink-0 rounded-xl overflow-hidden border border-outline-variant/20">
                                <img src="{{ $module->thumbnail_url }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold text-sm font-headline">
                                {{ $module->module_number }}
                            </div>
                        @endif
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
                                @if ($module->is_sequential)
                                    <flux:badge color="purple" size="sm">เรียนตามลำดับ</flux:badge>
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
                        <div class="flex items-center border border-outline-variant/30 rounded-xl bg-surface p-1 me-2">
                            <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveModule({{ $module->id }}, 'up')" :disabled="$loop->first" class="rounded-lg h-7 w-7" />
                            <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveModule({{ $module->id }}, 'down')" :disabled="$loop->last" class="rounded-lg h-7 w-7" />
                        </div>

                        <flux:button size="sm" variant="ghost" wire:click="openCreateContent({{ $module->id }})" class="hover:bg-primary/5 hover:text-primary">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            เพิ่มเนื้อหา
                        </flux:button>
                        
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEditModule({{ $module->id }})">แก้ไขรายละเอียด</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item
                                    icon="trash"
                                    variant="danger"
                                    wire:click="deleteModule({{ $module->id }})"
                                    wire:confirm="ยืนยันการลบโมดูล '{{ $module->title }}'? เนื้อหาทั้งหมดในโมดูลจะถูกลบ"
                                >
                                    ลบโมดูล
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                </div>

                {{-- Content table --}}
                @if ($module->contents->isNotEmpty())
                    <div class="border-t border-outline-variant/20 bg-surface-container-lowest/50">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column class="ps-6 w-24">ประเภท</flux:table.column>
                                <flux:table.column>ชื่อเนื้อหา</flux:table.column>
                                <flux:table.column>แหล่งข้อมูล</flux:table.column>
                                <flux:table.column class="w-24">ระยะเวลา</flux:table.column>
                                <flux:table.column class="w-32">กลุ่มเป้าหมาย</flux:table.column>
                                <flux:table.column class="pe-6 w-12"></flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($module->contents as $content)
                                    <flux:table.row wire:key="content-{{ $content->id }}" class="premium-table-row">
                                        <flux:table.cell class="ps-6">
                                            @php
                                                [$typeColor, $typeLabel, $typeIcon] = match($content->content_type) {
                                                    \App\Enums\ContentType::Video    => ['red',   'วิดีโอ',  'play_circle'],
                                                    \App\Enums\ContentType::Document => ['blue',  'เอกสาร', 'description'],
                                                    \App\Enums\ContentType::Link     => ['green', 'ลิงก์',   'link'],
                                                    \App\Enums\ContentType::Test     => ['purple', 'ทดสอบ', 'quiz'],
                                                };
                                            @endphp
                                            <div class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[16px] text-{{ $typeColor }}-500">{{ $typeIcon }}</span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-{{ $typeColor }}-600">{{ $typeLabel }}</span>
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="font-semibold text-on-surface">
                                            {{ $content->title }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            @if ($content->file_url)
                                                <a href="{{ $content->file_url }}" target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-surface border border-outline-variant/10 text-[11px] text-primary hover:bg-primary hover:text-on-primary transition-all max-w-[160px]">
                                                    <flux:icon name="link" variant="micro" />
                                                    <span class="truncate">{{ str_replace(['https://', 'http://'], '', $content->file_url) }}</span>
                                                </a>
                                            @else
                                                <span class="text-on-surface/30 italic text-xs">No link</span>
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell class="text-on-surface/60 text-[11px] font-bold">
                                            @if ($content->duration_minutes)
                                                <div class="flex items-center gap-1">
                                                    <flux:icon name="clock" variant="micro" class="opacity-50" />
                                                    {{ number_format($content->duration_minutes, 0) }}m
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            @if ($content->groupAccess->isEmpty())
                                                <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-100">Public</span>
                                            @else
                                                <flux:tooltip>
                                                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100 cursor-help">
                                                        {{ $content->groupAccess->count() }} Groups
                                                    </span>
                                                    <flux:tooltip.content>
                                                        {{ $content->groupAccess->map(fn($a) => $a->group?->name)->filter()->join(', ') }}
                                                    </flux:tooltip.content>
                                                </flux:tooltip>
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell class="pe-6">
                                            <div class="flex items-center justify-end gap-1">
                                                <div class="flex items-center bg-surface/50 rounded-lg p-0.5 me-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveContent({{ $content->id }}, 'up')" :disabled="$loop->first" class="h-6 w-6" />
                                                    <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveContent({{ $content->id }}, 'down')" :disabled="$loop->last" class="h-6 w-6" />
                                                </div>
                                                <flux:dropdown>
                                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full h-8 w-8" />
                                                    <flux:menu class="min-w-32">
                                                        <flux:menu.item icon="pencil" wire:click="openEditContent({{ $content->id }})">แก้ไข</flux:menu.item>
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
                                            </div>
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
                @include('partials.rich-editor', [
                    'model'       => 'moduleDescription',
                    'value'       => $moduleDescription,
                    'placeholder' => 'อธิบายเนื้อหาของโมดูลนี้...',
                ])
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

                <flux:field variant="inline">
                    <flux:switch wire:model="moduleIsSequential" />
                    <flux:label>เรียนตามลำดับ (Sequential)</flux:label>
                    <flux:description>ผู้เรียนต้องจบเนื้อหาก่อนหน้าก่อนเข้าสู่เนื้อหาถัดไป</flux:description>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>รูปหน้าปกโมดูล (Thumbnail)</flux:label>
                <div class="flex items-center gap-4 mt-1">
                    @if ($moduleThumbnail)
                        <div class="size-20 shrink-0 rounded-xl overflow-hidden border border-outline-variant/20 bg-surface">
                            <img src="{{ $moduleThumbnail->temporaryUrl() }}" class="w-full h-full object-cover">
                        </div>
                    @elseif ($moduleThumbnailUrl)
                        <div class="size-20 shrink-0 rounded-xl overflow-hidden border border-outline-variant/20 bg-surface">
                            <img src="{{ $moduleThumbnailUrl }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <div class="flex-1">
                        <flux:input type="file" wire:model="moduleThumbnail" accept="image/*" />
                        <flux:description class="mt-1">ขนาดสูงสุด 2MB</flux:description>
                    </div>
                </div>
                <flux:error name="moduleThumbnail" />
            </flux:field>
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
                <div class="grid grid-cols-4 gap-2 mt-1">
                    @foreach ([
                        ['video', 'play_circle', 'วิดีโอ'],
                        ['document', 'description', 'เอกสาร'],
                        ['link', 'link', 'ลิงก์'],
                        ['test', 'quiz', 'แบบทดสอบ']
                    ] as [$val, $icon, $label])
                        <button
                            wire:click="$set('contentType', '{{ $val }}')"
                            type="button"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 p-3 text-xs font-medium transition-all
                                {{ $contentType === $val
                                    ? 'border-primary bg-primary/5 text-primary'
                                    : 'border-outline-variant/40 text-on-surface/60 hover:border-primary/40 hover:bg-surface' }}"
                        >
                            <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
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

            {{-- URL / Test Selection --}}
            @if ($contentType === 'test')
                <flux:field>
                    <flux:label>เลือกแบบทดสอบ <span class="text-error">*</span></flux:label>
                    <flux:select wire:model="contentAssessmentId" placeholder="กรุณาเลือกแบบทดสอบ...">
                        <flux:select.option value="">เลือกแบบทดสอบ...</flux:select.option>
                        @foreach ($courseAssessments as $assessment)
                            <flux:select.option :value="$assessment->id">
                                {{ $assessment->title }} ({{ $assessment->type->name }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="contentAssessmentId" />
                </flux:field>
            @else
                <flux:field>
                    <flux:label>
                        {{ $contentType === 'video' ? 'URL วิดีโอ' : ($contentType === 'document' ? 'URL เอกสาร / ไฟล์' : 'URL ลิงก์') }}
                    </flux:label>
                    <flux:input wire:model="contentFileUrl" placeholder="https://..." />
                    <flux:error name="contentFileUrl" />
                </flux:field>
            @endif

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
