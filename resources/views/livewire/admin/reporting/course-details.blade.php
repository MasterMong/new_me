<div class="p-6 space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" wire:click="selectCourse(null)" />
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">{{ $course->title }}</h1>
            <p class="text-sm text-on-surface/60">รายละเอียดความก้าวหน้าของผู้เรียนรายบุคคล</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4">
        <flux:select wire:model.live="groupId" placeholder="ทุกกลุ่มผู้เรียน" class="sm:w-64">
            <flux:select.option value="">ทุกกลุ่มผู้เรียน</flux:select.option>
            @foreach ($groups as $group)
                <flux:select.option :value="$group->id">{{ $group->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="premium-table-container">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="ps-6">ผู้เรียน</flux:table.column>
                <flux:table.column>กลุ่ม</flux:table.column>
                <flux:table.column>ความก้าวหน้า</flux:table.column>
                <flux:table.column>สถานะ</flux:table.column>
                <flux:table.column>วันที่ลงทะเบียน</flux:table.column>
                <flux:table.column class="pe-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($enrollments as $enrollment)
                    <flux:table.row wire:key="enrollment-{{ $enrollment->id }}" class="premium-table-row">
                        <flux:table.cell class="ps-6">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                    {{ $enrollment->user->initials() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface leading-none">{{ $enrollment->user->fullName() }}</p>
                                    <p class="text-[10px] text-on-surface/40 truncate">{{ $enrollment->user->email }}</p>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @foreach ($enrollment->user->groupMemberships as $membership)
                                <flux:badge size="sm" color="zinc" class="mb-1">{{ $membership->group->name }}</flux:badge>
                            @endforeach
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="w-full max-w-[120px]">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold text-on-surface/40 uppercase">Progress</span>
                                    <span class="text-[10px] font-bold text-primary">80%</span> {{-- Mock for now --}}
                                </div>
                                <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
                                    <div class="h-full bg-primary rounded-full" style="width: 80%"></div>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($enrollment->completed_at)
                                <flux:badge color="green" size="sm">สำเร็จแล้ว</flux:badge>
                            @else
                                <flux:badge color="amber" size="sm">กำลังเรียน</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-on-surface/50 font-medium">
                            {{ $enrollment->enrolled_at->translatedFormat('d M Y') }}
                        </flux:table.cell>

                        <flux:table.cell class="pe-6">
                            <flux:button variant="ghost" size="sm" icon="eye" wire:navigate :href="route('admin.reporting.user-progress', ['selectedUserId' => $enrollment->user_id])" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="premium-table-empty">
                                <span class="material-symbols-outlined premium-table-empty-icon">person_search</span>
                                <h3 class="text-lg font-bold text-on-surface/60">ไม่พบผู้เรียนในหลักสูตรนี้</h3>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $enrollments->links() }}
    </div>
</div>
