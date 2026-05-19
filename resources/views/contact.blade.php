<x-layouts::public title="ติดต่อเรา">

    {{-- Header --}}
    <section class="pt-28 pb-12 px-8">
        <div class="max-w-7xl mx-auto text-center space-y-4">
            <h1 class="text-4xl lg:text-5xl font-extrabold font-headline text-primary tracking-tight">
                ติดต่อเรา
            </h1>
            <p class="text-on-surface-variant text-lg max-w-2xl mx-auto">
                สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.) สพฐ.
            </p>
        </div>
    </section>

    {{-- Contact Info --}}
    <section class="pb-24 px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12">

            {{-- Info Cards --}}
            <div class="space-y-6">
                <div class="bg-surface-container-lowest rounded-2xl p-8 border border-outline-variant/10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined">location_on</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-primary mb-1">ที่อยู่</h3>
                            <p class="text-on-surface-variant text-sm leading-relaxed">
                                สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.)<br>
                                สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน (สพฐ.)<br>
                                อาคาร สพฐ. 5 กระทรวงศึกษาธิการ<br>
                                ถนนศรีอยุธยา แขวงทุ่งพญาไท เขตราชเทวี<br>
                                กรุงเทพมหานคร 10400
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-2xl p-8 border border-outline-variant/10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined">phone</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-primary mb-1">โทรศัพท์</h3>
                            <p class="text-on-surface-variant text-sm">02-288-6000</p>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-2xl p-8 border border-outline-variant/10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-primary mb-1">อีเมล</h3>
                            <p class="text-on-surface-variant text-sm">me-learning@obec.go.th</p>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-2xl p-8 border border-outline-variant/10">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-primary mb-1">เวลาทำการ</h3>
                            <p class="text-on-surface-variant text-sm">
                                วันจันทร์ – ศุกร์<br>
                                08:30 – 16:30 น.<br>
                                (หยุด วันเสาร์-อาทิตย์ และวันนักขัตฤกษ์)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Map Placeholder --}}
            <div class="bg-surface-container-lowest rounded-2xl overflow-hidden border border-outline-variant/10">
                <div class="h-full min-h-[400px] flex items-center justify-center bg-primary/5">
                    <div class="text-center space-y-4">
                        <span class="material-symbols-outlined text-6xl text-primary/20">map</span>
                        <p class="text-on-surface-variant">แผนที่สำนักงาน</p>
                        <p class="text-xs text-on-surface-variant/60">อาคาร สพฐ. 5 กระทรวงศึกษาธิการ</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

</x-layouts::public>
