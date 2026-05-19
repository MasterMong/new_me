<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ME-Learning | สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน</title>
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
                    <a class="text-primary font-bold border-b-2 border-primary pb-1 font-headline tracking-tight" href="{{ route('home') }}">
                        หน้าแรก
                    </a>
                    <a class="text-on-surface-variant hover:text-primary transition-all duration-300 font-headline tracking-tight" href="#">
                        หลักสูตร
                    </a>
                    <a class="text-on-surface-variant hover:text-primary transition-all duration-300 font-headline tracking-tight" href="#">
                        รายงาน
                    </a>
                    <a class="text-on-surface-variant hover:text-primary transition-all duration-300 font-headline tracking-tight" href="#">
                        คลังความรู้
                    </a>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-2.5 text-primary font-semibold hover:bg-surface-container-low rounded-xl transition-all">
                    เข้าสู่ระบบ
                </a>
                <a href="{{ route('register') }}" class="px-6 py-2.5 bg-secondary-container text-on-secondary-container font-bold rounded-xl shadow-sm hover:translate-y-[-1px] active:scale-95 transition-all">
                    สมัครสมาชิก
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <header class="pt-32 pb-24 px-8 overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-16">
            <div class="lg:w-3/5 space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full font-semibold text-sm">
                    <span class="material-symbols-outlined text-sm">verified</span>
                    สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.)
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold font-headline text-primary tracking-tight leading-[1.1]">
                    พัฒนาศักยภาพนักติดตาม <br>
                    <span class="text-secondary">ประเมินผลระดับมืออาชีพ</span>
                </h1>
                <p class="text-xl text-on-surface-variant max-w-xl leading-relaxed">
                    สู่การพลิกโฉมคุณภาพการศึกษาไทย กับหลักสูตรออนไลน์ที่ได้มาตรฐาน ออกแบบโดยผู้เชี่ยวชาญเพื่อบุคลากรทางการศึกษาทั่วประเทศ
                </p>
                <div class="flex flex-wrap gap-4 pt-4">
                    <a href="#courses" class="px-8 py-4 bg-primary text-on-primary rounded-xl font-bold text-lg flex items-center gap-2 shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                        สำรวจหลักสูตร
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                    <a href="{{ route('register') }}" class="px-8 py-4 bg-surface-container-highest text-on-surface rounded-xl font-bold text-lg hover:bg-surface-container-high transition-all">
                        เริ่มต้นใช้งาน
                    </a>
                </div>
            </div>
            <div class="lg:w-2/5 relative">
                <div class="relative z-10 rounded-[2rem] overflow-hidden shadow-2xl">
                    <img
                        class="w-full h-[500px] object-cover transition-transform duration-500 group-hover:scale-110"
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEdBQLrX-xzGzLiG4GP_93KQfkCQDM6eEFz_2YTxWu79HyNpRyhMDBD_k9wuFi-EHaHGW5Mqg6sb9N2CTGPUnVnKw7ThQXbC5kW35_tbRo6PlilrW6t-e1zQY0JbsvSJY8jCg0KrQcR6Cqugg2a3S2PJyb7R-8OJ3dbmJwYRNS7VMqRQzFOEzJYArStskHutOkN-wyQk49hgFd0Es-QH87-NsY5zGSh8YSC_KfOSspuon_15gZP6w8k33GQw5sS90aj3DV5jPu52UJ"
                        alt="Modern classroom with professional educators learning"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/40 to-transparent"></div>
                </div>
                <div class="absolute -top-6 -right-6 w-32 h-32 bg-secondary-container rounded-3xl -z-10 rotate-12"></div>
                <div class="absolute -bottom-8 -left-8 w-48 h-48 bg-primary-container rounded-full -z-10 opacity-20"></div>
            </div>
        </div>
    </header>

    {{-- Statistics Section --}}
    <section class="py-12 px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-container-low p-8 rounded-3xl flex flex-col items-center justify-center text-center space-y-2">
                <span class="text-5xl font-extrabold text-primary font-headline">10K+</span>
                <p class="text-on-surface-variant font-medium">ผู้เรียนที่ลงทะเบียน</p>
                <div class="h-1 w-12 bg-secondary rounded-full mt-4"></div>
            </div>
            <div class="bg-primary text-on-primary p-8 rounded-3xl flex flex-col items-center justify-center text-center space-y-2 shadow-xl shadow-primary/20">
                <span class="text-5xl font-extrabold font-headline">8K+</span>
                <p class="opacity-80 font-medium">ผ่านการรับรอง</p>
                <div class="h-1 w-12 bg-secondary-container rounded-full mt-4"></div>
            </div>
            <div class="bg-surface-container-low p-8 rounded-3xl flex flex-col items-center justify-center text-center space-y-2">
                <div class="flex items-center gap-1 text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="text-5xl font-extrabold text-primary font-headline">4.8</span>
                </div>
                <p class="text-on-surface-variant font-medium">คะแนนความพึงพอใจ (5.0)</p>
                <div class="h-1 w-12 bg-secondary rounded-full mt-4"></div>
            </div>
        </div>
    </section>

    {{-- Recommended Courses Section --}}
    <section id="courses" class="py-24 px-8 bg-surface-bright">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
                <div class="space-y-4">
                    <h2 class="text-4xl font-extrabold font-headline text-primary tracking-tight">หลักสูตรแนะนำ</h2>
                    <p class="text-on-surface-variant text-lg max-w-xl">
                        ยกระดับทักษะของคุณด้วยเนื้อหาที่คัดสรรมาเพื่อตอบโจทย์การทำงานจริงในระบบการศึกษา
                    </p>
                </div>
                <a href="#" class="text-primary font-bold flex items-center gap-2 group">
                    ดูทั้งหมด
                    <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                {{-- Course Card 1 --}}
                <div class="group bg-surface-container-lowest rounded-[2rem] overflow-hidden transition-all duration-300 hover:shadow-[0px_20px_40px_rgba(25,28,29,0.06)] flex flex-col">
                    <div class="relative h-56 overflow-hidden">
                        <img
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCb_VJuhlbxI8NEVIiNJyzAnSqtASMnTxR1HVqfU7UUZbTvLLTZFcBRE46FzSVW0I1SBRiKPTUWwIsYRcDtzecGOlA5UPpTH1fOLRQMF0AOTdgNzieJB-d2qQn6o7_FKIfB-yiLcpcwxdFZFGJLsFvgVgGO7H0cpLTLncZu1op-wLua0VrsK4hNmVw5XxVv4Q6uKYxJpWF7Vvu0-cK736y5Phg5-PsDq19DLL7cfxKzGv5ivGM-WntHit8C9ur-LMHpFylpIRY5yuEF"
                            alt="หน้าจอแดชบอร์ดการติดตามการศึกษา"
                        >
                        <div class="absolute top-4 left-4 bg-secondary-container text-on-secondary-container px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                            แนะนำ
                        </div>
                    </div>
                    <div class="p-8 flex flex-col flex-grow space-y-4">
                        <div class="flex items-center gap-1 text-secondary-fixed-dim">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star_half</span>
                            <span class="text-on-surface-variant text-xs ml-2">(4.9)</span>
                        </div>
                        <h3 class="text-xl font-bold font-headline text-primary leading-snug">
                            หลักสูตรนักประเมินผลการศึกษา: พื้นฐานและการประยุกต์
                        </h3>
                        <p class="text-on-surface-variant text-sm line-clamp-2">
                            เทคนิคการวิเคราะห์ข้อมูลและสรุปผลเพื่อการพัฒนาสถานศึกษาอย่างยั่งยืน
                        </p>
                        <div class="pt-4 mt-auto">
                            <button class="w-full py-3 bg-surface-container-low text-primary font-bold rounded-xl hover:bg-primary hover:text-on-primary transition-all">
                                ดูรายละเอียด
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Course Card 2 --}}
                <div class="group bg-surface-container-lowest rounded-[2rem] overflow-hidden transition-all duration-300 hover:shadow-[0px_20px_40px_rgba(25,28,29,0.06)] flex flex-col">
                    <div class="relative h-56 overflow-hidden">
                        <img
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuB7is4-L36iAgoGSYBcC3xCxX5Xhfpm5nvVp7vqV7KI6QBnB4w1oR4sdaRn2rlT2eP1L6zJLdshQTXkD2RYznplxFWYlcrwDutZUiXjVdq7LLL_vvM60R0SlTaQKvuvopr6wp0G0fgMd01iKf1OdQcbOVMYSshDBNm05Cbe8DK_FbvTbdO637sZxvL3g2eJ7TL6LxHoeccA45fa5xT62y6ZkYXNYOq5zPoJuUuLKsw363bAAxWfE-jY0cjHXoo5ylzxHWHqvkJACUja"
                            alt="การประชุมผู้บริหารทางการศึกษา"
                        >
                    </div>
                    <div class="p-8 flex flex-col flex-grow space-y-4">
                        <div class="flex items-center gap-1 text-secondary-fixed-dim">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="text-on-surface-variant text-xs ml-2">(5.0)</span>
                        </div>
                        <h3 class="text-xl font-bold font-headline text-primary leading-snug">
                            หลักสูตรสำหรับ ผอ. เขตพื้นที่การศึกษา: การบริหารเชิงกลยุทธ์
                        </h3>
                        <p class="text-on-surface-variant text-sm line-clamp-2">
                            การนำผลการติดตามประเมินผลไปใช้ในการวางแผนและขับเคลื่อนนโยบายระดับเขต
                        </p>
                        <div class="pt-4 mt-auto">
                            <button class="w-full py-3 bg-surface-container-low text-primary font-bold rounded-xl hover:bg-primary hover:text-on-primary transition-all">
                                ดูรายละเอียด
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Course Card 3 --}}
                <div class="group bg-surface-container-lowest rounded-[2rem] overflow-hidden transition-all duration-300 hover:shadow-[0px_20px_40px_rgba(25,28,29,0.06)] flex flex-col">
                    <div class="relative h-56 overflow-hidden">
                        <img
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJwe0FQWSiRFvDKss970cYMM8Moi21EYXkCRbjZ4Krn5srZBUYLkNCvzoWQ-2lHMXXAkWLP00I3_ZvSiMSrk3hy1LnLpZ9xk6Mjf5oWTUeapKTzMaWzjk0U0qXVZTvpPECf9mcJUk4dqb35HlBB4OWdr9MNDH7694wYnMDuuYbaiNPDmm6unSfLgpVcyx6Mx7WZZ2J42bS80dH-vsHggUjbrMmeLjEfw-HErhFJecdv7O4eWp4RwNOZx0yf7KSSmwsFWsA9RMOSKFV"
                            alt="ครูสอนในห้องเรียน"
                        >
                    </div>
                    <div class="p-8 flex flex-col flex-grow space-y-4">
                        <div class="flex items-center gap-1 text-secondary-fixed-dim">
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span class="material-symbols-outlined text-sm">star</span>
                            <span class="text-on-surface-variant text-xs ml-2">(4.7)</span>
                        </div>
                        <h3 class="text-xl font-bold font-headline text-primary leading-snug">
                            หลักสูตรสำหรับครู: การประกันคุณภาพการศึกษาระดับห้องเรียน
                        </h3>
                        <p class="text-on-surface-variant text-sm line-clamp-2">
                            สร้างมาตรฐานการสอนด้วยเครื่องมือประเมินผลที่แม่นยำและตอบโจทย์ผู้เรียน
                        </p>
                        <div class="pt-4 mt-auto">
                            <button class="w-full py-3 bg-surface-container-low text-primary font-bold rounded-xl hover:bg-primary hover:text-on-primary transition-all">
                                ดูรายละเอียด
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Call to Action Section --}}
    <section class="py-24 px-8">
        <div class="max-w-7xl mx-auto hero-gradient rounded-[3rem] p-12 lg:p-20 relative overflow-hidden text-center lg:text-left">
            <div class="relative z-10 lg:max-w-2xl space-y-8">
                <h2 class="text-4xl lg:text-5xl font-extrabold font-headline text-on-primary leading-tight">
                    พร้อมที่จะก้าวสู่การเป็น <br>
                    มืออาชีพด้านการประเมินผลหรือยัง?
                </h2>
                <p class="text-on-primary-container text-lg leading-relaxed">
                    ร่วมเป็นส่วนหนึ่งของการเปลี่ยนแปลง และสร้างมาตรฐานใหม่ให้กับระบบการศึกษาไทย
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="{{ route('register') }}" class="px-10 py-4 bg-secondary-container text-on-secondary-container font-bold rounded-xl text-lg shadow-xl hover:scale-105 transition-all">
                        สมัครเรียนเลยวันนี้
                    </a>
                    <a href="#" class="px-10 py-4 bg-white/10 text-on-primary backdrop-blur-md font-bold rounded-xl text-lg hover:bg-white/20 transition-all">
                        ติดต่อเรา
                    </a>
                </div>
            </div>
            <div class="absolute top-1/2 right-0 -translate-y-1/2 w-[500px] h-[500px] bg-secondary opacity-10 rounded-full blur-[100px]"></div>
        </div>
    </section>

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
                        <li><a class="hover:text-primary transition-colors" href="#">หลักสูตรทั้งหมด</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">วิธีการสมัครเรียน</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">คำถามที่พบบ่อย</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">คู่มือการใช้งาน</a></li>
                    </ul>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <h4 class="font-bold text-primary">นโยบาย</h4>
                    <ul class="space-y-4 text-sm text-on-surface-variant">
                        <li><a class="hover:text-primary transition-colors" href="#">นโยบายความเป็นส่วนตัว</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">เงื่อนไขการใช้งาน</a></li>
                        <li><a class="hover:text-primary transition-colors" href="#">การเข้าถึง</a></li>
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
                <p>© {{ date('Y') }} สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.) สพฐ. สงวนลิขสิทธิ์</p>
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
