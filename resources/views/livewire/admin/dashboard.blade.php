<div class="p-6 space-y-8">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold font-headline text-on-surface">แดชบอร์ดผู้ดูแลระบบ</h1>
        <p class="text-sm text-on-surface/60 mt-1">ภาพรวมสถิติและกิจกรรมในระบบ ME-Learning</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 flex items-center gap-4 shadow-sm">
            <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10">
                <span class="material-symbols-outlined text-primary text-[24px]">group</span>
            </div>
            <div>
                <p class="text-2xl font-bold font-headline text-on-surface">{{ number_format($totalUsers) }}</p>
                <p class="text-sm text-on-surface/60">ผู้ใช้ทั้งหมด</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 flex items-center gap-4 shadow-sm">
            <div class="flex size-12 items-center justify-center rounded-xl bg-secondary-container/40">
                <span class="material-symbols-outlined text-secondary text-[24px]">book</span>
            </div>
            <div>
                <p class="text-2xl font-bold font-headline text-on-surface">{{ number_format($totalCourses) }}</p>
                <p class="text-sm text-on-surface/60">คอร์สทั้งหมด</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 flex items-center gap-4 shadow-sm">
            <div class="flex size-12 items-center justify-center rounded-xl bg-blue-50">
                <span class="material-symbols-outlined text-blue-600 text-[24px]">school</span>
            </div>
            <div>
                <p class="text-2xl font-bold font-headline text-on-surface">{{ number_format($totalEnrollments) }}</p>
                <p class="text-sm text-on-surface/60">การลงทะเบียนเรียน</p>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-outline-variant/30 p-5 flex items-center gap-4 shadow-sm">
            <div class="flex size-12 items-center justify-center rounded-xl bg-amber-50">
                <span class="material-symbols-outlined text-amber-500 text-[24px]">workspace_premium</span>
            </div>
            <div>
                <p class="text-2xl font-bold font-headline text-on-surface">{{ number_format($totalCertificates) }}</p>
                <p class="text-sm text-on-surface/60">ใบประกาศนียบัตร</p>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('admin.courses.index') }}" wire:navigate
            class="group rounded-2xl border border-outline-variant/30 bg-white p-5 hover:border-primary/40 hover:shadow-md transition-all">
            <div class="flex items-center gap-3 mb-3">
                <span class="material-symbols-outlined text-primary text-[22px]">library_books</span>
                <span class="font-semibold text-on-surface font-headline">จัดการคอร์ส</span>
            </div>
            <p class="text-sm text-on-surface/60">เพิ่ม แก้ไข และจัดการหลักสูตรทั้งหมด</p>
        </a>

        <a href="{{ route('admin.users.index') }}" wire:navigate
            class="group rounded-2xl border border-outline-variant/30 bg-white p-5 hover:border-primary/40 hover:shadow-md transition-all">
            <div class="flex items-center gap-3 mb-3">
                <span class="material-symbols-outlined text-primary text-[22px]">manage_accounts</span>
                <span class="font-semibold text-on-surface font-headline">จัดการผู้ใช้</span>
            </div>
            <p class="text-sm text-on-surface/60">ดูและจัดการบัญชีผู้ใช้งานทั้งหมด</p>
        </a>

        <a href="{{ route('admin.reports.index') }}" wire:navigate
            class="group rounded-2xl border border-outline-variant/30 bg-white p-5 hover:border-primary/40 hover:shadow-md transition-all">
            <div class="flex items-center gap-3 mb-3">
                <span class="material-symbols-outlined text-primary text-[22px]">bar_chart</span>
                <span class="font-semibold text-on-surface font-headline">รายงาน</span>
            </div>
            <p class="text-sm text-on-surface/60">ดูรายงานสถิติและผลการเรียน</p>
        </a>
    </div>

    {{-- Recent Courses --}}
    <div>
        <div class="flex items-center justify-between mb-4 px-2">
            <h2 class="text-xl font-bold font-headline text-on-surface">คอร์สล่าสุด</h2>
            <flux:button variant="ghost" size="sm" icon="arrow-right" icon-trailing href="{{ route('admin.courses.index') }}" wire:navigate class="rounded-xl">
                ดูทั้งหมด
            </flux:button>
        </div>

        <div class="premium-table-container">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-6">ชื่อคอร์ส</flux:table.column>
                    <flux:table.column>ผู้สร้าง</flux:table.column>
                    <flux:table.column>สถานะ</flux:table.column>
                    <flux:table.column class="pe-6">สร้างเมื่อ</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentCourses as $course)
                        <flux:table.row wire:key="course-{{ $course->id }}" class="premium-table-row">
                            <flux:table.cell class="ps-6">
                                <div class="flex items-center gap-3 py-1">
                                    @if ($course->thumbnail_url)
                                        <div class="size-10 shrink-0 rounded-xl overflow-hidden border border-outline-variant/20 shadow-sm">
                                            <img src="{{ $course->thumbnail_url }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="size-10 shrink-0 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/10">
                                            <span class="material-symbols-outlined text-[20px]">book</span>
                                        </div>
                                    @endif
                                    <span class="font-bold text-on-surface truncate">{{ $course->title }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-on-surface/70 font-medium">
                                {{ $course->creator?->fullName() ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($course->is_published)
                                    <flux:badge color="green" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">Published</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" class="font-bold uppercase tracking-wide text-[10px] px-2 py-0.5 rounded-full">Draft</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="pe-6 text-on-surface/50 text-[11px] font-bold">
                                {{ $course->created_at->diffForHumans() }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
                                <div class="premium-table-empty">
                                    <span class="material-symbols-outlined premium-table-empty-icon">history</span>
                                    <h3 class="text-lg font-bold text-on-surface/60">ยังไม่มีประวัติคอร์ส</h3>
                                    <p class="text-sm text-on-surface/40 mt-1">คอร์สที่เพิ่งสร้างจะแสดงที่นี่</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
