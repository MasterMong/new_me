<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-headline text-on-surface">รีวิวจากผู้เรียน</h1>
            <p class="text-sm text-on-surface/60 mt-1">ความคิดเห็นและความพึงพอใจต่อหลักสูตร</p>
        </div>

        <div class="flex items-center gap-4 bg-white px-4 py-2 rounded-2xl border border-outline-variant/30 shadow-sm">
            <div class="text-center border-e border-outline-variant/30 pe-4">
                <p class="text-2xl font-bold text-primary">{{ $averageRating }}</p>
                <p class="text-[10px] text-on-surface/40 uppercase font-bold">Avg Rating</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-on-surface">{{ number_format($totalReviews) }}</p>
                <p class="text-[10px] text-on-surface/40 uppercase font-bold">Total Reviews</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="ค้นหาชื่อผู้เรียน หรือชื่อคอร์ส..."
                icon="magnifying-glass"
                clearable
            />
        </div>
        <flux:select wire:model.live="ratingFilter" class="sm:w-44">
            <flux:select.option value="">ทุกระดับคะแนน</flux:select.option>
            <flux:select.option value="5">5 ดาว</flux:select.option>
            <flux:select.option value="4">4 ดาว</flux:select.option>
            <flux:select.option value="3">3 ดาว</flux:select.option>
            <flux:select.option value="2">2 ดาว</flux:select.option>
            <flux:select.option value="1">1 ดาว</flux:select.option>
        </flux:select>
    </div>

    {{-- Reviews List --}}
    <div class="grid grid-cols-1 gap-4">
        @forelse ($reviews as $review)
            <div wire:key="review-{{ $review->id }}" class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="size-10 shrink-0 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                            {{ $review->user->initials() }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-on-surface">{{ $review->user->fullName() }}</span>
                                <span class="text-xs text-on-surface/30">•</span>
                                <span class="text-xs text-on-surface/50">{{ $review->created_at->translatedFormat('d M Y H:i') }}</span>
                            </div>
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-[16px] {{ $i <= $review->rating ? 'text-amber-400 fill-amber-400' : 'text-zinc-200' }}">
                                            star
                                        </span>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-on-surface/40 uppercase tracking-tighter">on</span>
                                <span class="text-xs font-bold text-primary truncate max-w-[200px]">{{ $review->course->title }}</span>
                            </div>
                            <p class="text-on-surface/80 text-sm leading-relaxed italic">
                                "{{ $review->comment ?: 'ไม่มีความคิดเห็นเพิ่มเติม' }}"
                            </p>
                        </div>
                    </div>

                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="rounded-full opacity-0 group-hover:opacity-100 transition-opacity" />
                        <flux:menu>
                            <flux:menu.item
                                icon="trash"
                                variant="danger"
                                wire:click="deleteReview({{ $review->id }})"
                                wire:confirm="ยืนยันการลบรีวิวนี้?"
                            >
                                ลบรีวิวนี้
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        @empty
            <div class="premium-table-empty bg-white py-16">
                <span class="material-symbols-outlined premium-table-empty-icon text-primary/20">reviews</span>
                <h3 class="text-lg font-bold text-on-surface/60">ยังไม่มีรีวิวจากผู้เรียน</h3>
                <p class="text-sm text-on-surface/40 mt-1">รีวิวที่ผู้เรียนส่งเข้ามาจะปรากฏที่นี่</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($reviews->hasPages())
        <div class="flex justify-center">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
