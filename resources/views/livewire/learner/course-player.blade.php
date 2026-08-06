<div class="flex h-[calc(100vh-64px)] overflow-hidden bg-zinc-950"
     x-data="youtubePlayer({
        videoId: '{{ $activeContent->content_type === \App\Enums\ContentType::Video ? $this->extractYoutubeId($activeContent->file_url) : '' }}',
        contentId: {{ $activeContent->id }},
        initialPosition: {{ $activeContent->views->where('user_id', auth()->id())->first()?->last_position_sec ?? 0 }}
     })">

    {{-- Main Content Area --}}
    <div class="flex-1 flex flex-col relative">
        {{-- Content Player Container --}}
        <div class="flex-1 flex items-center justify-center bg-black relative">
            @switch($activeContent->content_type)
                @case(\App\Enums\ContentType::Video)
                    @if($this->extractYoutubeId($activeContent->file_url))
                        <div id="player" class="w-full h-full"></div>
                    @else
                        <video
                            controls
                            class="w-full h-full"
                            src="{{ $activeContent->file_url }}"
                            @timeupdate.throttle.5000ms="$wire.updateProgress(Math.floor($el.currentTime), Math.floor($el.currentTime), false)"
                            @ended="$wire.updateProgress(Math.floor($el.duration), Math.floor($el.duration), true)"
                        ></video>
                    @endif
                    @break

                @case(\App\Enums\ContentType::Document)
                    <iframe src="{{ $activeContent->file_url }}" class="w-full h-full border-0 bg-white"></iframe>
                    @break

                @case(\App\Enums\ContentType::Link)
                    <div class="flex flex-col items-center justify-center text-center p-10 max-w-md">
                        <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6">
                            <flux:icon.link variant="outline" class="size-8" />
                        </div>
                        <flux:heading size="xl" class="text-white mb-2">{{ $activeContent->title }}</flux:heading>
                        <flux:subheading class="text-zinc-400 mb-6">เนื้อหานี้เป็นลิงก์ภายนอก คลิกด้านล่างเพื่อเปิด</flux:subheading>
                        <flux:button variant="primary" href="{{ $activeContent->file_url }}" target="_blank">
                            <flux:icon.arrow-top-right-on-square variant="mini" class="mr-2" />
                            เปิดลิงก์
                        </flux:button>
                    </div>
                    @break

                @case(\App\Enums\ContentType::Test)
                    @php
                        $assessment = $activeContent->assessment;
                        $userAttempts = $assessment?->attempts->where('user_id', auth()->id()) ?? collect();
                        $latestAttempt = $userAttempts->sortByDesc('attempt_number')->first();
                        $questionCount = $assessment?->questions->count() ?? 0;
                        $maxAttempts = $assessment?->max_attempts ?? 0;
                        $attemptsUsed = $userAttempts->count();

                        $statusMeta = match ($latestAttempt?->status) {
                            \App\Enums\TestAttemptStatus::Passed => ['label' => 'ผ่านแล้ว', 'color' => 'text-green-400', 'cta' => 'ดูผลคะแนน'],
                            \App\Enums\TestAttemptStatus::Failed => ['label' => 'ไม่ผ่าน', 'color' => 'text-red-400', 'cta' => 'ทำใหม่อีกครั้ง'],
                            \App\Enums\TestAttemptStatus::PendingReview => ['label' => 'รอผู้เชี่ยวชาญตรวจ', 'color' => 'text-amber-400', 'cta' => 'ดูสถานะ'],
                            \App\Enums\TestAttemptStatus::RevisionNeeded => ['label' => 'ต้องแก้ไข', 'color' => 'text-amber-400', 'cta' => 'แก้ไขคำตอบ'],
                            \App\Enums\TestAttemptStatus::InProgress => ['label' => 'ทำค้างไว้', 'color' => 'text-primary', 'cta' => 'ทำต่อ'],
                            default => ['label' => 'ยังไม่ได้เริ่ม', 'color' => 'text-zinc-400', 'cta' => 'เริ่มทำแบบทดสอบ'],
                        };
                    @endphp
                    <div class="flex flex-col items-center text-center p-10 max-w-md">
                        <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6">
                            <flux:icon.clipboard-document-check variant="outline" class="size-8" />
                        </div>
                        <flux:heading size="xl" class="text-white mb-2">{{ $assessment?->title ?? $activeContent->title }}</flux:heading>
                        <flux:text class="{{ $statusMeta['color'] }} font-semibold mb-6">{{ $statusMeta['label'] }}</flux:text>

                        <div class="grid grid-cols-3 gap-3 w-full mb-6 text-left">
                            <div class="bg-zinc-900 rounded-xl p-3">
                                <flux:text class="text-zinc-500 text-xs block mb-1">จำนวนคำถาม</flux:text>
                                <flux:heading size="lg" class="text-white">{{ $questionCount }}</flux:heading>
                            </div>
                            <div class="bg-zinc-900 rounded-xl p-3">
                                <flux:text class="text-zinc-500 text-xs block mb-1">เกณฑ์ผ่าน</flux:text>
                                <flux:heading size="lg" class="text-white">{{ $assessment?->passing_score_pct }}%</flux:heading>
                            </div>
                            <div class="bg-zinc-900 rounded-xl p-3">
                                <flux:text class="text-zinc-500 text-xs block mb-1">เวลาทำ</flux:text>
                                <flux:heading size="lg" class="text-white">{{ $assessment?->is_timed ? $assessment->time_limit_minutes.' น.' : 'ไม่จำกัด' }}</flux:heading>
                            </div>
                        </div>

                        @if($assessment)
                            <flux:text class="text-zinc-500 mb-6">
                                ทำไปแล้ว {{ $attemptsUsed }} ครั้ง{{ $maxAttempts > 0 ? ' จาก '.$maxAttempts.' ครั้ง' : '' }}
                            </flux:text>

                            <flux:button variant="primary" href="{{ route('learn.assessments.show', $assessment) }}" wire:navigate>
                                {{ $statusMeta['cta'] }}
                            </flux:button>
                        @else
                            <flux:text class="text-zinc-500">ยังไม่มีแบบทดสอบเชื่อมโยงกับเนื้อหานี้</flux:text>
                        @endif
                    </div>
                    @break
            @endswitch

            {{-- Sequential Gating Overlay --}}
            <template x-if="!isAccessible">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6 z-50">
                    <div class="size-20 bg-zinc-800 rounded-full flex items-center justify-center text-zinc-500 mb-6">
                        <flux:icon.lock-closed variant="micro" class="size-10" />
                    </div>
                    <flux:heading size="xl" class="text-white mb-2">เนื้อหานี้ยังไม่ถูกปลดล็อค</flux:heading>
                    <flux:subheading class="text-zinc-400">กรุณาเรียนเนื้อหาก่อนหน้าให้จบเพื่อดำเนินการต่อ</flux:subheading>
                </div>
            </template>
        </div>

        {{-- Content Info Bar --}}
        <div class="h-20 bg-zinc-900 border-t border-zinc-800 px-8 flex items-center justify-between shrink-0">
            <div>
                <flux:heading size="lg" class="text-white">{{ $activeContent->title }}</flux:heading>
                <flux:text class="text-zinc-500">{{ $course->title }} • {{ $module->title }}</flux:text>
            </div>
            <div class="flex items-center gap-4">
                @if($activeContent->content_type === \App\Enums\ContentType::Document)
                    <flux:button variant="ghost" class="text-zinc-400 hover:text-white" href="{{ $activeContent->file_url }}" target="_blank">
                        <flux:icon.arrow-top-right-on-square variant="mini" class="mr-2" />
                        เปิดในแท็บใหม่
                    </flux:button>
                @endif

                @if(in_array($activeContent->content_type, [\App\Enums\ContentType::Document, \App\Enums\ContentType::Link]))
                    @php $activeIsCompleted = $activeContent->isCompletedFor(auth()->user()); @endphp
                    @if($activeIsCompleted)
                        <flux:button variant="primary" disabled class="bg-green-600 border-0">
                            <flux:icon.check variant="mini" class="mr-2" />
                            เรียนจบแล้ว
                        </flux:button>
                    @else
                        <flux:button variant="primary" wire:click="markComplete({{ $activeContent->id }})">
                            <flux:icon.check variant="mini" class="mr-2" />
                            ทำเครื่องหมายว่าเรียนจบแล้ว
                        </flux:button>
                    @endif
                @endif

                <flux:button variant="ghost" class="text-zinc-400 hover:text-white" href="{{ route('learn.courses.show', $course) }}" wire:navigate>
                    <flux:icon.arrow-left variant="mini" class="mr-2" />
                    กลับไปหน้ารวม
                </flux:button>

                @if($activeContent->content_type === \App\Enums\ContentType::Video)
                    <flux:button variant="primary" x-show="isCompleted" class="bg-green-600 hover:bg-green-500 border-0">
                        <flux:icon.check variant="mini" class="mr-2" />
                        เรียนจบแล้ว
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tree Navigation Sidebar --}}
    @include('livewire.learner._course-tree')

    {{-- YouTube API Integration --}}
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('youtubePlayer', (config) => ({
                player: null,
                videoId: config.videoId,
                contentId: config.contentId,
                initialPosition: config.initialPosition,
                watchDuration: 0,
                lastUpdate: 0,
                isCompleted: false,
                isAccessible: true,
                timer: null,

                init() {
                    window.onYouTubeIframeAPIReady = () => {
                        this.loadPlayer();
                    };

                    if (this.videoId && window.YT && window.YT.Player) {
                        this.loadPlayer();
                    }

                    // Handle content switching
                    this.$watch('videoId', (val) => {
                        if (this.player && val) {
                            this.player.loadVideoById(val);
                            this.watchDuration = 0;
                            this.lastUpdate = 0;
                            this.isCompleted = false;
                        }
                    });
                },

                loadPlayer() {
                    if (!this.videoId || !document.getElementById('player')) return;

                    this.player = new YT.Player('player', {
                        height: '100%',
                        width: '100%',
                        videoId: this.videoId,
                        playerVars: {
                            'autoplay': 0,
                            'controls': 1,
                            'modestbranding': 1,
                            'rel': 0,
                            'start': this.initialPosition
                        },
                        events: {
                            'onStateChange': (event) => this.onPlayerStateChange(event),
                            'onReady': (event) => {
                                if (this.initialPosition > 0) {
                                    event.target.seekTo(this.initialPosition);
                                }
                            }
                        }
                    });
                },

                onPlayerStateChange(event) {
                    if (event.data == YT.PlayerState.PLAYING) {
                        this.startTracking();
                    } else {
                        this.stopTracking();
                        if (event.data == YT.PlayerState.ENDED) {
                            this.isCompleted = true;
                            this.saveProgress(true);
                        }
                    }
                },

                startTracking() {
                    if (this.timer) return;
                    this.timer = setInterval(() => {
                        this.watchDuration += 1;
                        const currentTime = Math.floor(this.player.getCurrentTime());

                        // Sync with backend every 10 seconds
                        if (this.watchDuration % 10 === 0) {
                            this.saveProgress(false);
                        }
                    }, 1000);
                },

                stopTracking() {
                    clearInterval(this.timer);
                    this.timer = null;
                },

                saveProgress(completed) {
                    const currentTime = Math.floor(this.player.getCurrentTime());
                    @this.updateProgress(this.watchDuration, currentTime, completed);
                }
            }));
        });
    </script>
</div>
