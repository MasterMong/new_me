<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface font-body text-on-surface antialiased">
        <flux:sidebar sticky collapsible="mobile"
            class="border-e-0 !bg-primary shadow-xl shadow-primary/20">

            {{-- Brand --}}
            <flux:sidebar.header class="border-b border-white/10 pb-4">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-2">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-secondary-container text-on-secondary-container font-bold text-sm font-headline shrink-0">
                        ME
                    </div>
                    <span class="text-lg font-bold tracking-tight text-on-primary font-headline">ME-Learning</span>
                </a>
                <flux:sidebar.collapse class="lg:hidden text-on-primary/60 hover:text-on-primary" />
            </flux:sidebar.header>

            @php
                $settingsRoute = match(auth()->user()->role) {
                    \App\Enums\UserRole::Expert => route('expert.settings'),
                    \App\Enums\UserRole::Learner => route('learn.settings.index'),
                    default => route('profile.edit'),
                };
            @endphp

            {{-- Main Navigation --}}
            <flux:sidebar.nav class="mt-2">
                <flux:sidebar.group class="grid gap-0.5">
                    @if(auth()->user()->role === \App\Enums\UserRole::Learner || auth()->user()->role === \App\Enums\UserRole::Admin)
                        <flux:sidebar.item
                            wire:navigate
                            :href="route('dashboard')"
                            :current="request()->routeIs('dashboard')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">home</span>
                            </x-slot>
                            หน้าแรก
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            wire:navigate
                            :href="route('learn.dashboard')"
                            :current="request()->routeIs('learn.dashboard')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">school</span>
                            </x-slot>
                            คอร์สเรียนของฉัน
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            wire:navigate
                            :href="route('courses.index')"
                            :current="request()->routeIs('courses.index')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">search</span>
                            </x-slot>
                            ค้นหาคอร์ส
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            wire:navigate
                            :href="route('learn.progress')"
                            :current="request()->routeIs('learn.progress')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">leaderboard</span>
                            </x-slot>
                            ความก้าวหน้าของฉัน
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            wire:navigate
                            :href="route('learn.certificates.index')"
                            :current="request()->routeIs('learn.certificates.*')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                            </x-slot>
                            ใบประกาศนียบัตร
                        </flux:sidebar.item>

                        @php
                            $unreadNotificationsCount = \App\Models\LearnerNotification::where('user_id', auth()->id())
                                ->where('is_read', false)
                                ->count();
                        @endphp
                        <flux:sidebar.item
                            wire:navigate
                            :href="route('learn.notifications.index')"
                            :current="request()->routeIs('learn.notifications.*')"
                            :badge="$unreadNotificationsCount > 0 ? $unreadNotificationsCount : null"
                            badge:color="red"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">notifications</span>
                            </x-slot>
                            การแจ้งเตือน
                        </flux:sidebar.item>
                    @endif

                    @if(auth()->user()->role === \App\Enums\UserRole::Expert)
                        <flux:sidebar.item
                            wire:navigate
                            :href="route('expert.dashboard')"
                            :current="request()->routeIs('expert.dashboard')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                            </x-slot>
                            แดชบอร์ด
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            wire:navigate
                            :href="route('expert.reports.index')"
                            :current="request()->routeIs('expert.reports.*')"
                            class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                        >
                            <x-slot name="icon">
                                <span class="material-symbols-outlined text-[20px]">assessment</span>
                            </x-slot>
                            รายงานผล
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if(auth()->user()->role === \App\Enums\UserRole::Admin || auth()->user()->role === \App\Enums\UserRole::Expert)
                    <div class="mx-3 my-3 h-px bg-white/10"></div>
                    <flux:sidebar.group class="grid gap-0.5">
                        <div class="px-3 py-1 text-xs font-semibold text-on-primary/40 uppercase tracking-widest">
                            จัดการระบบ
                        </div>
                        @if(auth()->user()->role === \App\Enums\UserRole::Admin)
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.dashboard')"
                                :current="request()->routeIs('admin.dashboard')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                                </x-slot>
                                แดชบอร์ดแอดมิน
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.courses.index')"
                                :current="request()->routeIs('admin.courses.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">book</span>
                                </x-slot>
                                จัดการคอร์ส
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.users.index')"
                                :current="request()->routeIs('admin.users.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">group</span>
                                </x-slot>
                                จัดการผู้ใช้
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.groups.index')"
                                :current="request()->routeIs('admin.groups.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">group_work</span>
                                </x-slot>
                                จัดการกลุ่ม
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.reports.index')"
                                :current="request()->routeIs('admin.reports.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">bar_chart</span>
                                </x-slot>
                                รายงานภาพรวม
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.reporting.course-progress')"
                                :current="request()->routeIs('admin.reporting.course-progress')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">leaderboard</span>
                                </x-slot>
                                ความก้าวหน้าตามคอร์ส
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.reporting.user-progress')"
                                :current="request()->routeIs('admin.reporting.user-progress')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">person_check</span>
                                </x-slot>
                                ความก้าวหน้าตามผู้เรียน
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.reporting.expert-turnaround')"
                                :current="request()->routeIs('admin.reporting.expert-turnaround')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">timer</span>
                                </x-slot>
                                ความเร็วผู้เชี่ยวชาญ
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.reviews.index')"
                                :current="request()->routeIs('admin.reviews.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">reviews</span>
                                </x-slot>
                                รีวิวจากผู้เรียน
                            </flux:sidebar.item>
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('admin.certificates.index')"
                                :current="request()->routeIs('admin.certificates.*')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">workspace_premium</span>
                                </x-slot>
                                จัดการเกียรติบัตร
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->role === \App\Enums\UserRole::Expert)
                            <flux:sidebar.item
                                wire:navigate
                                :href="route('expert.dashboard')"
                                :current="request()->routeIs('expert.dashboard')"
                                class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                            >
                                <x-slot name="icon">
                                    <span class="material-symbols-outlined text-[20px]">rate_review</span>
                                </x-slot>
                                ตรวจผลงาน
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Settings --}}
            <flux:sidebar.nav class="mb-2">
                <flux:sidebar.item
                    :href="$settingsRoute"
                    :current="request()->routeIs(['learn.settings.*', 'expert.settings', 'profile.edit'])"
                    wire:navigate
                    class="!text-on-primary/80 hover:!text-on-primary hover:!bg-white/10 data-[current]:!bg-primary-container data-[current]:!text-on-primary font-medium"
                >
                    <x-slot name="icon">
                        <span class="material-symbols-outlined text-[20px]">settings</span>
                    </x-slot>
                    ตั้งค่าบัญชี
                </flux:sidebar.item>
            </flux:sidebar.nav>

            {{-- User Profile --}}
            <div class="border-t border-white/10 pt-4 pb-2 px-3">
                <flux:dropdown position="top" align="start" class="w-full">
                    <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 transition-all text-left group">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-secondary-container text-on-secondary-container font-bold text-sm">
                            {{ auth()->user()->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-on-primary truncate">{{ auth()->user()->fullName() }}</p>
                            <p class="text-xs text-on-primary/50 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <span class="material-symbols-outlined text-[16px] text-on-primary/40 group-hover:text-on-primary/70">unfold_more</span>
                    </button>

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar
                                        :name="auth()->user()->fullName()"
                                        :initials="auth()->user()->initials()"
                                    />
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->fullName() }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>
                        <flux:menu.separator />
                        <flux:menu.radio.group>
                            <flux:menu.item :href="$settingsRoute" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </flux:sidebar>

        {{-- Mobile Header --}}
        <flux:header class="lg:hidden bg-primary border-b border-white/10">
            <flux:sidebar.toggle class="text-on-primary/70 hover:text-on-primary" icon="bars-2" inset="left" />

            <a href="{{ route('home') }}" class="text-on-primary font-bold font-headline tracking-tight mx-auto">
                ME-Learning
            </a>

            <flux:dropdown position="top" align="end">
                <button class="flex size-9 items-center justify-center rounded-full bg-secondary-container text-on-secondary-container font-bold text-sm">
                    {{ auth()->user()->initials() }}
                </button>
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar
                                :name="auth()->user()->fullName()"
                                :initials="auth()->user()->initials()"
                            />
                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->fullName() }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                        <flux:menu.separator />
                        <flux:menu.item :href="route('learn.settings.index')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts

        <script>
            // Dark mode not yet designed — force light after Alpine and on each Livewire navigation
            const forceLightMode = () => {
                if (window.Flux) {
                    if (typeof window.Flux.applyAppearance === 'function') {
                        window.Flux.applyAppearance('light');
                    } else {
                        window.Flux.appearance = 'light';
                    }
                }
            }

            forceLightMode();
            document.addEventListener('livewire:navigated', forceLightMode);
        </script>
    </body>
</html>
