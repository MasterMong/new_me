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
    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">กลุ่มผู้เรียน</flux:table.column>
                <flux:table.column>สมาชิก</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($groups as $group)
                    <flux:table.row wire:key="group-{{ $group->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex flex-col py-1">
                                <p class="font-semibold text-on-surface leading-tight">{{ $group->name }}</p>
                                @if ($group->description)
                                    <p class="text-[11px] text-on-surface/50 mt-1 max-w-sm line-clamp-1">{{ $group->description }}</p>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon name="users" variant="micro" class="text-primary" />
                                <span class="text-sm font-bold text-on-surface">{{ number_format($group->users_count) }}</span>
                                <span class="text-[10px] text-on-surface/40 uppercase tracking-tighter font-bold">Members</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($group->is_active)
                                <flux:badge color="green" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">
                                    Enabled
                                </flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">
                                    Disabled
                                </flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <div class="flex justify-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full hover:bg-surface-container" />
                                    <flux:menu class="min-w-44">
                                        <flux:menu.item icon="users" wire:navigate :href="route('admin.groups.members', $group)">จัดการสมาชิกในกลุ่ม</flux:menu.item>
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
                                            ลบกลุ่มนี้
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">group_work</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบกลุ่มผู้เรียน</h3>
                                <p class="text-sm text-on-surface/40 mt-1">เริ่มต้นด้วยการสร้างกลุ่มใหม่เพื่อจัดการสิทธิ์เข้าถึง</p>
                            </div>
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
