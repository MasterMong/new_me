<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface">จัดการกลุ่มผู้เรียน</h1>
            <p class="text-sm text-on-surface/60 mt-1">กลุ่มสำหรับควบคุมการเข้าถึงเนื้อหา</p>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$set('showCreateModal', true)">
            เพิ่มกลุ่มใหม่
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="ค้นหาชื่อกลุ่ม..."
        icon="magnifying-glass"
        clearable
        class="max-w-sm"
    />

    {{-- Table --}}
    <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>ชื่อกลุ่ม</flux:table.column>
                <flux:table.column>คำอธิบาย</flux:table.column>
                <flux:table.column>สมาชิก</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($groups as $group)
                    <flux:table.row wire:key="group-{{ $group->id }}">
                        <flux:table.cell class="font-medium text-on-surface">{{ $group->name }}</flux:table.cell>
                        <flux:table.cell class="text-on-surface/60 text-sm max-w-xs truncate">
                            {{ $group->description ?? '-' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="blue" size="sm">
                                {{ number_format($group->users_count) }} คน
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($group->is_active)
                                <flux:badge color="green" size="sm">ใช้งาน</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">ปิดการใช้งาน</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="users" wire:navigate :href="route('admin.groups.members', $group)">จัดการสมาชิก</flux:menu.item>
                                    <flux:menu.item
                                        wire:click="toggleActive({{ $group->id }})"
                                        :icon="$group->is_active ? 'no-symbol' : 'check-circle'"
                                    >
                                        {{ $group->is_active ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน' }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item
                                        icon="trash"
                                        variant="danger"
                                        wire:click="deleteGroup({{ $group->id }})"
                                        wire:confirm="ยืนยันการลบกลุ่ม '{{ $group->name }}'?"
                                    >
                                        ลบ
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-on-surface/50 py-12">
                            <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface/30">group_work</span>
                            ยังไม่มีกลุ่มผู้เรียน
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($groups->hasPages())
        <div class="flex justify-center">
            {{ $groups->links() }}
        </div>
    @endif

    {{-- Create Modal --}}
    <flux:modal wire:model="showCreateModal" class="w-full max-w-md">
        <flux:heading size="lg">เพิ่มกลุ่มผู้เรียน</flux:heading>
        <flux:text class="mt-1 mb-6">สร้างกลุ่มใหม่สำหรับควบคุมการเข้าถึงเนื้อหา</flux:text>

        <div class="space-y-4">
            <flux:field>
                <flux:label>ชื่อกลุ่ม <span class="text-error">*</span></flux:label>
                <flux:input wire:model="newGroupName" placeholder="ชื่อกลุ่ม..." />
                <flux:error name="newGroupName" />
            </flux:field>

            <flux:field>
                <flux:label>คำอธิบาย</flux:label>
                <flux:textarea wire:model="newGroupDescription" placeholder="อธิบายวัตถุประสงค์ของกลุ่ม..." rows="3" />
                <flux:error name="newGroupDescription" />
            </flux:field>
        </div>

        <div class="flex gap-3 mt-6 justify-end">
            <flux:button variant="ghost" wire:click="$set('showCreateModal', false)">ยกเลิก</flux:button>
            <flux:button variant="primary" wire:click="createGroup">สร้างกลุ่ม</flux:button>
        </div>
    </flux:modal>
</div>
