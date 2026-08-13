<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gate Pass - {{ $gatePass->request_number }}</title>
    <style>
        *, *:before, *:after { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { width: 850px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: table; width: 100%; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info { display: table-cell; width: 50%; vertical-align: top; }
        .company-info h2 { margin: 0; color: #111; font-size: 22px; font-weight: bold; }
        .pass-info { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .pass-info h3 { margin: 0; color: #17a2b8; font-size: 20px; letter-spacing: 0.5px; }
        .section-title { background: #f1f5f9; color: #1e293b; padding: 6px 12px; font-weight: bold; font-size: 13px; margin-top: 20px; border-left: 4px solid #0284c7; text-transform: uppercase; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px; margin-top: 10px; }
        .detail-item { font-size: 13px; }
        .detail-item strong { display: inline-block; width: 140px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; font-size: 13px; }
        th { background-color: #f8fafc; color: #334155; font-weight: bold; }
        .footer { margin-top: 45px; display: flex; justify-content: space-between; text-align: center; }
        .signature-box { border-top: 1px dashed #64748b; width: 160px; padding-top: 6px; font-size: 11px; color: #475569; }
        .qr-section { text-align: center; margin-top: 25px; }
        
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; margin: 0; }
            .container { border: none; box-shadow: none; width: 100%; padding: 10px; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; font-size: 14px; font-weight: bold; background: #0284c7; color: #ffffff; border: none; border-radius: 4px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            🖨️ Print Gate Pass
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; background: #64748b; color: #ffffff; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-info">
                @if(company()->logo_url)
                    <img src="{{ company()->logo_url }}" alt="Logo" style="height: 45px; margin-bottom: 8px;">
                @endif
                <h2>{{ company()->company_name }}</h2>
                <p style="font-size: 11px; margin: 0; color: #64748b;">{{ company()->address }}</p>
            </div>
            <div class="pass-info">
                <h3>GATE PASS ({{ strtoupper($gatePass->type) }})</h3>
                <p style="margin: 4px 0 0 0;"><strong>Number:</strong> {{ $gatePass->request_number }}</p>
                <p style="margin: 2px 0 0 0;"><strong>Date:</strong> {{ $gatePass->request_date->format(company()->date_format) }}</p>
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
            <div class="detail-item"><strong>From Location:</strong> {{ $gatePass->from_location ?: 'Main Warehouse' }}</div>
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
                    <th width="5%">#</th>
                    <th width="45%">Item Description</th>
                    <th width="15%">Quantity</th>
                    <th width="20%">Serial / Asset Tag</th>
                    <th width="15%">Condition</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gatePass->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                        <td>{{ $item->serial_number ?: ($item->asset_tag ?: '--') }}</td>
                        <td>{{ $item->condition ?: 'Good' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($gatePass->qr_code)
            <div class="qr-section">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(route('gate-pass.verify', $gatePass->qr_code)) }}" alt="QR Code">
                <p style="font-size: 10px; margin-top: 4px; color: #64748b;">Verification Hash: {{ $gatePass->qr_code }}</p>
            </div>
        @endif

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
        
        <p style="margin-top: 30px; font-size: 10px; text-align: center; color: #94a3b8;">
            This is a computer-generated gate pass. Printed on: {{ now()->format('d-M-Y H:i') }}
        </p>
    </div>

    <!-- Automatic Native Browser Print Trigger -->
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
