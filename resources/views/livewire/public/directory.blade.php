<div>
    {{-- Header --}}
    <section class="pt-28 pb-12 px-8">
        <div class="max-w-7xl mx-auto text-center space-y-4">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full font-semibold text-sm">
                <span class="material-symbols-outlined text-sm">military_tech</span>
                ทำเนียบผู้ผ่านการรับรอง
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold font-headline text-primary tracking-tight">
                ทำเนียบนักติดตาม
            </h1>
            <p class="text-on-surface-variant text-lg max-w-2xl mx-auto">
                รายชื่อผู้เรียนที่ผ่านหลักสูตรและได้รับเกียรติบัตรจากสำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน
            </p>
        </div>
    </section>

    {{-- Directory List --}}
    <section class="pb-24 px-8">
        <div class="max-w-7xl mx-auto">
            @if($certificates->isEmpty())
                <div class="text-center py-20 bg-surface-container-lowest rounded-2xl">
                    <span class="material-symbols-outlined text-6xl text-on-surface-variant/30">workspace_premium</span>
                    <p class="mt-4 text-on-surface-variant text-lg">ยังไม่มีผู้ผ่านการรับรองในขณะนี้</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($certificates as $cert)
                        <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold flex-shrink-0">
                                    {{ $cert->user->initials() }}
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="font-bold text-primary truncate">{{ $cert->full_name_on_cert }}</h3>
                                    <p class="text-sm text-on-surface-variant mt-1">{{ $cert->course->title }}</p>
                                    <div class="flex items-center gap-4 mt-3 text-xs text-on-surface-variant">
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">workspace_premium</span>
                                            {{ $cert->certificate_number }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">percent</span>
                                            {{ number_format($cert->final_score_pct, 1) }}%
                                        </span>
                                    </div>
                                    <div class="text-xs text-on-surface-variant/60 mt-2">
                                        ออกเมื่อ {{ $cert->issued_date->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $certificates->links() }}
                </div>
            @endif
        </div>
    </section>
</div>
