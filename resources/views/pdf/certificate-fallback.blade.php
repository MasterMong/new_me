<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เกียรติบัตร - {{ $certificate->certificate_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1a1a1a;
        }

        .frame {
            border: 6px double #003e74;
            padding: 60px 50px;
            text-align: center;
            height: 100%;
            box-sizing: border-box;
        }

        .eyebrow {
            font-size: 14px;
            letter-spacing: 4px;
            color: #745b00;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 32px;
            color: #003e74;
            margin: 0 0 40px;
        }

        .name {
            font-size: 30px;
            font-weight: bold;
            margin: 20px 0;
            border-bottom: 2px solid #003e74;
            display: inline-block;
            padding-bottom: 8px;
        }

        .course-title {
            font-size: 18px;
            margin: 20px 0 40px;
        }

        .meta {
            font-size: 13px;
            color: #555555;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="frame">
        <p class="eyebrow">เกียรติบัตร</p>
        <h1>Certificate of Completion</h1>

        <p>ขอมอบเกียรติบัตรฉบับนี้ให้ไว้เพื่อแสดงว่า</p>
        <p class="name">{{ $certificate->full_name_on_cert }}</p>
        <p>ได้ผ่านการอบรมหลักสูตร</p>
        <p class="course-title">{{ $certificate->course->title }}</p>

        <p class="meta">
            เลขที่เกียรติบัตร {{ $certificate->certificate_number }}
            &nbsp;·&nbsp;
            วันที่ออก {{ $certificate->issued_date->translatedFormat('d F Y') }}
        </p>
    </div>
</body>
</html>
