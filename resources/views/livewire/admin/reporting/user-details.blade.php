<div class="p-6 space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" wire:click="selectUser(null)" />
        <div class="flex items-center gap-4">
            <div class="size-16 rounded-3xl bg-primary/10 flex items-center justify-center text-primary text-2xl font-bold">
                {{ $user->initials() }}
            </div>
            <div>
                <h1 class="text-2xl font-bold font-headline text-on-surface tracking-tight">{{ $user->fullName() }}</h1>
                <p class="text-sm text-on-surface/60">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="premium-table-container">
                <div class="px-6 py-4 border-b border-outline-variant/30 flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">ประวัติการลงทะเบียนเรียน</h3>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="ps-6">หลักสูตร</flux:table.column>
                        <flux:table.column>สถานะ</flux:table.column>
                        <flux:table.column>ความก้าวหน้า</flux:table.column>
                        <flux:table.column class="pe-6"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($user->enrollments as $enrollment)
                            <flux:table.row wire:key="enr-{{ $enrollment->id }}" class="premium-table-row">
                                <flux:table.cell class="ps-6">
                                    <span class="font-bold text-on-surface">{{ $enrollment->course->title }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($enrollment->completed_at)
                                        <flux:badge color="green" size="sm">สำเร็จแล้ว</flux:badge>
                                    @else
                                        <flux:badge color="amber" size="sm">กำลังเรียน</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="h-1.5 w-24 bg-surface-container rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full" style="width: 50%"></div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell class="pe-6">
                                    <flux:button variant="ghost" size="sm" icon="eye" wire:navigate :href="route('admin.reporting.course-progress', ['selectedCourseId' => $enrollment->course_id])" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-outline-variant/30 p-6 shadow-sm">
                <h3 class="font-bold text-on-surface mb-4">ข้อมูลสังกัด</h3>
                <div class="space-y-3">
                    @foreach ($user->groupMemberships as $membership)
                        <div class="flex items-center gap-3 p-3 bg-surface rounded-2xl border border-outline-variant/10">
                            <span class="material-symbols-outlined text-primary/40">groups</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-on-surface truncate">{{ $membership->group->name }}</p>
                                <p class="text-[10px] text-on-surface/40 uppercase font-bold">Member since {{ $membership->assigned_at->format('M Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
