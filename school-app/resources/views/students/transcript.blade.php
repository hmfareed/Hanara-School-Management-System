<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Transcript — {{ $student->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a2e; line-height: 1.5; padding: 30px; }
        .header { text-align: center; border-bottom: 3px solid #1a1a6e; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #1a1a6e; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .header .motto { font-size: 10px; color: #666; font-style: italic; margin-bottom: 8px; }
        .header h2 { font-size: 14px; color: #333; text-transform: uppercase; letter-spacing: 1px; background: #f0f0ff; padding: 6px; border-radius: 4px; }
        .student-info { margin-bottom: 20px; display: table; width: 100%; }
        .student-info .row { display: table-row; }
        .student-info .label { display: table-cell; font-weight: bold; color: #555; padding: 3px 10px 3px 0; width: 140px; }
        .student-info .value { display: table-cell; padding: 3px 0; color: #1a1a2e; }
        .year-section { margin-bottom: 20px; page-break-inside: avoid; }
        .year-header { background: #1a1a6e; color: white; padding: 6px 12px; font-size: 12px; font-weight: bold; border-radius: 4px 4px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th { background: #f0f0ff; color: #1a1a6e; text-align: left; padding: 6px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #1a1a6e; }
        td { padding: 5px 10px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) { background: #fafafe; }
        .grade-cell { text-align: center; font-weight: bold; }
        .score-cell { text-align: center; }
        .footer { margin-top: 30px; border-top: 2px solid #1a1a6e; padding-top: 15px; }
        .footer-row { display: table; width: 100%; margin-top: 30px; }
        .footer-col { display: table-cell; width: 50%; }
        .signature-line { border-top: 1px solid #333; width: 180px; margin-top: 40px; padding-top: 4px; font-size: 10px; color: #555; }
        .stamp { text-align: center; font-size: 10px; color: #999; margin-top: 10px; font-style: italic; }
        .watermark { position: fixed; top: 40%; left: 20%; font-size: 80px; color: rgba(26, 26, 110, 0.03); transform: rotate(-30deg); z-index: -1; letter-spacing: 10px; }
    </style>
</head>
<body>
    <div class="watermark">TRANSCRIPT</div>

    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <div class="motto">{{ $schoolMotto }}</div>
        <h2>Official Academic Transcript</h2>
    </div>

    <div class="student-info">
        <div class="row">
            <span class="label">Student Name:</span>
            <span class="value">{{ $student->full_name }}</span>
        </div>
        <div class="row">
            <span class="label">Student ID:</span>
            <span class="value">{{ $student->student_id_number }}</span>
        </div>
        <div class="row">
            <span class="label">Date of Birth:</span>
            <span class="value">{{ $student->date_of_birth?->format('d F Y') ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="label">Admission Date:</span>
            <span class="value">{{ $student->admission_date?->format('d F Y') ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="label">Current Class:</span>
            <span class="value">{{ $currentEnrollment?->classAcademicYear?->schoolClass?->name ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span class="label">Date Issued:</span>
            <span class="value">{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    @forelse($academicHistory as $yearRecord)
    <div class="year-section">
        <div class="year-header">{{ $yearRecord['year'] }} — {{ $yearRecord['class'] }}</div>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th style="text-align: center; width: 80px;">Score (%)</th>
                    <th style="text-align: center; width: 60px;">Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($yearRecord['subjects'] as $subj)
                <tr>
                    <td>{{ $subj['subject'] }}</td>
                    <td class="score-cell">{{ $subj['score'] }}</td>
                    <td class="grade-cell">{{ $subj['grade'] }}</td>
                    <td>{{ $subj['remarks'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <p style="text-align: center; color: #999; padding: 30px 0;">No academic records available.</p>
    @endforelse

    <div class="footer">
        <p style="font-size: 10px; color: #555;">This is an official transcript issued by {{ $schoolName }}. Any alteration renders this document invalid.</p>
        <div class="footer-row">
            <div class="footer-col">
                <div class="signature-line">Head Teacher's Signature</div>
            </div>
            <div class="footer-col" style="text-align: right;">
                <div class="signature-line" style="margin-left: auto;">School Stamp</div>
            </div>
        </div>
        <div class="stamp">Generated on {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
