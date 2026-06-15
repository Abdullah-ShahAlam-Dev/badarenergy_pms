<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order — {{ $workOrder->wo_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; background: #f4f6f9; margin: 0; padding: 0; }
        .no-print {
            background: #1a1a2e;
            padding: 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-btn {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .print-btn:hover {
            background: #0056b3;
        }
        .page { 
            width: 820px; 
            margin: 30px auto; 
            padding: 40px; 
            background: #fff; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 8px;
            box-sizing: border-box;
        }
        /* Header */
        .header { display: table; width: 100%; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left { display: table-cell; width: 60%; vertical-align: middle; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: middle; }
        .company-logo { max-height: 70px; max-width: 200px; }
        .company-name { font-size: 18px; font-weight: bold; color: #1a1a2e; }
        .company-info { font-size: 11px; color: #666; margin-top: 4px; line-height: 1.4; }
        .wo-title { font-size: 24px; font-weight: bold; color: #1a1a2e; letter-spacing: 0.5px; }
        .wo-number { font-size: 16px; color: #666; margin-top: 4px; font-weight: 600; }
        /* Info Section */
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 6px 8px; font-size: 12px; }
        .info-table .label { color: #777; width: 25%; }
        .info-table .value { font-weight: bold; color: #333; }
        /* Vendor Box */
        .vendor-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; margin-bottom: 20px; }
        .vendor-box h4 { font-size: 13px; color: #1a1a2e; margin: 0 0 8px 0; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        .vendor-box p { margin: 3px 0; font-size: 12px; color: #555; }
        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #1a1a2e; color: #fff; padding: 10px 8px; text-align: left; font-size: 12px; font-weight: 600; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 12px; color: #444; }
        .items-table tr:nth-child(even) td { background: #f9f9f9; }
        .items-table .text-right { text-align: right; }
        /* Totals */
        .totals-table { width: 45%; margin-left: auto; border-collapse: collapse; margin-bottom: 20px; }
        .totals-table td { padding: 6px 10px; font-size: 12px; color: #555; }
        .totals-table .grand-row td { font-weight: bold; font-size: 15px; border-top: 2px solid #333; padding-top: 10px; color: #1a1a2e; }
        /* Notes */
        .section-title { font-size: 13px; font-weight: bold; color: #1a1a2e; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 10px; margin-top: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .notes-text { font-size: 12px; color: #555; line-height: 1.6; margin: 0 0 15px 0; }
        /* Signatures */
        .signatures { display: table; width: 100%; margin-top: 50px; }
        .sig-cell { display: table-cell; width: 50%; text-align: center; padding: 0 10px; }
        .sig-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 6px; font-size: 11px; color: #666; }
        /* Status badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-approved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-draft { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
        .status-rejected { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        @media print {
            .no-print { display: none !important; }
            body { background: none; }
            .page { 
                width: 100%; 
                margin: 0; 
                padding: 0; 
                box-shadow: none; 
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
    </div>
    <div class="page">
    @php
        $isWithoutPayment = $workOrder->items->count() > 0 && $workOrder->items->every(fn($item) => $item->without_amount);
    @endphp

    {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            @if($company->logo)
                <img src="{{ $company->logo_url }}" class="company-logo" alt="Logo">
            @endif
            <div class="company-name mt-2">{{ $company->company_name }}</div>
            <div class="company-info">{{ $company->address ?? '' }}</div>
        </div>
        <div class="header-right">
            <div class="wo-title">WORK ORDER</div>
            <div class="wo-number">#{{ $workOrder->wo_number }}</div>
            <div style="margin-top:6px;">
                @php
                    $statusMap = ['approved'=>'status-approved','pending_approval'=>'status-pending','draft'=>'status-draft','rejected'=>'status-rejected'];
                    $sCls = $statusMap[$workOrder->status] ?? 'status-draft';
                @endphp
                <span class="status-badge {{ $sCls }}">{{ ucwords(str_replace('_',' ',$workOrder->status)) }}</span>
            </div>
        </div>
    </div>

    {{-- ── INFO GRID ───────────────────────────────────────────────────────── --}}
    <table class="info-table">
        <tr>
            <td class="label">WO Date</td>
            <td class="value">{{ $workOrder->wo_date ? $workOrder->wo_date->format(company()->date_format) : '--' }}</td>
            <td class="label">Completion Date Time</td>
            <td class="value">{{ $workOrder->completion_date_time ? $workOrder->completion_date_time->format(company()->date_format . ' H:i') : '--' }}</td>
        </tr>
        <tr>
            <td class="label">Event</td>
            <td class="value">{{ $workOrder->event->event_name ?? '--' }}</td>
            <td class="label">Venue</td>
            <td class="value">{{ $workOrder->venue ?? '--' }}</td>
        </tr>
        <tr>
            <td class="label">Work Category</td>
            <td class="value">{{ $workOrder->work_category ?? '--' }}</td>
            <td class="label">Priority</td>
            <td class="value">{{ ucfirst($workOrder->priority) }}</td>
        </tr>
        <tr>
            <td class="label">No. of Days</td>
            <td class="value">{{ $workOrder->no_of_days }}</td>
            <td class="label">Prepared By</td>
            <td class="value">{{ $workOrder->creator->name ?? '--' }}</td>
        </tr>
    </table>

    {{-- ── VENDOR BOX ──────────────────────────────────────────────────────── --}}
    @if($workOrder->vendor)
    <div class="vendor-box">
        <h4>Vendor Details</h4>
        <p><strong>{{ $workOrder->vendor->vendor_name }}</strong> — {{ $workOrder->vendor->company_name }}</p>
        <p>{{ $workOrder->vendor->designation }}</p>
        @if($workOrder->vendor->mobile) <p>Mobile: {{ $workOrder->vendor->mobile }}</p> @endif
        @if($workOrder->vendor->email)  <p>Email: {{ $workOrder->vendor->email }}</p> @endif
        @if($workOrder->vendor->office_address) <p>{{ $workOrder->vendor->office_address }}</p> @endif
    </div>
    @endif

    {{-- ── DESCRIPTION ─────────────────────────────────────────────────────── --}}
    @if($workOrder->description)
    <div class="section-title">Work Description</div>
    <p class="notes-text">{{ $workOrder->description }}</p>
    @endif

    {{-- ── ITEMS TABLE ─────────────────────────────────────────────────────── --}}
    <div class="section-title">Items / Services</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Item / Service</th>
                <th>Expected Completion</th>
                <th class="text-right">Qty</th>
                <th>Unit</th>
                <th>SQM (From - To)</th>
                <th class="text-right">Total SQM</th>
                @if(!$isWithoutPayment)
                    <th class="text-right">Rate</th>
                    <th>Tax Name</th>
                    <th>Tax Mode</th>
                    <th class="text-right">Tax Value</th>
                    <th class="text-right">Total</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $totalSqm = 0;
            @endphp
            @foreach($workOrder->items as $i => $item)
            @php
                $rowSqm = max(0, $item->sqm_to - $item->sqm_from) * $item->quantity;
                $totalSqm += $rowSqm;
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td style="font-size:11px;color:#666;">{{ $item->completion_date_time ? $item->completion_date_time->format(company()->date_format . ' H:i') : '--' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td>{{ $item->unit ?? '--' }}</td>
                <td>{{ $item->sqm_from }} - {{ $item->sqm_to }}</td>
                <td class="text-right">{{ number_format($rowSqm, 2) }}</td>
                @if(!$isWithoutPayment)
                    <td class="text-right">{{ $item->without_amount ? '--' : number_format($item->rate, 2) }}</td>
                    <td>{{ $item->without_amount ? '--' : ($item->tax_name ?? 'None') }}</td>
                    <td>{{ $item->without_amount ? '--' : ucfirst($item->tax_type) }}</td>
                    <td class="text-right">{{ $item->without_amount ? '--' : ($item->tax_method == 'fixed' ? number_format($item->tax_percent, 2) : $item->tax_percent . '%') }}</td>
                    <td class="text-right"><strong>{{ $item->without_amount ? '--' : number_format($item->total, 2) }}</strong></td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ──────────────────────────────────────────────────────────── --}}
    <table class="totals-table">
        <tr><td>Total SQM</td><td class="text-right">{{ number_format($totalSqm, 2) }}</td></tr>
        @if(!$isWithoutPayment)
            <tr><td>Sub Total</td><td class="text-right">{{ number_format($workOrder->sub_total, 2) }}</td></tr>
            <tr><td>Discount ({{ $workOrder->discount_type == 'percent' ? $workOrder->discount.'%' : 'Fixed' }})</td><td class="text-right">-{{ number_format($workOrder->discount_type == 'percent' ? $workOrder->sub_total * ($workOrder->discount / 100) : $workOrder->discount, 2) }}</td></tr>
            <tr><td>Tax Amount</td><td class="text-right">{{ number_format($workOrder->tax_amount, 2) }}</td></tr>
            <tr class="grand-row"><td>Grand Total</td><td class="text-right">{{ number_format($workOrder->grand_total, 2) }}</td></tr>
        @endif
    </table>

    {{-- ── REMARKS / TERMS ─────────────────────────────────────────────────── --}}
    @if($workOrder->remarks)
    <div class="section-title">Remarks</div>
    <p class="notes-text">{{ $workOrder->remarks }}</p>
    @endif

    @if($workOrder->terms_conditions)
    <div class="section-title">Terms &amp; Conditions</div>
    <p class="notes-text">{{ $workOrder->terms_conditions }}</p>
    @endif

    @if($workOrder->special_instructions)
    <div class="section-title">Special Instructions</div>
    <p class="notes-text">{{ $workOrder->special_instructions }}</p>
    @endif

    {{-- ── APPROVAL INFO ───────────────────────────────────────────────────── --}}
    @if($workOrder->approver)
    <div class="section-title">Approval Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Approved By</td>
            <td class="value">{{ $workOrder->approver->name }}</td>
            <td class="label">Approval Date</td>
            <td class="value">{{ $workOrder->approved_at ? $workOrder->approved_at->format(company()->date_format) : '--' }}</td>
        </tr>
    </table>
    @endif

    {{-- ── SIGNATURES ──────────────────────────────────────────────────────── --}}
    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line">Prepared By<br>{{ $workOrder->creator->name ?? '' }}</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">Approved By<br>{{ $workOrder->approver->name ?? '' }}</div>
        </div>
    </div>

</div>
</body>
</html>
