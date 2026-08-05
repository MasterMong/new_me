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
        <div class="lg:col-span-2 space-y-4">
            <h3 class="font-bold text-on-surface px-1">ประวัติการลงทะเบียนเรียน</h3>

            @forelse ($user->enrollments as $enrollment)
                <div x-data="{ open: false }" wire:key="enr-{{ $enrollment->id }}" class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 text-left hover:bg-surface/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <flux:icon.chevron-right variant="micro" x-bind:class="open ? 'rotate-90' : ''" class="transition-transform shrink-0 text-on-surface/40" />
                            <div class="min-w-0">
                                <p class="font-bold text-on-surface truncate">{{ $enrollment->course->title }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if ($enrollment->completed_at)
                                        <flux:badge color="green" size="sm">สำเร็จแล้ว</flux:badge>
                                    @else
                                        <flux:badge color="amber" size="sm">กำลังเรียน</flux:badge>
                                    @endif
                                    <span class="text-[10px] font-bold text-on-surface/40">{{ $enrollment->progress_percent }}% เสร็จสิ้น</span>
                                </div>
                            </div>
                        </div>
                        <div class="w-28 h-1.5 bg-surface-container rounded-full overflow-hidden hidden sm:block shrink-0">
                            <div class="h-full bg-primary rounded-full" style="width: {{ $enrollment->progress_percent }}%"></div>
                        </div>
                    </button>

                    <div x-show="open" x-cloak class="border-t border-outline-variant/20 bg-surface/40 divide-y divide-outline-variant/10">
                        @foreach ($enrollment->module_breakdown as $row)
                            @php
                                [$statusColor, $statusLabel, $statusIcon] = match ($row['status']) {
                                    'completed' => ['green', 'เรียนจบแล้ว', 'check_circle'],
                                    'in_progress' => ['blue', 'กำลังเรียน', 'autorenew'],
                                    'locked' => ['zinc', 'ยังไม่ปลดล็อค', 'lock'],
                                    default => ['zinc', 'ยังไม่เริ่ม', 'radio_button_unchecked'],
                                };
                            @endphp
                            <div class="flex items-center justify-between gap-4 px-5 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="material-symbols-outlined text-[18px] text-{{ $statusColor }}-500 shrink-0">{{ $statusIcon }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-on-surface truncate">
                                            โมดูล {{ $row['module']->module_number }}: {{ $row['module']->title }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                            <flux:badge size="sm" :color="$statusColor">{{ $statusLabel }}</flux:badge>
                                            @if ($row['pre_test'])
                                                <span class="text-[10px] font-bold text-on-surface/40">
                                                    Pre-test: {{ $row['pre_test']->attempts->where('user_id', $user->id)->isNotEmpty() ? 'ทำแล้ว' : 'ยังไม่ทำ' }}
                                                </span>
                                            @endif
                                            @if ($row['post_test'])
                                                <span class="text-[10px] font-bold text-on-surface/40">
                                                    Post-test: {{ $row['post_test_passed'] ? 'ผ่าน' : 'ยังไม่ผ่าน' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-on-surface/50 shrink-0">{{ $row['progress_percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-dashed border-outline-variant/30 p-10 text-center text-on-surface/40">
                    ยังไม่มีการลงทะเบียนเรียน
                </div>
            @endforelse
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
