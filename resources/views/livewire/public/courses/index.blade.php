<div>
    {{-- Header --}}
    <section class="pt-28 pb-12 px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center space-y-4">
                <h1 class="text-4xl lg:text-5xl font-extrabold font-headline text-primary tracking-tight">
                    หลักสูตรทั้งหมด
                </h1>
                <p class="text-on-surface-variant text-lg max-w-2xl mx-auto">
                    หลักสูตรออนไลน์ที่ออกแบบโดยผู้เชี่ยวชาญ เพื่อพัฒนาทักษะการติดตามและประเมินผลการศึกษาขั้นพื้นฐาน
                </p>
            </div>
        </div>
    </section>

    @if($courses->isEmpty())
        {{-- Empty State --}}
        <section class="py-20 px-8">
            <div class="max-w-7xl mx-auto text-center">
                <span class="material-symbols-outlined text-6xl text-on-surface-variant/30">school</span>
                <p class="mt-4 text-on-surface-variant text-lg">ยังไม่มีหลักสูตรที่เปิดให้ลงทะเบียนในขณะนี้</p>
            </div>
        </section>
    @else
        {{-- Course Grid --}}
        <section class="pb-24 px-8">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($courses as $course)
                    <a href="{{ route('courses.show', $course) }}" class="group bg-surface-container-lowest rounded-[2rem] overflow-hidden transition-all duration-300 hover:shadow-[0px_20px_40px_rgba(25,28,29,0.06)] flex flex-col">
                        <div class="relative h-56 overflow-hidden bg-primary/5">
                            <div class="flex items-center justify-center h-full">
                                <span class="material-symbols-outlined text-7xl text-primary/20">menu_book</span>
                            </div>
                            @if($course->reviews_avg_rating >= 4.5)
                                <div class="absolute top-4 left-4 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                                    แนะนำ
                                </div>
                            @endif
                        </div>
                        <div class="p-8 flex flex-col flex-grow space-y-4">
                            {{-- Rating --}}
                            <div class="flex items-center gap-1 text-secondary">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($course->reviews_avg_rating ?? 0))
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                    @elseif($i - 0.5 <= ($course->reviews_avg_rating ?? 0))
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star_half</span>
                                    @else
                                        <span class="material-symbols-outlined text-sm">star</span>
                                    @endif
                                @endfor
                                @if($course->reviews_avg_rating)
                                    <span class="text-on-surface-variant text-xs ml-2">({{ number_format($course->reviews_avg_rating, 1) }})</span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h3 class="text-xl font-bold font-headline text-primary leading-snug">
                                {{ $course->title }}
                            </h3>

                            {{-- Description --}}
                            <p class="text-on-surface-variant text-sm line-clamp-2">
                                {{ Str::limit($course->description, 120) }}
                            </p>

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 text-xs text-on-surface-variant pt-2">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    {{ $course->duration_hours }} ชั่วโมง
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">layers</span>
                                    {{ $course->modules->count() }} โมดูล
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">group</span>
                                    {{ $course->enrollments_count }} ผู้เรียน
                                </span>
                            </div>

                            {{-- CTA --}}
                            <div class="pt-4 mt-auto">
                                <div class="w-full py-3 bg-surface-container-low text-primary font-bold rounded-xl group-hover:bg-primary group-hover:text-on-primary transition-all text-center">
                                    ดูรายละเอียด
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
