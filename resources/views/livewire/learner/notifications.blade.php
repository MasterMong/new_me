<div class="p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <flux:heading size="xl" class="mb-2">การแจ้งเตือน</flux:heading>
            <flux:subheading>ติดตามข่าวสารและสถานะการเรียนของคุณ</flux:subheading>
        </div>
        
        @if($notifications->where('is_read', false)->count() > 0)
            <flux:button variant="ghost" size="sm" wire:click="markAllAsRead">
                <flux:icon.check-circle variant="micro" class="mr-2" />
                อ่านทั้งหมดแล้ว
            </flux:button>
        @endif
    </div>

    {{-- Notifications List --}}
    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div @class([
                'group p-5 rounded-3xl border transition-all flex gap-5 items-start',
                'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800' => $notification->is_read,
                'bg-primary/5 border-primary/20 ring-1 ring-primary/10 shadow-sm' => !$notification->is_read,
            ])>
                {{-- Type Icon --}}
                <div @class([
                    'size-12 rounded-2xl flex items-center justify-center shrink-0',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' => $notification->is_read,
                    'bg-primary text-white shadow-lg shadow-primary/20' => !$notification->is_read,
                ])>
                    @switch($notification->type->value)
                        @case('certificate_issued')
                            <span class="material-symbols-outlined text-[24px]">workspace_premium</span>
                            @break
                        @case('review_completed')
                            <span class="material-symbols-outlined text-[24px]">rate_review</span>
                            @break
                        @case('revision_needed')
                            <span class="material-symbols-outlined text-[24px]">edit_note</span>
                            @break
                        @default
                            <span class="material-symbols-outlined text-[24px]">notifications</span>
                    @endswitch
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <flux:heading size="md" @class([
                            'truncate',
                            'text-zinc-500' => $notification->is_read,
                            'text-zinc-900 dark:text-white' => !$notification->is_read,
                        ])>
                            {{ $notification->title }}
                        </flux:heading>
                        <span class="text-[10px] font-bold text-zinc-400 uppercase whitespace-nowrap ml-4">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p @class([
                        'text-sm leading-relaxed mb-4',
                        'text-zinc-400' => $notification->is_read,
                        'text-zinc-600 dark:text-zinc-400' => !$notification->is_read,
                    ])>
                        {{ $notification->message }}
                    </p>

                    <div class="flex items-center gap-4">
                        @if(!$notification->is_read)
                            <flux:button variant="ghost" size="xs" wire:click="markAsRead({{ $notification->id }})" class="text-primary hover:text-primary">
                                ทำเครื่องหมายว่าอ่านแล้ว
                            </flux:button>
                        @endif
                        <flux:button variant="ghost" size="xs" class="text-red-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity" wire:click="delete({{ $notification->id }})">
                            ลบ
                        </flux:button>
                    </div>
                </div>

                {{-- Unread Dot --}}
                @if(!$notification->is_read)
                    <div class="size-2 bg-primary rounded-full shrink-0 mt-2"></div>
                @endif
            </div>
        @empty
            <div class="py-20 flex flex-col items-center justify-center bg-zinc-50 dark:bg-zinc-900/50 rounded-[2.5rem] border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                <div class="size-20 mb-6 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                    <span class="material-symbols-outlined text-[40px]">notifications_off</span>
                </div>
                <flux:heading size="lg" class="mb-2">ไม่มีการแจ้งเตือน</flux:heading>
                <flux:subheading>คุณได้อ่านการแจ้งเตือนทั้งหมดครบแล้ว</flux:subheading>
            </div>
        @endforelse
    </div>
</div>
