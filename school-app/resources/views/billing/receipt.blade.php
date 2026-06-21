<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $payment->payment_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-logo {
            font-size: 28px;
            font-weight: bold;
            color: #1a56db; /* Hanara brand primary */
            line-height: 1.1;
        }
        .header-sub {
            font-size: 11px;
            color: #666666;
            margin-top: 5px;
        }
        .header-receipt {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            color: #111827;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-label {
            font-size: 11px;
            color: #666666;
            text-transform: uppercase;
            font-weight: bold;
            padding-bottom: 4px;
        }
        .meta-value {
            font-size: 13px;
            color: #111827;
            font-weight: 500;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .details-header {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-cell {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .summary-label {
            text-align: right;
            padding: 6px 12px;
            font-size: 12px;
            color: #4b5563;
        }
        .summary-value {
            text-align: right;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            width: 120px;
        }
        .summary-total-label {
            text-align: right;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            border-top: 2px solid #e5e7eb;
        }
        .summary-total-value {
            text-align: right;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: bold;
            color: #1a56db;
            border-top: 2px solid #e5e7eb;
            width: 120px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
            color: #666666;
            border-t: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
            border-collapse: collapse;
        }
        .signature-line {
            border-top: 1px solid #9ca3af;
            width: 200px;
            text-align: center;
            font-size: 11px;
            color: #4b5563;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- School Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="header-logo">HANARA SCHOOLS</div>
                <div class="header-sub">
                    P.O. Box GP 1234, Accra, Ghana<br>
                    Tel: +233 24 123 4567 • Email: billing@hanaraschools.edu.gh
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="header-receipt">Official Receipt</div>
                <div class="header-sub" style="font-size: 12px;">
                    Receipt #: <strong>{{ $payment->payment_number }}</strong><br>
                    Date Issued: <strong>{{ $payment->payment_date->format('d M, Y') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Grid -->
    <table class="meta-table">
        <tr>
            <td style="width: 25%; vertical-align: top;">
                <div class="meta-label">Student ID</div>
                <div class="meta-value" style="font-family: monospace;">{{ $payment->invoice->student->student_id_number }}</div>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <div class="meta-label">Student Name</div>
                <div class="meta-value">{{ $payment->invoice->student->full_name }}</div>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <div class="meta-label">Academic Class</div>
                @php $enroll = $payment->invoice->student->currentClassEnrollment(); @endphp
                <div class="meta-value">{{ $enroll ? $enroll->classAcademicYear->schoolClass->name : 'N/A' }}</div>
            </td>
            <td style="width: 25%; vertical-align: top;">
                <div class="meta-label">Invoice Number</div>
                <div class="meta-value" style="font-family: monospace;">{{ $payment->invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    <!-- Payment Breakdowns -->
    <div class="section-title">Payment Summary</div>
    <table class="details-table">
        <thead>
            <tr class="details-header">
                <th class="details-cell" style="text-align: left;">Description</th>
                <th class="details-cell" style="text-align: left;">Payment Method</th>
                <th class="details-cell" style="text-align: left;">Reference / Tx ID</th>
                <th class="details-cell" style="text-align: right; width: 120px;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="details-cell">
                    School Fees Payment — Term {{ $payment->invoice->term->number }} ({{ $payment->invoice->term->academicYear->name }})
                </td>
                <td class="details-cell" style="text-transform: capitalize;">
                    {{ str_replace('_', ' ', $payment->method) }}
                </td>
                <td class="details-cell font-mono" style="font-size: 11px;">
                    {{ $payment->reference ?? 'N/A' }}
                </td>
                <td class="details-cell" style="text-align: right; font-weight: bold;">
                    GH₵{{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Ledger Totals -->
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: top; font-size: 11px; color: #666666;">
                <strong>Payment Notes:</strong><br>
                {{ $payment->notes ?? 'No additional remarks registered.' }}
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label">Total Billed:</td>
                        <td class="summary-value">GH₵{{ number_format($payment->invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Paid Prior:</td>
                        @php
                            $priorPaid = (float) $payment->invoice->amount_paid - (float) $payment->amount;
                        @endphp
                        <td class="summary-value">GH₵{{ number_format($priorPaid, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Paid This Receipt:</td>
                        <td class="summary-value" style="color: #16a34a;">GH₵{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-total-label">Outstanding Balance:</td>
                        <td class="summary-total-value">GH₵{{ number_format($payment->invoice->balance, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <table class="signatures" style="margin-top: 80px;">
        <tr>
            <td style="text-align: left;">
                <div class="signature-line">Parent / Guardian Signature</div>
            </td>
            <td style="text-align: right;">
                <div class="signature-line" style="float: right;">
                    Issued By: {{ $payment->receivedByUser ? $payment->receivedByUser->name : 'Administrator' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for partnering with Hanara Schools. Education is the best legacy.</p>
        <p style="font-size: 9px; color: #9ca3af; margin-top: 10px;">This is a computer-generated official receipt. No signature required for validation.</p>
    </div>

</body>
</html>
