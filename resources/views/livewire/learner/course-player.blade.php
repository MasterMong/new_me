<div class="flex h-[calc(100vh-64px)] overflow-hidden bg-zinc-950" 
     x-data="youtubePlayer({ 
        videoId: '{{ $this->extractYoutubeId($activeContent->file_url) }}',
        contentId: {{ $activeContent->id }},
        initialPosition: {{ $activeContent->views->where('user_id', auth()->id())->first()?->last_position_sec ?? 0 }}
     })">
    
    {{-- Main Content Area --}}
    <div class="flex-1 flex flex-col relative">
        {{-- Video Player Container --}}
        <div class="flex-1 flex items-center justify-center bg-black relative">
            @if($activeContent->content_type->value === 'video')
                <div id="player" class="w-full h-full"></div>
            @elseif($activeContent->content_type->value === 'document')
                <iframe src="{{ $activeContent->file_url }}" class="w-full h-full border-0"></iframe>
            @else
                <div class="text-white">ไม่รองรับรูปแบบเนื้อหานี้</div>
            @endif

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
                <flux:button variant="ghost" class="text-zinc-400 hover:text-white" href="{{ route('learn.courses.show', $course) }}" wire:navigate>
                    <flux:icon.arrow-left variant="mini" class="mr-2" />
                    กลับไปหน้ารวม
                </flux:button>
                <flux:button variant="primary" x-show="isCompleted" class="bg-green-600 hover:bg-green-500 border-0">
                    <flux:icon.check variant="mini" class="mr-2" />
                    เรียนจบแล้ว
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Sidebar Content List --}}
    <div class="w-80 bg-zinc-900 border-l border-zinc-800 flex flex-col shrink-0 overflow-hidden">
        <div class="p-6 border-b border-zinc-800 shrink-0">
            <flux:heading class="text-white mb-1">เนื้อหาในโมดูล</flux:heading>
            <flux:subheading class="text-zinc-500">{{ $contents->count() }} บทเรียน</flux:subheading>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar">
            @foreach($contents as $content)
                <button 
                    wire:click="selectContent({{ $content->id }})"
                    @class([
                        'w-full p-4 flex items-start gap-4 transition-all text-left border-b border-zinc-800/50',
                        'bg-primary/10 border-l-4 border-l-primary' => $activeContent->id === $content->id,
                        'hover:bg-zinc-800' => $activeContent->id !== $content->id && $content->is_accessible,
                        'opacity-50 cursor-not-allowed' => !$content->is_accessible,
                    ])
                    {{ !$content->is_accessible ? 'disabled' : '' }}
                >
                    <div @class([
                        'size-8 rounded-full flex items-center justify-center shrink-0 text-sm font-bold',
                        'bg-green-500 text-white' => $content->is_completed,
                        'bg-primary text-white' => !$content->is_completed && $content->is_accessible,
                        'bg-zinc-800 text-zinc-600' => !$content->is_accessible,
                    ])>
                        @if($content->is_completed)
                            <flux:icon.check variant="micro" />
                        @elseif(!$content->is_accessible)
                            <flux:icon.lock-closed variant="micro" />
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p @class([
                            'text-sm font-medium truncate',
                            'text-white' => $activeContent->id === $content->id,
                            'text-zinc-300' => $activeContent->id !== $content->id && $content->is_accessible,
                            'text-zinc-600' => !$content->is_accessible,
                        ])>
                            {{ $content->title }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-zinc-500 flex items-center gap-1">
                                <flux:icon.play variant="micro" class="size-3" />
                                {{ $content->duration_minutes }} นาที
                            </span>
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

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
                    
                    if (window.YT && window.YT.Player) {
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

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #27272a;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #3f3f46;
        }
    </style>
</div>
