<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Testimonial — {{ $student->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #1a1a2e; line-height: 1.8; padding: 50px 60px; }
        .header { text-align: center; border-bottom: 3px solid #1a1a6e; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { font-size: 24px; color: #1a1a6e; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 5px; }
        .header .motto { font-size: 11px; color: #666; font-style: italic; margin-bottom: 10px; }
        .header h2 { font-size: 16px; color: #444; text-transform: uppercase; letter-spacing: 2px; border: 2px solid #1a1a6e; display: inline-block; padding: 6px 20px; }
        .body-text { margin: 25px 0; text-align: justify; }
        .body-text p { margin-bottom: 15px; }
        .student-name { font-weight: bold; color: #1a1a6e; }
        .footer { margin-top: 60px; }
        .footer-row { display: table; width: 100%; }
        .footer-col { display: table-cell; width: 50%; vertical-align: top; }
        .signature-block { margin-top: 50px; }
        .signature-line { border-top: 1px solid #333; width: 200px; padding-top: 5px; font-size: 11px; color: #555; }
        .date-line { margin-top: 8px; font-size: 11px; color: #555; }
        .watermark { position: fixed; top: 35%; left: 15%; font-size: 90px; color: rgba(26, 26, 110, 0.03); transform: rotate(-30deg); z-index: -1; letter-spacing: 8px; }
        .ref { font-size: 10px; color: #999; text-align: center; margin-top: 40px; font-style: italic; }
    </style>
</head>
<body>
    <div class="watermark">TESTIMONIAL</div>

    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <div class="motto">{{ $schoolMotto }}</div>
        <h2>Testimonial</h2>
    </div>

    <div class="body-text">
        <p>To Whom It May Concern,</p>

        <p>This is to certify that <span class="student-name">{{ $student->full_name }}</span>,
        bearing Student ID <strong>{{ $student->student_id_number }}</strong>,
        @if($student->date_of_birth)
        born on <strong>{{ $student->date_of_birth->format('d F Y') }}</strong>,
        @endif
        was a student of <strong>{{ $schoolName }}</strong> from
        <strong>{{ $student->admission_date?->format('F Y') ?? 'N/A' }}</strong>
        to <strong>{{ now()->format('F Y') }}</strong>,
        a period of approximately <strong>{{ $yearsAttended }} year(s)</strong>.</p>

        <p>During the period of attendance, {{ $student->gender === 'Female' ? 'she' : 'he' }} was enrolled in <strong>{{ $className }}</strong>
        and demonstrated commitment to {{ $student->gender === 'Female' ? 'her' : 'his' }} academic work.</p>

        <p>{{ $student->gender === 'Female' ? 'She' : 'He' }} was well-behaved, showed good conduct, and maintained a satisfactory relationship with both staff and fellow students throughout
        {{ $student->gender === 'Female' ? 'her' : 'his' }} time at the school.</p>

        <p>We wish {{ $student->gender === 'Female' ? 'her' : 'him' }} the very best in {{ $student->gender === 'Female' ? 'her' : 'his' }} future academic and professional endeavours.</p>

        <p>This testimonial is issued at {{ $student->gender === 'Female' ? 'her' : 'his' }} request for whatever purpose it may serve.</p>
    </div>

    <div class="footer">
        <div class="footer-row">
            <div class="footer-col">
                <div class="signature-block">
                    <div class="signature-line">{{ $headTeacher }}</div>
                    <div class="date-line">Head Teacher</div>
                </div>
            </div>
            <div class="footer-col" style="text-align: right;">
                <div class="signature-block">
                    <div class="signature-line" style="margin-left: auto;">School Stamp</div>
                    <div class="date-line">Date: {{ now()->format('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ref">
        Ref: TEST-{{ $student->student_id_number }}-{{ now()->format('Ymd') }} |
        Generated on {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
