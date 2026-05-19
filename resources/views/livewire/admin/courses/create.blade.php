<div class="p-6 space-y-6 max-w-5xl">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <flux:button variant="ghost" icon="arrow-left" wire:navigate :href="route('admin.courses.index')" />
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-on-surface/50 mb-0.5">
                    <span>จัดการคอร์ส</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span>เพิ่มคอร์สใหม่</span>
                </div>
                <h1 class="text-xl font-bold font-headline text-on-surface">เพิ่มคอร์สใหม่</h1>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <flux:button variant="ghost" wire:navigate :href="route('admin.courses.index')">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">สร้างคอร์ส</span>
                <span wire:loading wire:target="save">กำลังบันทึก...</span>
            </flux:button>
        </div>
    </div>

    @include('livewire.admin.courses._form')

    {{-- Bottom save (for long scroll) --}}
    <div class="flex justify-end gap-3 pt-2 border-t border-outline-variant/20">
        <flux:button variant="ghost" wire:navigate :href="route('admin.courses.index')">ยกเลิก</flux:button>
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">สร้างคอร์ส</span>
            <span wire:loading wire:target="save">กำลังบันทึก...</span>
        </flux:button>
    </div>

</div>
