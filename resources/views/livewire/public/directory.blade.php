<div class="min-h-screen bg-surface">
    {{-- Hero Section --}}
    <div class="bg-primary pt-32 pb-20 px-6 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-1/3 h-full bg-white/5 skew-x-[-20deg] translate-x-1/2"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <flux:badge color="blue" size="sm" class="mb-4 !bg-white/20 !text-white border-0">PUBLIC DIRECTORY</flux:badge>
            <h1 class="text-4xl md:text-5xl font-black font-headline text-white tracking-tight mb-6">
                ทำเนียบนักติดตาม <br/>
                <span class="text-secondary-container">Certified ME-Trackers</span>
            </h1>
            <p class="text-lg text-white/70 max-w-2xl leading-relaxed">
                ตรวจสอบรายชื่อผู้ที่ผ่านการอบรมและได้รับใบประกาศนียบัตรรับรองมาตรฐานการติดตามและประเมินผล (Monitoring & Evaluation) 
                จากหน่วยงานภายใต้โครงการ ME-Learning
            </p>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="max-w-7xl mx-auto px-6 -mt-10 relative z-20">
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-primary/10 border border-outline-variant/30 p-8 flex flex-col md:flex-row gap-6 items-center">
            <div class="flex-1 w-full">
                <flux:input 
                    placeholder="ค้นหาชื่อ-นามสกุล หรือ รหัสใบประกาศ..." 
                    icon="magnifying-glass"
                    size="xl"
                    class="!rounded-2xl"
                />
            </div>
            <div class="flex gap-4 w-full md:w-auto">
                <flux:select placeholder="ทุกรุ่นการอบรม" class="w-full md:w-48 !rounded-2xl" size="xl">
                    <flux:select.option>รุ่นที่ 1 (2566)</flux:select.option>
                    <flux:select.option>รุ่นที่ 2 (2567)</flux:select.option>
                </flux:select>
                <flux:button variant="primary" size="xl" class="!rounded-2xl shrink-0">ค้นหา</flux:button>
            </div>
        </div>
    </div>

    {{-- Directory Table --}}
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="bg-white rounded-[2.5rem] border border-outline-variant/30 overflow-hidden shadow-sm">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="ps-8 py-5 uppercase tracking-widest text-[10px] font-black opacity-40">Certified Name</flux:table.column>
                    <flux:table.column class="uppercase tracking-widest text-[10px] font-black opacity-40">Certificate ID</flux:table.column>
                    <flux:table.column class="uppercase tracking-widest text-[10px] font-black opacity-40">Course</flux:table.column>
                    <flux:table.column class="uppercase tracking-widest text-[10px] font-black opacity-40">Issue Date</flux:table.column>
                    <flux:table.column class="pe-8 uppercase tracking-widest text-[10px] font-black opacity-40 text-right">Verification</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    {{-- Mock Data --}}
                    @foreach ([
                        ['นายสมชาย มั่นคง', 'ME-2024-001', 'การประเมินผลพื้นฐาน', '15 ม.ค. 2567'],
                        ['นางสาววิภาดา ใจดี', 'ME-2024-002', 'การวิเคราะห์ข้อมูลโครงการ', '20 ม.ค. 2567'],
                        ['นายรุ่งโรจน์ สุขสวัสดิ์', 'ME-2024-003', 'การประเมินผลเชิงลึก', '02 ก.พ. 2567'],
                        ['นางรัตนาภรณ์ แสงทอง', 'ME-2024-004', 'การเขียนรายงานสรุปผล', '10 ก.พ. 2567'],
                        ['นายปกรณ์ มีศิลป์', 'ME-2024-005', 'การติดตามผลโครงการภาครัฐ', '22 ก.พ. 2567']
                    ] as $tracker)
                        <flux:table.row class="group hover:bg-primary/5 transition-colors">
                            <flux:table.cell class="ps-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-full bg-surface border border-outline-variant/20 flex items-center justify-center font-bold text-xs text-on-surface/40">
                                        {{ mb_substr($tracker[0], 0, 1) }}
                                    </div>
                                    <span class="font-bold text-on-surface leading-tight group-hover:text-primary transition-colors">{{ $tracker[0] }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <code class="text-xs font-bold text-primary bg-primary/5 px-2 py-1 rounded-lg">{{ $tracker[1] }}</code>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-on-surface/60 font-medium">
                                {{ $tracker[2] }}
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-on-surface/40 font-bold">
                                {{ $tracker[3] }}
                            </flux:table.cell>
                            <flux:table.cell class="pe-8 text-right">
                                <flux:button variant="ghost" size="sm" icon="check-badge" class="text-green-600">Verified</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="p-8 border-t border-outline-variant/10 flex justify-center">
                <flux:button variant="ghost">โหลดเพิ่มเติม...</flux:button>
            </div>
        </div>
    </div>
</div>
