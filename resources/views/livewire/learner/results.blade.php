<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-10">
        <flux:heading size="xl" class="mb-2">ผลการเรียนรู้</flux:heading>
        <flux:subheading>สรุปผลคะแนนแบบทดสอบและสถานะการเรียนของทุกหลักสูตร</flux:subheading>
    </div>

    @if($enrollments->isEmpty())
        <div class="text-center py-16 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
            <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">school</span>
            </div>
            <flux:heading size="lg" class="mb-2">ยังไม่มีข้อมูลผลการเรียนรู้</flux:heading>
            <flux:subheading>คุณยังไม่ได้ลงทะเบียนหรือทำแบบทดสอบในหลักสูตรใดๆ</flux:subheading>
            <flux:button href="{{ route('courses.index') }}" wire:navigate variant="primary" class="mt-6">ค้นหาหลักสูตร</flux:button>
        </div>
    @else
        <div class="space-y-8">
            @foreach($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    // Constrained to module_id = null: every module also has its own
                    // pre_test/post_test now, so an unscoped firstWhere() would be
                    // ambiguous between those and the course-wide one.
                    $courseWideAssessments = $course->assessments->whereNull('module_id');
                    $preTest = $courseWideAssessments->firstWhere('type', \App\Enums\AssessmentType::PreTest);
                    $postTest = $courseWideAssessments->firstWhere('type', \App\Enums\AssessmentType::PostTest);
                    
                    $preTestScore = $preTest ? $preTest->attempts->max('score_pct') : null;
                    $postTestScore = $postTest ? $postTest->attempts->max('score_pct') : null;
                    
                    $isPassed = $postTestScore !== null && $postTestScore >= ($postTest->passing_score_pct ?? 60);
                @endphp

                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                    {{-- Header --}}
                    <div class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <flux:heading size="lg" class="text-primary">{{ $course->title }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 mt-1">ลงทะเบียนเมื่อ: {{ $enrollment->enrolled_at->format('d/m/Y') }}</flux:text>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:badge color="{{ $isPassed ? 'green' : ($postTestScore !== null ? 'red' : 'zinc') }}">
                                {{ $isPassed ? 'ผ่านเกณฑ์' : ($postTestScore !== null ? 'ไม่ผ่านเกณฑ์' : 'กำลังเรียน') }}
                            </flux:badge>
                            <flux:button wire:click="downloadPdf({{ $enrollment->id }})" variant="subtle" size="sm" icon="arrow-down-tray">
                                ดาวน์โหลดรายงาน (PDF)
                            </flux:button>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-zinc-500 uppercase bg-zinc-50 dark:bg-zinc-800/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">รายการประเมิน</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">คะแนนที่ได้ (%)</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">เกณฑ์ผ่าน (%)</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                {{-- Pre-Test --}}
                                @if($preTest)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">แบบทดสอบก่อนเรียน (Pre-test)</td>
                                        <td class="px-6 py-4 text-center font-semibold {{ $preTestScore !== null ? 'text-primary' : 'text-zinc-400' }}">
                                            {{ $preTestScore !== null ? $preTestScore . '%' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-zinc-500">-</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($preTestScore !== null)
                                                <flux:badge color="zinc" size="sm">ทดสอบแล้ว</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm" class="opacity-50">ยังไม่ทดสอบ</flux:badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                                {{-- Modules --}}
                                @foreach($course->modules as $module)
                                    @php
                                        $moduleTest = $module->assessments->firstWhere('type', \App\Enums\AssessmentType::PostTest)
                                            ?? $module->assessments->firstWhere('type', \App\Enums\AssessmentType::ModuleTest);
                                        $moduleScore = $moduleTest ? $moduleTest->attempts->max('score_pct') : null;
                                        $modulePassed = $moduleTest ? ($moduleScore !== null && $moduleScore >= ($moduleTest->passing_score_pct ?? 60)) : true;
                                    @endphp
                                    @if($moduleTest)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white pl-10 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-[16px] text-zinc-400">subdirectory_arrow_right</span>
                                                โมดูล {{ $module->module_number }}: {{ $module->title }}
                                            </td>
                                            <td class="px-6 py-4 text-center font-semibold {{ $moduleScore !== null ? 'text-primary' : 'text-zinc-400' }}">
                                                {{ $moduleScore !== null ? $moduleScore . '%' : '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-center text-zinc-500">{{ $moduleTest->passing_score_pct ?? 60 }}%</td>
                                            <td class="px-6 py-4 text-center">
                                                @if($moduleScore !== null)
                                                    <flux:badge color="{{ $modulePassed ? 'green' : 'red' }}" size="sm">
                                                        {{ $modulePassed ? 'ผ่าน' : 'ไม่ผ่าน' }}
                                                    </flux:badge>
                                                @else
                                                    <flux:badge color="zinc" size="sm" class="opacity-50">ยังไม่ทดสอบ</flux:badge>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                {{-- Post-Test --}}
                                @if($postTest)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors bg-primary/5">
                                        <td class="px-6 py-4 font-bold text-primary">แบบทดสอบหลังเรียน (Post-test)</td>
                                        <td class="px-6 py-4 text-center font-bold {{ $postTestScore !== null ? 'text-primary' : 'text-zinc-400' }}">
                                            {{ $postTestScore !== null ? $postTestScore . '%' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-semibold text-zinc-700">{{ $postTest->passing_score_pct ?? 60 }}%</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($postTestScore !== null)
                                                <flux:badge color="{{ $isPassed ? 'green' : 'red' }}" size="sm">
                                                    {{ $isPassed ? 'ผ่านเกณฑ์' : 'ไม่ผ่านเกณฑ์' }}
                                                </flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm" class="opacity-50">ยังไม่ทดสอบ</flux:badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
