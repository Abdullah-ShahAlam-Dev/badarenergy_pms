<!DOCTYPE html>
<html>
<head>
    <title>Gate Pass - {{ $gatePass->request_number }}</title>
    <style>
        *, *:before, *:after { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; }
        .container { width: 800px; margin: 0 auto; border: 1px solid #eee; padding: 30px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .company-info { display: table-cell; width: 50%; vertical-align: top; }
        .company-info h2 { margin: 0; color: #000; }
        .pass-info { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .pass-info h3 { margin: 0; color: #555; }
        .section-title { background: #f4f4f4; padding: 5px 10px; font-weight: bold; margin-top: 20px; border-left: 5px solid #000; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
        .detail-item { font-size: 14px; }
        .detail-item strong { display: inline-block; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #f9f9f9; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
        .signature-box { border-top: 1px solid #000; width: 150px; padding-top: 5px; font-size: 12px; }
        .qr-section { text-align: center; margin-top: 30px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
            .container { border: none; width: 100%; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #fff; border: none; cursor: pointer;">Print Now</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-info">
                <img src="{{ company()->logo_url }}" alt="Logo" style="height: 50px; margin-bottom: 10px;">
                <h2>{{ company()->company_name }}</h2>
                <p style="font-size: 12px; margin: 0;">{{ company()->address }}</p>
            </div>
            <div class="pass-info">
                <h3>GATE PASS ({{ strtoupper($gatePass->type) }})</h3>
                <p><strong>Number:</strong> {{ $gatePass->request_number }}</p>
                <p><strong>Date:</strong> {{ $gatePass->request_date->format(company()->date_format) }}</p>
            </div>
        </div>

        <div class="section-title">REQUESTER INFORMATION</div>
        <div class="details-grid">
            <div class="detail-item"><strong>Employee Name:</strong> {{ $gatePass->user->name }}</div>
            <div class="detail-item"><strong>Department:</strong> {{ $gatePass->department->team_name ?? '--' }}</div>
            <div class="detail-item"><strong>Employee ID:</strong> {{ $gatePass->user->employeeDetail->employee_id ?? '--' }}</div>
            <div class="detail-item"><strong>Contact:</strong> {{ $gatePass->user->mobile ?? '--' }}</div>
        </div>

        <div class="section-title">MOVEMENT DETAILS</div>
        <div class="details-grid">
            <div class="detail-item"><strong>From Location:</strong> {{ $gatePass->from_location ?: 'Main Office' }}</div>
            <div class="detail-item"><strong>To Location:</strong> {{ $gatePass->to_location ?: '--' }}</div>
            <div class="detail-item"><strong>Return Type:</strong> {{ ucwords($gatePass->return_type) }}</div>
            <div class="detail-item"><strong>Exp. Return Date:</strong> {{ $gatePass->expected_return_date ? $gatePass->expected_return_date->format(company()->date_format) : 'N/A' }}</div>
            <div class="detail-item"><strong>Vehicle No:</strong> {{ $gatePass->vehicle_number ?: '--' }}</div>
            <div class="detail-item"><strong>Driver Name:</strong> {{ $gatePass->driver_name ?: '--' }}</div>
        </div>

        <div class="section-title">ITEM LIST</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Serial / Asset Tag</th>
                    <th>Condition</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gatePass->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>{{ $item->serial_number ?: ($item->asset_tag ?: '--') }}</td>
                        <td>{{ $item->condition ?: 'Good' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(route('gate-pass.verify', $gatePass->qr_code)) }}" alt="QR Code">
            <p style="font-size: 10px; margin-top: 5px;">Verification Hash: {{ $gatePass->qr_code }}</p>
        </div>

        <div class="footer">
            <div class="signature-box">
                Requester Signature<br><br><br>
                ({{ $gatePass->user->name }})
            </div>
            <div class="signature-box">
                HOD Approved By<br><br><br>
                ({{ $gatePass->hod->name ?? '__________' }})
            </div>
            <div class="signature-box">
                Store Authorized By<br><br><br>
                ({{ $gatePass->store->name ?? '__________' }})
            </div>
            <div class="signature-box">
                Security Verified By<br><br><br>
                ({{ $gatePass->security->name ?? '__________' }})
            </div>
        </div>
        
        <p style="margin-top: 30px; font-size: 10px; text-align: center; color: #888;">
            This is a computer-generated gate pass. Printed on: {{ now()->format('d-M-Y H:i') }}
        </p>
    </div>
</body>
</html>
