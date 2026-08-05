<div class="p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">ความก้าวหน้าตามรายบุคคล</h1>
            <p class="text-sm text-on-surface/60 mt-1">ติดตามประวัติการเรียน ผลการสอบ และความสำเร็จของผู้เรียนแต่ละคน</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="ค้นหาชื่อ, นามสกุล หรืออีเมล..."
            icon="magnifying-glass"
            class="flex-1"
        />
        <flux:select wire:model.live="groupId" placeholder="เลือกกลุ่มผู้เรียน" class="sm:w-64">
            <flux:select.option value="">ทุกกลุ่มผู้เรียน</flux:select.option>
            @foreach ($groups as $group)
                <flux:select.option :value="$group->id">{{ $group->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:field class="sm:w-44">
            <flux:label>ลงทะเบียนตั้งแต่</flux:label>
            <flux:input type="date" wire:model.live="enrolledFrom" />
        </flux:field>

        <flux:field class="sm:w-44">
            <flux:label>ลงทะเบียนถึง</flux:label>
            <flux:input type="date" wire:model.live="enrolledTo" />
        </flux:field>

        @if ($enrolledFrom || $enrolledTo)
            <flux:button variant="ghost" size="sm" wire:click="clearDateFilter">
                ล้างช่วงวันที่
            </flux:button>
        @endif
    </div>

    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">ผู้เรียน</flux:table.column>
                <flux:table.column>กลุ่ม</flux:table.column>
                <flux:table.column>คอร์สที่ลงทะเบียน</flux:table.column>
                <flux:table.column>สำเร็จแล้ว</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-3 py-1">
                                <div class="size-10 shrink-0 rounded-full bg-gradient-to-tr from-primary/20 to-primary/5 flex items-center justify-center text-primary font-bold text-sm">
                                    {{ $user->initials() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface leading-tight">{{ $user->fullName() }}</p>
                                    <p class="text-[11px] text-on-surface/50 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->groupMemberships as $membership)
                                    <flux:badge size="sm" color="zinc">{{ $membership->group->name }}</flux:badge>
                                @empty
                                    <span class="text-xs text-on-surface/30">-</span>
                                @endforelse
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-on-surface">{{ $user->enrollments_count }}</span>
                                <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Courses</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-green-600">
                                    {{ $user->enrollments()->whereNotNull('completed_at')->count() }}
                                </span>
                                <span class="text-[10px] font-bold text-on-surface/40 uppercase tracking-tight">Completed</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="pe-6 text-right">
                            <flux:button variant="primary" size="sm" wire:click="selectUser({{ $user->id }})">
                                ดูรายละเอียด
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">person_search</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบข้อมูลผู้เรียน</h3>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
