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
                    <span>เทมเพลตเกียรติบัตร</span>
                </div>
                <h1 class="text-xl font-bold font-headline text-on-surface">เทมเพลตเกียรติบัตร</h1>
            </div>
        </div>
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save,templateImage">
            <span wire:loading.remove wire:target="save">บันทึกเทมเพลต</span>
            <span wire:loading wire:target="save">กำลังบันทึก...</span>
        </flux:button>
    </div>

    @if (session('status'))
        <div class="flex items-center gap-2 p-4 rounded-2xl bg-green-50 border border-green-100 text-green-700 text-sm">
            <flux:icon.check-circle variant="micro" />
            {{ session('status') }}
        </div>
    @endif

    {{-- Upload area (shown until a template image exists, or to replace it) --}}
    <div class="rounded-3xl border border-outline-variant/30 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="size-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-[18px]">image</span>
            </div>
            <h3 class="text-sm font-bold font-headline text-on-surface uppercase tracking-tight">ภาพเทมเพลต</h3>
        </div>

        <div class="relative">
            <flux:button variant="ghost" size="sm" class="relative overflow-hidden">
                <span class="material-symbols-outlined text-[16px] mr-1">upload</span>
                {{ $templateImageUrl ? 'เปลี่ยนภาพเทมเพลต' : 'อัปโหลดภาพเทมเพลต' }}
                <input type="file" wire:model="templateImage" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
            </flux:button>
        </div>
        <flux:error name="templateImage" />
        <div wire:loading wire:target="templateImage" class="flex items-center gap-2 text-xs text-primary font-bold animate-pulse mt-2">
            <span class="material-symbols-outlined text-[14px]">sync</span>
            กำลังอัปโหลด...
        </div>
        <p class="text-[11px] text-on-surface/40 mt-2">รองรับ JPG/PNG ขนาดไม่เกิน 5MB — แนะนำภาพแนวนอนความละเอียดสูงสำหรับพิมพ์เกียรติบัตร</p>
    </div>

    {{-- Interactive coordinate picker --}}
    @if ($templateImage || $templateImageUrl)
        <div
            x-data="{
                mode: 'name',
                naturalWidth: 0,
                naturalHeight: 0,
                nameX: @entangle('nameX'),
                nameY: @entangle('nameY'),
                dateX: @entangle('dateX'),
                dateY: @entangle('dateY'),
                onImageLoad(event) {
                    this.naturalWidth = event.target.naturalWidth;
                    this.naturalHeight = event.target.naturalHeight;
                },
                setPosition(event) {
                    if (! this.naturalWidth) return;
                    const rect = event.currentTarget.getBoundingClientRect();
                    const xPercent = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
                    const yPercent = Math.min(1, Math.max(0, (event.clientY - rect.top) / rect.height));
                    const x = Math.round(xPercent * this.naturalWidth);
                    const y = Math.round(yPercent * this.naturalHeight);
                    if (this.mode === 'name') { this.nameX = x; this.nameY = y; }
                    else { this.dateX = x; this.dateY = y; }
                },
            }"
            class="rounded-3xl border border-outline-variant/30 bg-white p-6 shadow-sm"
        >
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="text-sm font-bold font-headline text-on-surface uppercase tracking-tight">กำหนดตำแหน่ง</h3>
                    <p class="text-xs text-on-surface/50 mt-1">คลิกบนภาพเพื่อกำหนดตำแหน่งที่เลือกไว้ด้านล่าง</p>
                </div>
                <div class="flex items-center gap-2 bg-surface p-1 rounded-xl">
                    <button
                        type="button"
                        @click="mode = 'name'"
                        :class="mode === 'name' ? 'bg-primary text-on-primary shadow' : 'text-on-surface/60'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                    >
                        <span class="size-2 rounded-full bg-blue-500"></span>
                        ตำแหน่งชื่อ
                    </button>
                    <button
                        type="button"
                        @click="mode = 'date'"
                        :class="mode === 'date' ? 'bg-primary text-on-primary shadow' : 'text-on-surface/60'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                    >
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        ตำแหน่งวันที่
                    </button>
                </div>
            </div>

            <div
                class="relative inline-block w-full rounded-2xl overflow-hidden border border-outline-variant/20 select-none"
                style="cursor: crosshair;"
                @click="setPosition($event)"
            >
                <img
                    src="{{ $templateImage ? $templateImage->temporaryUrl() : $templateImageUrl }}"
                    @load="onImageLoad($event)"
                    class="w-full h-auto block pointer-events-none"
                    draggable="false"
                >

                {{-- Name marker + live preview text --}}
                <div
                    class="absolute -translate-x-1/2 -translate-y-1/2 pointer-events-none"
                    :style="`left: ${naturalWidth ? (nameX / naturalWidth * 100) : 0}%; top: ${naturalHeight ? (nameY / naturalHeight * 100) : 0}%;`"
                >
                    <span class="whitespace-nowrap text-lg font-bold text-blue-600 bg-white/80 px-2 py-0.5 rounded shadow border border-blue-200">
                        นายตัวอย่าง ใจดี
                    </span>
                </div>

                {{-- Date marker + live preview text --}}
                <div
                    class="absolute -translate-x-1/2 -translate-y-1/2 pointer-events-none"
                    :style="`left: ${naturalWidth ? (dateX / naturalWidth * 100) : 0}%; top: ${naturalHeight ? (dateY / naturalHeight * 100) : 0}%;`"
                >
                    <span class="whitespace-nowrap text-sm font-bold text-amber-600 bg-white/80 px-2 py-0.5 rounded shadow border border-amber-200">
                        {{ now()->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>

            {{-- Precise numeric adjustment --}}
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-blue-600 flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-blue-500"></span>
                        ตำแหน่งชื่อ (พิกเซล)
                    </p>
                    <div class="flex gap-2">
                        <flux:input type="number" x-model.number="nameX" min="0" placeholder="X" />
                        <flux:input type="number" x-model.number="nameY" min="0" placeholder="Y" />
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-bold text-amber-600 flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        ตำแหน่งวันที่ (พิกเซล)
                    </p>
                    <div class="flex gap-2">
                        <flux:input type="number" x-model.number="dateX" min="0" placeholder="X" />
                        <flux:input type="number" x-model.number="dateY" min="0" placeholder="Y" />
                    </div>
                </div>
            </div>
            <flux:error name="nameX" />
            <flux:error name="nameY" />
            <flux:error name="dateX" />
            <flux:error name="dateY" />
        </div>
    @else
        <div class="rounded-3xl border-2 border-dashed border-outline-variant/40 bg-surface/50 p-16 text-center">
            <span class="material-symbols-outlined text-[48px] text-on-surface/20">workspace_premium</span>
            <p class="mt-3 text-on-surface/40 font-medium">อัปโหลดภาพเทมเพลตด้านบนเพื่อเริ่มกำหนดตำแหน่งชื่อและวันที่</p>
        </div>
    @endif
</div>
