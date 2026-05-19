<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-10">
        <flux:heading size="xl" class="mb-2">ตั้งค่าบัญชี</flux:heading>
        <flux:subheading>จัดการข้อมูลส่วนตัว ความปลอดภัย และการแจ้งเตือนของคุณ</flux:subheading>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        {{-- Sidebar Tabs --}}
        <div class="w-full md:w-64 shrink-0">
            <nav class="flex flex-col gap-1">
                <flux:navlist>
                    <flux:navlist.item icon="user-circle" href="#profile" x-data @click.prevent="document.getElementById('profile').scrollIntoView({behavior: 'smooth'})">ข้อมูลส่วนตัว</flux:navlist.item>
                    <flux:navlist.item icon="lock-closed" href="#security" x-data @click.prevent="document.getElementById('security').scrollIntoView({behavior: 'smooth'})">ความปลอดภัย</flux:navlist.item>
                    <flux:navlist.item icon="bell" href="#notifications" x-data @click.prevent="document.getElementById('notifications').scrollIntoView({behavior: 'smooth'})">การแจ้งเตือน</flux:navlist.item>
                </flux:navlist>
            </nav>
        </div>

        {{-- Forms Content --}}
        <div class="flex-1 space-y-12">
            {{-- Profile Section --}}
            <section id="profile" class="bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm scroll-mt-24">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <flux:icon.user-circle variant="micro" />
                    </div>
                    <flux:heading size="lg">ข้อมูลส่วนตัว</flux:heading>
                </div>

                <form wire:submit="updateProfile" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <flux:select label="คำนำหน้า" wire:model="prefix" required>
                            @foreach($prefixes as $p)
                                <option value="{{ $p->value }}">{{ $p->value }}</option>
                            @endforeach
                        </flux:select>
                        <flux:input label="ชื่อ" wire:model="first_name" required />
                        <flux:input label="นามสกุล" wire:model="last_name" required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input label="เบอร์โทรศัพท์" wire:model="phone" />
                        <flux:input label="ชื่อสถานศึกษา / หน่วยงาน" wire:model="school_name" />
                    </div>

                    <flux:select label="ประสบการณ์การทำงาน" wire:model="experience" required>
                        @foreach($experiences as $exp)
                            <option value="{{ $exp->value }}">{{ $exp->label() }}</option>
                        @endforeach
                    </flux:select>

                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary">บันทึกข้อมูลส่วนตัว</flux:button>
                    </div>
                </form>
            </section>

            {{-- Security Section --}}
            <section id="security" class="bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm scroll-mt-24">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="size-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500">
                        <flux:icon.lock-closed variant="micro" />
                    </div>
                    <flux:heading size="lg">ความปลอดภัย</flux:heading>
                </div>

                <form wire:submit="updatePassword" class="space-y-6">
                    <flux:input type="password" label="รหัสผ่านปัจจุบัน" wire:model="current_password" required viewable />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input type="password" label="รหัสผ่านใหม่" wire:model="new_password" required viewable />
                        <flux:input type="password" label="ยืนยันรหัสผ่านใหม่" wire:model="new_password_confirmation" required viewable />
                    </div>

                    <div class="flex justify-end pt-4">
                        <flux:button type="submit" variant="primary" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 border-0">เปลี่ยนรหัสผ่าน</flux:button>
                    </div>
                </form>
            </section>

            {{-- Notifications Section --}}
            <section id="notifications" class="bg-white dark:bg-zinc-900 p-8 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm scroll-mt-24">
                <div class="flex items-center gap-3 mb-8 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="size-10 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <flux:icon.bell variant="micro" />
                    </div>
                    <flux:heading size="lg">การแจ้งเตือน</flux:heading>
                </div>

                <div class="space-y-8">
                    <div class="flex items-center justify-between gap-6">
                        <div>
                            <p class="font-bold text-zinc-900 dark:text-white mb-1">การแจ้งเตือนทางอีเมล</p>
                            <p class="text-sm text-zinc-500">รับข้อมูลข่าวสาร คอร์สเรียนใหม่ และเกียรติบัตรผ่านทางอีเมลของคุณ</p>
                        </div>
                        <flux:switch wire:model.live="email_notifications_enabled" wire:click="updateNotifications" />
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
