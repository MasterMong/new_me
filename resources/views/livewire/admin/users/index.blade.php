<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface">จัดการผู้ใช้</h1>
            <p class="text-sm text-on-surface/60 mt-1">รายชื่อผู้ใช้งานทั้งหมดในระบบ</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="ค้นหาชื่อ หรืออีเมล..."
                icon="magnifying-glass"
                clearable
            />
        </div>
        <flux:select wire:model.live="roleFilter" class="sm:w-44">
            <flux:select.option value="">ทุกบทบาท</flux:select.option>
            @foreach ($userRoles as $role)
                <flux:select.option value="{{ $role->value }}">
                    {{ match($role) {
                        \App\Enums\UserRole::Admin  => 'ผู้ดูแลระบบ',
                        \App\Enums\UserRole::Expert => 'ผู้เชี่ยวชาญ',
                        \App\Enums\UserRole::Learner => 'ผู้เรียน',
                    } }}
                </flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-outline-variant/30 bg-white overflow-hidden">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-4">ชื่อ-นามสกุล</flux:table.column>
                <flux:table.column>อีเมล</flux:table.column>
                <flux:table.column>บทบาท</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column>วันที่สมัคร</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="ps-4">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-sm">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-on-surface">{{ $user->fullName() }}</p>
                                    @if ($user->position)
                                        <p class="text-xs text-on-surface/50">{{ $user->position->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/70">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $roleLabel = match($user->role) {
                                    \App\Enums\UserRole::Admin  => ['label' => 'ผู้ดูแลระบบ', 'color' => 'red'],
                                    \App\Enums\UserRole::Expert => ['label' => 'ผู้เชี่ยวชาญ', 'color' => 'purple'],
                                    \App\Enums\UserRole::Learner => ['label' => 'ผู้เรียน', 'color' => 'blue'],
                                };
                            @endphp
                            <flux:badge color="{{ $roleLabel['color'] }}" size="sm">
                                {{ $roleLabel['label'] }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->is_active)
                                <flux:badge color="green" size="sm">ใช้งานได้</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">ระงับการใช้</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-on-surface/60 text-sm">
                            {{ $user->created_at->format('d/m/Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="eye">ดูโปรไฟล์</flux:menu.item>
                                    <flux:menu.item
                                        wire:click="toggleActive({{ $user->id }})"
                                        wire:confirm="{{ $user->is_active ? 'ยืนยันการระงับบัญชีผู้ใช้นี้?' : 'ยืนยันการเปิดใช้งานบัญชีผู้ใช้นี้?' }}"
                                        :icon="$user->is_active ? 'no-symbol' : 'check-circle'"
                                    >
                                        {{ $user->is_active ? 'ระงับบัญชี' : 'เปิดใช้งาน' }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-on-surface/50 py-12">
                            <span class="material-symbols-outlined text-[40px] block mb-2 text-on-surface/30">group</span>
                            ไม่พบผู้ใช้ที่ตรงกับเงื่อนไข
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="flex justify-center">
            {{ $users->links() }}
        </div>
    @endif
</div>
