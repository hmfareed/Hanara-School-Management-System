<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Card - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
        }
        .school-sub {
            font-size: 12px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            text-transform: uppercase;
            color: #2563eb;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 15%;
        }
        .meta-value {
            color: #0f172a;
            width: 35%;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 25px;
        }
        .summary-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
            font-size: 14px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }
        .summary-grid {
            width: 100%;
        }
        .summary-grid td {
            padding: 2px 0;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }
        .results-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 12px;
            border: 1px solid #0f172a;
        }
        .results-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 12px;
        }
        .results-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .remarks-section {
            margin-top: 40px;
            width: 100%;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            margin-top: 45px;
            text-align: center;
            font-size: 12px;
            color: #475569;
            width: 200px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="school-name">Hanara Schools</h1>
        <p class="school-sub">P.O. Box GP 1234, Accra, Ghana | Tel: +233 24 000 0000 | Email: info@hanaraschools.edu.gh</p>
        <div class="report-title">Student Progress Report (Report Card)</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Student Name:</td>
            <td class="meta-value font-bold">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
            <td class="meta-label">Academic Year:</td>
            <td class="meta-value">{{ $currentYear?->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Student ID:</td>
            <td class="meta-value">{{ $student->student_id_number }}</td>
            <td class="meta-label">Academic Term:</td>
            <td class="meta-value">{{ $currentTerm?->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Class:</td>
            <td class="meta-value">{{ $schoolClass->name }}</td>
            <td class="meta-label">Date Generated:</td>
            <td class="meta-value">{{ now()->format('d M, Y') }}</td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-title">Performance Summary</div>
        <table class="summary-grid">
            <tr>
                <td style="width: 25%;"><span class="font-bold">Average Score:</span> {{ $averageScore }}%</td>
                <td style="width: 25%;"><span class="font-bold">Position:</span> {{ $position }} of {{ $totalStudents }}</td>
                <td style="width: 50%;">&nbsp;</td>
            </tr>
        </table>
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th style="width: 30%;">Subject</th>
                @foreach($components as $component)
                    <th class="text-center" style="width: 10%;">{{ $component->name }} ({{ $component->weight }}%)</th>
                @endforeach
                <th class="text-center" style="width: 10%;">Total (100%)</th>
                <th class="text-center" style="width: 8%;">Grade</th>
                <th style="width: 22%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td class="font-bold">{{ $result['subject'] }}</td>
                    @foreach($components as $component)
                        <td class="text-center">
                            {{ $result['scores'][$component->id] !== null ? number_format($result['scores'][$component->id], 1) : '—' }}
                        </td>
                    @endforeach
                    <td class="text-center font-bold">{{ number_format($result['total'], 1) }}%</td>
                    <td class="text-center font-bold" style="color: {{ $result['grade'] == 'F' || $result['grade'] == '9' ? '#ef4444' : 'inherit' }}">
                        {{ $result['grade'] }}
                    </td>
                    <td>{{ $result['remarks'] ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="remarks-section">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 30px;">
                <div class="font-bold" style="color: #475569; margin-bottom: 5px;">Class Teacher's Remarks</div>
                <div style="border-bottom: 1px solid #cbd5e1; height: 35px; line-height: 35px; color: #64748b;">
                    {{ $averageScore >= 80 ? 'An excellent performance, keep it up!' : ($averageScore >= 60 ? 'Good performance, but can do better.' : 'Needs to work harder in subsequent terms.') }}
                </div>
                <div class="signature-line">Class Teacher Signature</div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 30px;">
                <div class="font-bold" style="color: #475569; margin-bottom: 5px;">Head Teacher's Remarks</div>
                <div style="border-bottom: 1px solid #cbd5e1; height: 35px; line-height: 35px; color: #64748b;">
                    {{ $averageScore >= 80 ? 'Very impressive work this term.' : ($averageScore >= 60 ? 'Satisfactory results. Encouraged to aim higher.' : 'A lot of effort is required next term.') }}
                </div>
                <div class="signature-line">Head Teacher Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generated electronically by Hanara School Management System.
    </div>

</body>
</html>
