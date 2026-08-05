<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── LEFT COLUMN: Main form sections ── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Section 1: Basic Info --}}
        <div class="rounded-3xl border border-outline-variant/30 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-outline-variant/20">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-[24px]">auto_stories</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold font-headline text-on-surface tracking-tight">ข้อมูลหลักสูตร</h2>
                    <p class="text-sm text-on-surface/50">รายละเอียดเบื้องต้นสำหรับผู้เรียน</p>
                </div>
            </div>

            <div class="space-y-6">
                <flux:field>
                    <flux:label class="text-sm font-bold text-on-surface/70">ชื่อหลักสูตร <span class="text-error">*</span></flux:label>
                    <div x-data="{ count: {{ strlen($title) }} }" class="relative">
                        <flux:input
                            wire:model="title"
                            x-on:input="count = $event.target.value.length"
                            placeholder="เช่น การพัฒนาภาวะผู้นำยุคใหม่..."
                            maxlength="255"
                            class="bg-surface/30 focus:bg-white transition-colors"
                        />

                        <p class="text-xs text-right text-on-surface/40 mt-1" x-text="`${count}/255`"></p>
                    </div>
                    <flux:error name="title" />
                </flux:field>

                <flux:field>
                    <flux:label>คำอธิบายคอร์ส</flux:label>
                    @include('partials.rich-editor', [
                        'model'       => 'description',
                        'value'       => $description,
                        'placeholder' => 'อธิบายเนื้อหา เป้าหมาย และประโยชน์ที่ผู้เรียนจะได้รับ...',
                    ])
                    <flux:description>แสดงในหน้าแนะนำคอร์สสำหรับผู้เรียนและหน้าสาธารณะ</flux:description>
                    <flux:error name="description" />
                </flux:field>
            </div>
        </div>

        {{-- Section 2: Course Details --}}
        <div class="rounded-3xl border border-outline-variant/30 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-outline-variant/20">
                <div class="flex size-12 items-center justify-center rounded-2xl bg-amber-100/50 text-amber-600">
                    <span class="material-symbols-outlined text-[24px]">tune</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold font-headline text-on-surface tracking-tight">การตั้งค่าและเกณฑ์การวัดผล</h2>
                    <p class="text-sm text-on-surface/50">เงื่อนไขการผ่านหลักสูตรและระยะเวลา</p>
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

        {{-- Section 4: Image Gallery --}}
        <div class="rounded-2xl border border-outline-variant/30 bg-white p-6">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                    <span class="material-symbols-outlined text-[20px] text-primary">gallery_thumbnail</span>
                </div>
                <div>
                    <h2 class="text-base font-semibold font-headline text-on-surface">คลังรูปภาพ</h2>
                    <p class="text-xs text-on-surface/50">รูปภาพเพิ่มเติมที่แสดงในหน้าคอร์ส</p>
                </div>
            </div>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>เพิ่มรูปภาพในคลัง</flux:label>
                    <flux:input type="file" wire:model="gallery" multiple accept="image/*" />
                    <flux:description>สามารถเลือกได้หลายรูป (สูงสุด 2MB ต่อรูป)</flux:description>
                    <flux:error name="gallery.*" />
                </flux:field>

                @if ($existingGallery)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4">
                        @foreach ($existingGallery as $image)
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-outline-variant/20">
                                <img src="{{ $image['image_url'] }}" class="w-full h-full object-cover" alt="Gallery image">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <flux:button
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        wire:click="removeGalleryImage({{ $image['id'] }})"
                                        wire:confirm="ลบรูปภาพนี้?"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div wire:loading wire:target="gallery" class="text-xs text-primary animate-pulse">
                    กำลังอัปโหลดรูปภาพ...
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT COLUMN: Sidebar ── --}}
    <div class="space-y-4 lg:sticky lg:top-6 lg:self-start">

        {{-- Publishing card --}}
        <div class="rounded-3xl border border-outline-variant/30 bg-white p-6 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="size-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                </div>
                <h3 class="text-sm font-bold font-headline text-on-surface uppercase tracking-tight">สถานะการเผยแพร่</h3>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-2xl {{ $isPublished ? 'bg-green-50 border border-green-100' : 'bg-zinc-50 border border-zinc-100' }}">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full {{ $isPublished ? 'bg-green-500 animate-pulse' : 'bg-zinc-400' }}"></div>
                        <span class="text-sm font-bold {{ $isPublished ? 'text-green-700' : 'text-zinc-600' }}">
                            {{ $isPublished ? 'Published' : 'Draft Mode' }}
                        </span>
                    </div>
                    <flux:switch wire:model.live="isPublished" />
                </div>
                
                <p class="text-[11px] text-on-surface/40 leading-relaxed px-1">
                    {{ $isPublished
                        ? 'หลักสูตรนี้เปิดให้ผู้เรียนเข้าถึงและลงทะเบียนได้ทันที'
                        : 'หลักสูตรนี้จะยังไม่แสดงผลในหน้าแรกและหน้าค้นหา' }}
                </p>
            </div>
        </div>

        {{-- Group access card --}}
        <div class="rounded-3xl border border-outline-variant/30 bg-white p-6 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="size-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                </div>
                <h3 class="text-sm font-bold font-headline text-on-surface uppercase tracking-tight">การมองเห็นหลักสูตร</h3>
            </div>

            <p class="text-[11px] text-on-surface/40 leading-relaxed mb-3">
                ไม่เลือกกลุ่ม = ทุกคนเห็นและลงทะเบียนได้ &nbsp;·&nbsp; เลือกกลุ่ม = จำกัดเฉพาะสมาชิกกลุ่มที่เลือกเท่านั้น
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

        {{-- Thumbnail card --}}
        <div class="rounded-3xl border border-outline-variant/30 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-outline-variant/20">
                <div class="size-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[18px]">image</span>
                </div>
                <h3 class="text-sm font-bold font-headline text-on-surface uppercase tracking-tight">รูปหน้าปก</h3>
            </div>

            <div class="space-y-4">
                <div class="relative group aspect-video rounded-2xl overflow-hidden border-2 border-dashed border-outline-variant/50 bg-surface flex flex-col items-center justify-center transition-all hover:border-primary/50">
                    @if ($thumbnail)
                        <img src="{{ $thumbnail->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover">
                    @elseif ($thumbnailUrl)
                        <img src="{{ $thumbnailUrl }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-[32px] text-on-surface/20">add_photo_alternate</span>
                        <p class="text-[10px] font-bold text-on-surface/40 mt-2 uppercase">Upload Thumbnail</p>
                    @endif
                    
                    <input type="file" wire:model="thumbnail" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                </div>

                <div wire:loading wire:target="thumbnail" class="flex items-center gap-2 text-[10px] text-primary font-bold animate-pulse">
                    <span class="material-symbols-outlined text-[14px]">sync</span>
                    UPDATING PREVIEW...
                </div>
                
                <p class="text-[10px] text-on-surface/40 text-center">แนะนำขนาด 1280x720px (ไม่เกิน 2MB)</p>
            </div>
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
