<div class="p-6 space-y-6 max-w-5xl">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <flux:button variant="ghost" icon="arrow-left" wire:navigate :href="route('admin.groups.index')" />
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-on-surface/50 mb-0.5">
                    <span>จัดการกลุ่ม</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="truncate">{{ $group->name }}</span>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span>สมาชิก</span>
                </div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold font-headline text-on-surface">จัดการสมาชิก</h1>
                    @if ($group->is_active)
                        <flux:badge color="green" size="sm">ใช้งาน</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">ปิดการใช้งาน</flux:badge>
                    @endif
                </div>
                @if ($group->description)
                    <p class="text-sm text-on-surface/50 mt-0.5">{{ $group->description }}</p>
                @endif
            </div>
        </div>
        <flux:button variant="primary" wire:click="$set('showAddModal', true)">
            <span class="material-symbols-outlined text-[18px]">person_add</span>
            เพิ่มสมาชิก
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="ค้นหาชื่อหรืออีเมลสมาชิก..."
        icon="magnifying-glass"
        clearable
        class="max-w-sm"
    />

    {{-- Members table --}}
    <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-4">ชื่อ-นามสกุล</flux:table.column>
                <flux:table.column>อีเมล</flux:table.column>
                <flux:table.column>บทบาท</flux:table.column>
                <flux:table.column>เพิ่มโดย</flux:table.column>
                <flux:table.column>วันที่เพิ่ม</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($members as $membership)
                    <flux:table.row wire:key="member-{{ $membership->id }}">
                        <flux:table.cell class="ps-4">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-sm">
                                    {{ $membership->user->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-on-surface">{{ $membership->user->fullName() }}</p>
                                    @if ($membership->user->position)
                                        <p class="text-xs text-on-surface/50">{{ $membership->user->position->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/70 text-sm">
                            {{ $membership->user->email }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $roleLabel = match($membership->user->role) {
                                    \App\Enums\UserRole::Admin   => ['label' => 'ผู้ดูแลระบบ', 'color' => 'red'],
                                    \App\Enums\UserRole::Expert  => ['label' => 'ผู้เชี่ยวชาญ', 'color' => 'purple'],
                                    \App\Enums\UserRole::Learner => ['label' => 'ผู้เรียน',      'color' => 'blue'],
                                };
                            @endphp
                            <flux:badge color="{{ $roleLabel['color'] }}" size="sm">{{ $roleLabel['label'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/60 text-sm">
                            {{ $membership->assignedBy?->fullName() ?? 'ระบบ' }}
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/60 text-sm">
                            {{ $membership->assigned_at->format('d/m/Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                wire:click="removeMember({{ $membership->id }})"
                                wire:confirm="ยืนยันการนำ {{ $membership->user->fullName() }} ออกจากกลุ่มนี้?"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex size-14 items-center justify-center rounded-2xl bg-surface">
                                    <span class="material-symbols-outlined text-[28px] text-on-surface/30">group</span>
                                </div>
                                <p class="text-on-surface/50">ยังไม่มีสมาชิกในกลุ่มนี้</p>
                                <flux:button variant="primary" size="sm" wire:click="$set('showAddModal', true)">
                                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                                    เพิ่มสมาชิกแรก
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    @if ($members->hasPages())
        <div class="flex justify-center">
            {{ $members->links() }}
        </div>
    @endif


    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- ADD MEMBERS MODAL                                      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <flux:modal wire:model="showAddModal" class="w-full max-w-lg">

        <div class="flex items-center gap-3 mb-1">
            <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10">
                <span class="material-symbols-outlined text-[20px] text-primary">person_add</span>
            </div>
            <flux:heading size="lg">เพิ่มสมาชิกเข้ากลุ่ม</flux:heading>
        </div>
        <flux:text class="mb-5 ps-12">ค้นหาและเลือกผู้ใช้ที่ต้องการเพิ่มเข้า "{{ $group->name }}"</flux:text>

        {{-- Search --}}
        <flux:input
            wire:model.live.debounce.300ms="userSearch"
            placeholder="ค้นหาชื่อหรืออีเมล..."
            icon="magnifying-glass"
            clearable
            class="mb-4"
        />

        {{-- User list --}}
        <div class="rounded-xl border border-outline-variant/30 overflow-hidden max-h-72 overflow-y-auto">
            @forelse ($availableUsers as $user)
                <label
                    wire:key="available-{{ $user->id }}"
                    class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-surface transition-colors
                        {{ ! $loop->last ? 'border-b border-outline-variant/20' : '' }}
                        {{ in_array((string) $user->id, $selectedUserIds) ? 'bg-primary/5' : '' }}"
                >
                    <flux:checkbox wire:model="selectedUserIds" :value="(string) $user->id" />
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-sm">
                        {{ $user->initials() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-on-surface">{{ $user->fullName() }}</p>
                        <p class="text-xs text-on-surface/50 truncate">{{ $user->email }}</p>
                    </div>
                    @php
                        $badge = match($user->role) {
                            \App\Enums\UserRole::Admin   => ['ผู้ดูแลระบบ', 'red'],
                            \App\Enums\UserRole::Expert  => ['ผู้เชี่ยวชาญ', 'purple'],
                            \App\Enums\UserRole::Learner => ['ผู้เรียน', 'blue'],
                        };
                    @endphp
                    <flux:badge color="{{ $badge[1] }}" size="sm">{{ $badge[0] }}</flux:badge>
                </label>
            @empty
                <div class="flex flex-col items-center justify-center gap-2 py-10 text-sm text-on-surface/40">
                    <span class="material-symbols-outlined text-[28px]">
                        {{ $userSearch ? 'search_off' : 'group_add' }}
                    </span>
                    {{ $userSearch ? 'ไม่พบผู้ใช้ที่ตรงกับการค้นหา' : 'ผู้ใช้ที่ยังไม่ได้อยู่ในกลุ่มนี้จะแสดงที่นี่' }}
                </div>
            @endforelse
        </div>

        <div class="flex items-center justify-between mt-5">
            <p class="text-sm text-on-surface/50">
                @if (count($selectedUserIds) > 0)
                    เลือกแล้ว {{ count($selectedUserIds) }} คน
                @else
                    เลือกผู้ใช้ที่ต้องการเพิ่ม
                @endif
            </p>
            <div class="flex gap-3">
                <flux:button variant="ghost" wire:click="$set('showAddModal', false)">ยกเลิก</flux:button>
                <flux:button
                    variant="primary"
                    wire:click="addMembers"
                    wire:loading.attr="disabled"
                    wire:target="addMembers"
                    :disabled="count($selectedUserIds) === 0"
                >
                    <span wire:loading.remove wire:target="addMembers">
                        เพิ่ม {{ count($selectedUserIds) > 0 ? count($selectedUserIds).' คน' : '' }}
                    </span>
                    <span wire:loading wire:target="addMembers">กำลังเพิ่ม...</span>
                </flux:button>
            </div>
        </div>

    </flux:modal>

</div>
