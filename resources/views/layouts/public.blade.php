<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'ME-Learning' }} | สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-body text-on-surface antialiased">

    {{-- Top Navigation Bar --}}
    <nav class="fixed top-0 w-full z-50 glass-nav shadow-[0px_20px_40px_rgba(25,28,29,0.06)]">
        <div class="flex justify-between items-center px-8 h-20 max-w-7xl mx-auto">
            <div class="flex items-center gap-12">
                <a class="text-2xl font-bold tracking-tighter text-primary font-headline" href="{{ route('home') }}">
                    ME-Learning
                </a>
                <div class="hidden md:flex gap-8 items-center">
                    <a class="{{ request()->routeIs('home') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} transition-all duration-300 font-headline tracking-tight" href="{{ route('home') }}">
                        หน้าแรก
                    </a>
                    <a class="{{ request()->routeIs('courses.*') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} transition-all duration-300 font-headline tracking-tight" href="{{ route('courses.index') }}">
                        หลักสูตร
                    </a>
                    <a class="{{ request()->routeIs('directory') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} transition-all duration-300 font-headline tracking-tight" href="{{ route('directory') }}">
                        ทำเนียบนักติดตาม
                    </a>
                    <a class="{{ request()->routeIs('contact') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' }} transition-all duration-300 font-headline tracking-tight" href="{{ route('contact') }}">
                        ติดต่อเรา
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-xl shadow-sm hover:translate-y-[-1px] active:scale-95 transition-all">
                        แดชบอร์ด
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2.5 text-primary font-semibold hover:bg-surface-container-low rounded-xl transition-all">
                        เข้าสู่ระบบ
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-2.5 bg-secondary-container text-on-secondary-container font-bold rounded-xl shadow-sm hover:translate-y-[-1px] active:scale-95 transition-all">
                        สมัครสมาชิก
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-surface-container-low pt-20 border-t border-outline-variant/10">
        <div class="max-w-7xl mx-auto px-12">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 pb-16">
                <div class="md:col-span-4 space-y-6">
                    <a class="text-2xl font-bold font-headline text-primary tracking-tighter" href="{{ route('home') }}">
                        ME-Learning
                    </a>
                    <p class="text-on-surface-variant text-sm leading-relaxed">
                        ศูนย์กลางการเรียนรู้ออนไลน์เพื่อพัฒนาทักษะการติดตามและประเมินผล สังกัดสำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.) สพฐ.
                    </p>
                    <div class="flex gap-4">
                        <a class="w-10 h-10 rounded-full bg-surface-container-highest flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all" href="#">
                            <span class="material-symbols-outlined text-lg">public</span>
                        </a>
                        <a class="w-10 h-10 rounded-full bg-surface-container-highest flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all" href="#">
                            <span class="material-symbols-outlined text-lg">mail</span>
                        </a>
                    </div>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <h4 class="font-bold text-primary">การใช้งาน</h4>
                    <ul class="space-y-4 text-sm text-on-surface-variant">
                        <li><a class="hover:text-primary transition-colors" href="{{ route('courses.index') }}">หลักสูตรทั้งหมด</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('directory') }}">ทำเนียบนักติดตาม</a></li>
                        <li><a class="hover:text-primary transition-colors" href="{{ route('contact') }}">ติดต่อเรา</a></li>
                    </ul>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <h4 class="font-bold text-primary">นโยบาย</h4>
                    <ul class="space-y-4 text-sm text-on-surface-variant">
                        <li><a class="hover:text-primary transition-colors" href="#">นโยบายความเป็นส่วนตัว</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">เงื่อนไขการใช้งาน</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2 space-y-6">
                    <h4 class="font-bold text-primary">ติดต่อเรา</h4>
                    <p class="text-sm text-on-surface-variant leading-relaxed">
                        อาคาร สพฐ. 5 <br>
                        กระทรวงศึกษาธิการ <br>
                        กทม. 10300
                    </p>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center py-10 border-t border-outline-variant/10 text-xs text-on-surface-variant tracking-wide">
                <p>&copy; {{ date('Y') }} สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.) สพฐ. สงวนลิขสิทธิ์</p>
                <div class="flex gap-8 mt-4 md:mt-0">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">shield</span>
                        Secure Education Platform
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">language</span>
                        ภาษาไทย
                    </span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
