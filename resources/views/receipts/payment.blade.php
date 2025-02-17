<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .amount {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #794646;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        .header-content {
            display: inline-block;
            text-align: left;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 50px;
            vertical-align: middle;
            margin-right: 10px;
            display: inline-block;
        }
        
        .company-name {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #794646;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="header-content">
                <img src="{{ public_path('storage/images/logo.png') }}" alt="Logo" class="logo">
                <span class="company-name">SBH BOOKING</span>
            </div>
            <h1>Payment Receipt</h1>
            <p>Receipt No: {{ $receipt_no }}</p>
            <p>Date: {{ $date }}</p>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td><strong>Tenant Name:</strong></td>
                    <td>{{ $tenant_name }}</td>
                </tr>
                <tr>
                    <td><strong>Boarding House:</strong></td>
                    <td>{{ $boarding_house }}</td>
                </tr>
                <tr>
                    <td><strong>Room:</strong></td>
                    <td>{{ $room_name }}</td>
                </tr>
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>{{ $payment_method }}</td>
                </tr>
                <tr>
                    <td><strong>Reference Number:</strong></td>
                    <td>{{ $reference_number }}</td>
                </tr>
            </table>
        </div>

        <div class="amount">
            Amount Paid: &#8369;{{ $amount }}
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>SBH BOOKING | Surigao City, Surigao Del Norte, Philippines</p>
        </div>
    </div>
</body>
</html> 