<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เกียรติบัตร - {{ $certificate->certificate_number }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .certificate {
            position: relative;
            width: 100%;
        }

        .certificate img {
            width: 100%;
            display: block;
        }

        {{-- dompdf doesn't reliably support CSS transform, so centering uses a
             fixed-width box with a matching negative margin instead of
             translate(-50%, -50%). --}}
        .overlay {
            position: absolute;
            width: 600px;
            margin-left: -300px;
            text-align: center;
        }

        .name {
            font-size: 28px;
            font-weight: bold;
            color: #1a1a1a;
            margin-top: -18px;
        }

        .date {
            font-size: 16px;
            color: #333333;
            margin-top: -10px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <img src="{{ $imagePath }}">

        <div class="overlay" style="left: {{ $namePercentX }}%; top: {{ $namePercentY }}%;">
            <div class="name">{{ $certificate->full_name_on_cert }}</div>
        </div>

        <div class="overlay" style="left: {{ $datePercentX }}%; top: {{ $datePercentY }}%;">
            <div class="date">{{ $certificate->issued_date->translatedFormat('d F Y') }}</div>
        </div>
    </div>
</body>
</html>
