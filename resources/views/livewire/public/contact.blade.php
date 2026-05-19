<div class="min-h-screen bg-surface">
    {{-- Hero Section --}}
    <div class="bg-surface-container-high pt-32 pb-20 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <flux:badge color="blue" size="sm" class="mb-4">CONTACT US</flux:badge>
            <h1 class="text-4xl md:text-5xl font-black font-headline text-on-surface tracking-tight mb-6">
                ติดต่อสอบถาม <br/>
                <span class="text-primary">GET IN TOUCH</span>
            </h1>
            <p class="text-lg text-on-surface/50 leading-relaxed">
                หากคุณมีข้อสงสัยเกี่ยวกับหลักสูตร การลงทะเบียน หรือต้องการความช่วยเหลือด้านเทคนิค <br class="hidden md:block"/>
                ทีมงานของเราพร้อมให้บริการคุณอย่างเต็มความสามารถ
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
            {{-- Contact Info --}}
            <div class="lg:col-span-2 space-y-10">
                <div>
                    <h3 class="text-xl font-bold font-headline text-on-surface mb-6">ข้อมูลการติดต่อ</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-5">
                            <div class="size-12 rounded-2xl bg-white shadow-sm border border-outline-variant/30 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined">location_on</span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface">ที่อยู่โครงการ</p>
                                <p class="text-sm text-on-surface/50 mt-1 leading-relaxed">
                                    เลขที่ 123 อาคารศูนย์เรียนรู้ ชั้น 4 <br/>
                                    ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง <br/>
                                    เขตหลักสี่ กรุงเทพมหานคร 10210
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-5">
                            <div class="size-12 rounded-2xl bg-white shadow-sm border border-outline-variant/30 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface">เบอร์โทรศัพท์</p>
                                <p class="text-sm text-on-surface/50 mt-1">02-123-4567, 02-987-6543</p>
                                <p class="text-xs text-on-surface/30 mt-0.5">จันทร์ - ศุกร์ | 08:30 - 16:30 น.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-5">
                            <div class="size-12 rounded-2xl bg-white shadow-sm border border-outline-variant/30 flex items-center justify-center text-primary shrink-0">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface">อีเมล</p>
                                <p class="text-sm text-on-surface/50 mt-1">support@me-learning.go.th</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-primary rounded-[2rem] text-white space-y-4">
                    <h4 class="font-bold font-headline text-xl">สมัครรับข่าวสาร</h4>
                    <p class="text-sm text-white/70">รับข้อมูลเกี่ยวกับหลักสูตรใหม่และข่าวประชาสัมพันธ์ผ่านทางอีเมลของคุณ</p>
                    <div class="flex gap-2">
                        <flux:input placeholder="อีเมลของคุณ..." class="!bg-white/10 !border-0 !text-white placeholder:text-white/40" />
                        <flux:button variant="primary" class="!bg-white !text-primary border-0">ติดตาม</flux:button>
                    </div>
                </div>
            </div>

            {{-- Contact Form --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-[2.5rem] border border-outline-variant/30 p-10 shadow-xl shadow-primary/5">
                    @if ($sent)
                        <div class="py-12 text-center space-y-4">
                            <div class="size-20 bg-green-50 rounded-full flex items-center justify-center text-green-500 mx-auto mb-6">
                                <span class="material-symbols-outlined text-[40px]">check_circle</span>
                            </div>
                            <h3 class="text-2xl font-bold text-on-surface">ส่งข้อความสำเร็จ!</h3>
                            <p class="text-on-surface/50">เราได้รับข้อความของคุณแล้ว และจะติดต่อกลับโดยเร็วที่สุด</p>
                            <flux:button variant="ghost" wire:click="$set('sent', false)" class="mt-6">ส่งข้อความใหม่</flux:button>
                        </div>
                    @else
                        <form wire:submit.prevent="sendMessage" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <flux:field>
                                    <flux:label>ชื่อ-นามสกุล <span class="text-error">*</span></flux:label>
                                    <flux:input wire:model="name" placeholder="ระบุชื่อของคุณ..." class="!rounded-2xl" />
                                    <flux:error name="name" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>อีเมลติดต่อ <span class="text-error">*</span></flux:label>
                                    <flux:input type="email" wire:model="email" placeholder="email@example.com" class="!rounded-2xl" />
                                    <flux:error name="email" />
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>หัวข้อติดต่อ <span class="text-error">*</span></flux:label>
                                <flux:select wire:model="subject" placeholder="เลือกหัวข้อที่ต้องการติดต่อ..." class="!rounded-2xl">
                                    <flux:select.option value="general">สอบถามทั่วไป</flux:select.option>
                                    <flux:select.option value="technical">ปัญหาด้านเทคนิค / การใช้งาน</flux:select.option>
                                    <flux:select.option value="certificate">ปัญหาเกี่ยวกับใบประกาศนียบัตร</flux:select.option>
                                    <flux:select.option value="partnership">ติดต่อร่วมงาน / วิทยากร</flux:select.option>
                                </flux:select>
                                <flux:error name="subject" />
                            </flux:field>

                            <flux:field>
                                <flux:label>ข้อความ <span class="text-error">*</span></flux:label>
                                <flux:textarea wire:model="message" placeholder="ระบุรายละเอียดที่คุณต้องการสอบถาม..." rows="6" class="!rounded-2xl" />
                                <flux:error name="message" />
                            </flux:field>

                            <div class="pt-4">
                                <flux:button type="submit" variant="primary" class="w-full !rounded-2xl h-14 font-bold tracking-wide">
                                    ส่งข้อความ
                                    <flux:icon name="paper-airplane" variant="micro" class="ms-2" />
                                </flux:button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
