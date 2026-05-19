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
    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">ผู้ใช้งาน</flux:table.column>
                <flux:table.column>บทบาท</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column>วันที่เข้าร่วม</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-4 py-1">
                                <div class="size-10 shrink-0 rounded-full bg-gradient-to-tr from-primary/20 to-primary/5 flex items-center justify-center text-primary font-bold text-sm border border-primary/10">
                                    {{ $user->initials() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-on-surface leading-tight">{{ $user->fullName() }}</p>
                                    <p class="text-[11px] text-on-surface/50 mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @php
                                $roleLabel = match($user->role) {
                                    \App\Enums\UserRole::Admin  => ['label' => 'ผู้ดูแลระบบ', 'color' => 'red'],
                                    \App\Enums\UserRole::Expert => ['label' => 'ผู้เชี่ยวชาญ', 'color' => 'purple'],
                                    \App\Enums\UserRole::Learner => ['label' => 'ผู้เรียน', 'color' => 'blue'],
                                };
                            @endphp
                            <flux:badge color="{{ $roleLabel['color'] }}" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-md">
                                {{ $roleLabel['label'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($user->is_active)
                                <div class="flex items-center gap-1.5">
                                    <div class="size-1.5 rounded-full bg-green-500 animate-pulse"></div>
                                    <span class="text-xs font-medium text-green-600">Active</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5">
                                    <div class="size-1.5 rounded-full bg-zinc-400"></div>
                                    <span class="text-xs font-medium text-zinc-500">Suspended</span>
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-on-surface/60 text-sm font-medium">
                            {{ $user->created_at->translatedFormat('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <div class="flex justify-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full hover:bg-surface-container" />
                                    <flux:menu class="min-w-40">
                                        <flux:menu.item icon="eye">ดูรายละเอียด</flux:menu.item>
                                        <flux:menu.item
                                            wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->is_active ? 'ยืนยันการระงับบัญชีผู้ใช้นี้?' : 'ยืนยันการเปิดใช้งานบัญชีผู้ใช้นี้?' }}"
                                            :icon="$user->is_active ? 'no-symbol' : 'check-circle'"
                                        >
                                            {{ $user->is_active ? 'ระงับการเข้าถึง' : 'เปิดการเข้าถึง' }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">person_search</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบผู้ใช้งาน</h3>
                                <p class="text-sm text-on-surface/40 mt-1">ลองเปลี่ยนคำค้นหาหรือบทบาทใหม่อีกครั้ง</p>
                            </div>
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
