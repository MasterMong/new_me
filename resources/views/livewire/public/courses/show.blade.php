<div>
    {{-- Hero Section --}}
    <section class="pt-28 pb-12 px-8 bg-primary/5">
        <div class="max-w-7xl mx-auto">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary transition-colors mb-6 text-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                กลับหน้าหลักสูตร
            </a>

            <div class="flex flex-col lg:flex-row gap-12">
                <div class="lg:w-2/3 space-y-6">
                    @if($course->thumbnail_url)
                        <div class="w-full aspect-[21/9] rounded-3xl overflow-hidden mb-8 border border-outline-variant/10 shadow-lg">
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <h1 class="text-3xl lg:text-4xl font-extrabold font-headline text-primary tracking-tight leading-snug">
                        {{ $course->title }}
                    </h1>
                    <p class="text-on-surface-variant text-lg leading-relaxed">
                        {{ $course->description }}
                    </p>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-surface-container-lowest rounded-2xl p-6 space-y-5 shadow-sm">
                        {{-- Stats --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-symbols-outlined text-primary">schedule</span>
                                <span class="text-on-surface-variant">{{ $course->duration_hours }} ชั่วโมง</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-symbols-outlined text-primary">layers</span>
                                <span class="text-on-surface-variant">{{ $course->modules->count() }} โมดูล</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-symbols-outlined text-primary">group</span>
                                <span class="text-on-surface-variant">{{ $enrollmentCount }} ผู้เรียน</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-symbols-outlined text-primary">verified</span>
                                <span class="text-on-surface-variant">คะแนนผ่าน {{ $course->passing_score_pct }}%</span>
                            </div>
                            @if($avgRating)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="flex items-center gap-0.5 text-secondary">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' {{ $i <= round($avgRating) ? '1' : '0' }};">star</span>
                                        @endfor
                                    </div>
                                    <span class="text-on-surface-variant">{{ number_format($avgRating, 1) }}/5.0</span>
                                </div>
                            @endif
                        </div>

                        @auth
                            @if($isEnrolled)
                                <a href="{{ route('learn.courses.show', $course) }}" wire:navigate class="block w-full py-3 bg-primary text-on-primary font-bold rounded-xl text-center hover:opacity-90 transition-all">
                                    เข้าสู่บทเรียน
                                </a>
                            @else
                                <button wire:click="enroll" class="block w-full py-3 bg-primary text-on-primary font-bold rounded-xl text-center hover:opacity-90 transition-all">
                                    ลงทะเบียนเรียน
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block w-full py-3 bg-primary text-on-primary font-bold rounded-xl text-center hover:opacity-90 transition-all">
                                เข้าสู่ระบบเพื่อเรียน
                            </a>
                        @endauth
                    </div>

                    {{-- Course Images Gallery --}}
                    @if($course->images->isNotEmpty())
                        <div class="mt-6 space-y-3">
                            <h3 class="text-sm font-bold text-primary px-1">รูปภาพหลักสูตร</h3>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($course->images as $image)
                                    <div class="aspect-square rounded-xl overflow-hidden border border-outline-variant/10 bg-surface">
                                        <img src="{{ $image->image_url }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Course Content --}}
    <section class="py-16 px-8">
        <div class="max-w-7xl mx-auto">
            <div class="lg:w-2/3 space-y-12">
                {{-- Modules --}}
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold font-headline text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">menu_book</span>
                        เนื้อหาหลักสูตร
                    </h2>
                    <div class="space-y-3">
                        @foreach($course->modules as $module)
                            <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10">
                                <div class="flex items-start gap-4">
                                    @if($module->thumbnail_url)
                                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 border border-outline-variant/10">
                                            <img src="{{ $module->thumbnail_url }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold font-headline flex-shrink-0">
                                            {{ $module->module_number }}
                                        </div>
                                    @endif
                                    <div class="flex-grow">
                                        <h3 class="font-bold text-primary">{{ $module->title }}</h3>
                                        @if($module->description)
                                            <p class="text-sm text-on-surface-variant mt-1">{{ $module->description }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-3 mt-3">
                                            @foreach($module->contents as $content)
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-low rounded-lg text-xs text-on-surface-variant">
                                                    @if($content->content_type === 'video')
                                                        <span class="material-symbols-outlined text-sm">play_circle</span>
                                                    @elseif($content->content_type === 'document')
                                                        <span class="material-symbols-outlined text-sm">description</span>
                                                    @else
                                                        <span class="material-symbols-outlined text-sm">link</span>
                                                    @endif
                                                    {{ $content->title }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Reviews --}}
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold font-headline text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">reviews</span>
                        รีวิวจากผู้เรียน
                        @if($course->reviews->count() > 0)
                            <span class="text-sm font-normal text-on-surface-variant">({{ $course->reviews->count() }} รีวิว)</span>
                        @endif
                    </h2>

                    @if($course->reviews->isEmpty())
                        <div class="text-center py-12 bg-surface-container-lowest rounded-2xl">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/30">rate_review</span>
                            <p class="mt-2 text-on-surface-variant">ยังไม่มีรีวิวจากผู้เรียน</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($course->reviews as $review)
                                <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                            {{ $review->user->initials() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm">{{ $review->user->fullName() }}</p>
                                            <div class="flex items-center gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="material-symbols-outlined text-xs {{ $i <= $review->rating ? 'text-secondary' : 'text-on-surface-variant/30' }}" style="font-variation-settings: 'FILL' {{ $i <= $review->rating ? '1' : '0' }};">star</span>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    @if($review->comment)
                                        <p class="text-sm text-on-surface-variant leading-relaxed">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
