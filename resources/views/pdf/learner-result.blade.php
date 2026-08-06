<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานผลการเรียนรู้ - {{ $enrollment->course->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* For Thai support if configured, fallback to sans-serif */
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0056b3;
            margin: 0;
            font-size: 24px;
        }
        .info {
            margin-bottom: 30px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: bold;
        }
        .text-left {
            text-align: left;
        }
        .status-pass {
            color: #28a745;
            font-weight: bold;
        }
        .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>รายงานผลการเรียนรู้รายบุคคล</h1>
        <p>{{ $enrollment->course->title }}</p>
    </div>

    <div class="info">
        <p><strong>ชื่อ-นามสกุล:</strong> {{ $user->title }} {{ $user->first_name }} {{ $user->last_name }}</p>
        <p><strong>วันที่ออกรายงาน:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>วันที่ลงทะเบียน:</strong> {{ $enrollment->enrolled_at->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left">รายการประเมิน</th>
                <th>คะแนนที่ได้ (%)</th>
                <th>เกณฑ์ผ่าน (%)</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @php
                $course = $enrollment->course;
                // Constrained to module_id = null: every module also has its own
                // pre_test/post_test now, so an unscoped lookup would be ambiguous
                // between those and the course-wide one.
                $courseWideAssessments = $course->assessments->whereNull('module_id');
                $preTest = $courseWideAssessments->where('type', 'pre_test')->first();
                $postTest = $courseWideAssessments->where('type', 'post_test')->first();

                $preTestScore = $preTest ? $preTest->attempts->max('score_pct') : null;
                $postTestScore = $postTest ? $postTest->attempts->max('score_pct') : null;
                $isPassed = $postTestScore !== null && $postTestScore >= ($postTest->passing_score_pct ?? 60);
            @endphp

            @if($preTest)
            <tr>
                <td class="text-left">แบบทดสอบก่อนเรียน (Pre-test)</td>
                <td>{{ $preTestScore !== null ? $preTestScore . '%' : '-' }}</td>
                <td>-</td>
                <td>{{ $preTestScore !== null ? 'ทดสอบแล้ว' : 'ยังไม่ทดสอบ' }}</td>
            </tr>
            @endif

            @foreach($course->modules as $module)
                @php
                    $moduleTest = $module->assessments->firstWhere('type', 'post_test')
                        ?? $module->assessments->firstWhere('type', 'module_test');
                    $moduleScore = $moduleTest ? $moduleTest->attempts->max('score_pct') : null;
                    $modulePassed = $moduleTest ? ($moduleScore !== null && $moduleScore >= ($moduleTest->passing_score_pct ?? 60)) : true;
                @endphp
                @if($moduleTest)
                <tr>
                    <td class="text-left">- โมดูล {{ $module->module_number }}: {{ $module->title }}</td>
                    <td>{{ $moduleScore !== null ? $moduleScore . '%' : '-' }}</td>
                    <td>{{ $moduleTest->passing_score_pct ?? 60 }}%</td>
                    <td class="{{ $moduleScore !== null ? ($modulePassed ? 'status-pass' : 'status-fail') : '' }}">
                        {{ $moduleScore !== null ? ($modulePassed ? 'ผ่าน' : 'ไม่ผ่าน') : 'ยังไม่ทดสอบ' }}
                    </td>
                </tr>
                @endif
            @endforeach

            @if($postTest)
            <tr>
                <td class="text-left"><strong>แบบทดสอบหลังเรียน (Post-test)</strong></td>
                <td><strong>{{ $postTestScore !== null ? $postTestScore . '%' : '-' }}</strong></td>
                <td>{{ $postTest->passing_score_pct ?? 60 }}%</td>
                <td class="{{ $postTestScore !== null ? ($isPassed ? 'status-pass' : 'status-fail') : '' }}">
                    <strong>{{ $postTestScore !== null ? ($isPassed ? 'ผ่านเกณฑ์' : 'ไม่ผ่านเกณฑ์') : 'ยังไม่ทดสอบ' }}</strong>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
