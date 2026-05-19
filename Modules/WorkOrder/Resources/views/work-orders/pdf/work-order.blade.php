<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order — {{ $workOrder->wo_number }}</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .page { padding: 30px; }
        /* Header */
        .header { display: table; width: 100%; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left { display: table-cell; width: 60%; vertical-align: middle; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: middle; }
        .company-logo { max-height: 70px; max-width: 200px; }
        .company-name { font-size: 16px; font-weight: bold; }
        .company-info { font-size: 10px; color: #666; }
        .wo-title { font-size: 22px; font-weight: bold; color: #1a1a2e; }
        .wo-number { font-size: 14px; color: #666; margin-top: 4px; }
        /* Info Section */
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px 8px; font-size: 11px; }
        .info-table .label { color: #888; width: 30%; }
        .info-table .value { font-weight: bold; }
        /* Vendor Box */
        .vendor-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 12px; margin-bottom: 20px; }
        .vendor-box h4 { font-size: 12px; color: #888; margin-bottom: 6px; text-transform: uppercase; }
        .vendor-box p { margin: 2px 0; font-size: 12px; }
        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #1a1a2e; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        .items-table td { padding: 7px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table tr:nth-child(even) td { background: #f9f9f9; }
        .items-table .text-right { text-align: right; }
        /* Totals */
        .totals-table { width: 40%; margin-left: auto; border-collapse: collapse; margin-bottom: 20px; }
        .totals-table td { padding: 5px 10px; font-size: 12px; }
        .totals-table .grand-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #333; padding-top: 8px; }
        /* Notes */
        .section-title { font-size: 12px; font-weight: bold; color: #1a1a2e; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; margin-top: 15px; }
        .notes-text { font-size: 11px; color: #555; line-height: 1.5; }
        /* Signatures */
        .signatures { display: table; width: 100%; margin-top: 40px; }
        .sig-cell { display: table-cell; width: 33%; text-align: center; padding: 0 10px; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 10px; color: #666; }
        /* Status badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-draft { background: #e2e3e5; color: #383d41; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            @if($company->logo)
                @if(file_exists(public_path('user-uploads/app-logo/' . $company->logo)))
                    <img src="{{ public_path('user-uploads/app-logo/' . $company->logo) }}" class="company-logo" alt="Logo">
                @else
                    <img src="{{ $company->logo_url }}" class="company-logo" alt="Logo">
                @endif
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
            <td class="label">Delivery Date</td>
            <td class="value">{{ $workOrder->delivery_date ? $workOrder->delivery_date->format(company()->date_format) : '--' }}</td>
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
                <th class="text-right">Qty</th>
                <th>Unit</th>
                <th class="text-right">Rate</th>
                <th>Tax Type</th>
                <th class="text-right">Tax %</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workOrder->items as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td>{{ $item->unit ?? '--' }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td>{{ ucfirst($item->tax_type) }}</td>
                <td class="text-right">{{ $item->tax_percent }}%</td>
                <td class="text-right"><strong>{{ number_format($item->total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ──────────────────────────────────────────────────────────── --}}
    <table class="totals-table">
        <tr><td>Sub Total</td><td class="text-right">{{ number_format($workOrder->sub_total, 2) }}</td></tr>
        <tr><td>Discount ({{ $workOrder->discount_type == 'percent' ? $workOrder->discount.'%' : 'Fixed' }})</td><td class="text-right">-{{ number_format($workOrder->discount_type == 'percent' ? $workOrder->sub_total * ($workOrder->discount / 100) : $workOrder->discount, 2) }}</td></tr>
        <tr><td>Tax Amount</td><td class="text-right">{{ number_format($workOrder->tax_amount, 2) }}</td></tr>
        <tr class="grand-row"><td>Grand Total</td><td class="text-right">{{ number_format($workOrder->grand_total, 2) }}</td></tr>
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
        <div class="sig-cell">
            <div class="sig-line">Authorized Signatory</div>
        </div>
    </div>

</div>
</body>
</html>
