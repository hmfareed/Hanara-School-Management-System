<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Badge - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
        }
        .badge-container {
            width: 240px;
            height: 380px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            padding: 15px;
            position: relative;
            background: #ffffff;
            overflow: hidden;
        }
        /* Design accents */
        .top-accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 75px;
            background-color: #0f172a;
            border-bottom: 4px solid #2563eb;
            z-index: 1;
        }
        .header {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #ffffff;
            margin-bottom: 20px;
        }
        .school-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            padding-top: 5px;
        }
        .school-subtitle {
            font-size: 8px;
            color: #94a3b8;
            margin: 2px 0 0 0;
        }
        .badge-body {
            position: relative;
            z-index: 2;
            margin-top: 50px;
            text-align: center;
        }
        .avatar-container {
            width: 90px;
            height: 90px;
            margin: 0 auto 12px auto;
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            background-color: #e2e8f0;
            overflow: hidden;
        }
        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-fallback {
            line-height: 84px;
            font-size: 32px;
            font-weight: bold;
            color: #475569;
        }
        .student-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
            text-transform: capitalize;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-role {
            font-size: 9px;
            color: #2563eb;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
            text-align: left;
        }
        .detail-row {
            font-size: 9px;
            margin-bottom: 3px;
            color: #334155;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: bold;
            color: #64748b;
            display: inline-block;
            width: 60px;
        }
        .detail-value {
            font-weight: 600;
            color: #0f172a;
        }
        .qr-container {
            margin: 0 auto;
            width: 65px;
            height: 65px;
        }
        .qr-img {
            width: 100%;
            height: 100%;
        }
        .footer-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8px;
            text-align: center;
            line-height: 25px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="badge-container">
        <div class="top-accent"></div>
        
        <div class="header">
            <h1 class="school-title">Hanara Schools</h1>
            <p class="school-subtitle">Accra, Ghana</p>
        </div>

        <div class="badge-body">
            <div class="avatar-container">
                @if($student->photo)
                    <img class="avatar-img" src="{{ public_path('storage/' . $student->photo) }}" alt="Photo">
                @else
                    <div class="avatar-fallback">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <h2 class="student-name">{{ $student->first_name }} {{ $student->last_name }}</h2>
            <div class="student-role">Student</div>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">Student ID:</span>
                    <span class="detail-value font-mono">{{ $student->student_id_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Class:</span>
                    <span class="detail-value">{{ $className }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gender:</span>
                    <span class="detail-value" style="text-transform: capitalize;">{{ $student->gender }}</span>
                </div>
            </div>

            <div class="qr-container">
                <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($student->student_id_number) }}" alt="QR Code">
            </div>
        </div>

        <div class="footer-bar">
            Official Student ID Card
        </div>
    </div>

</body>
</html>
