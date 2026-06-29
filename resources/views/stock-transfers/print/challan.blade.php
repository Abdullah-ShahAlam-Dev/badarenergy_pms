<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>WMS Stock Transfer Challan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; color: #111; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #555; text-transform: uppercase; font-size: 11px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { bg-color: #f5f5f5; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .signatures { width: 100%; margin-top: 50px; }
        .signatures td { width: 33%; text-align: center; padding-top: 50px; border-top: 1px dashed #999; }
        .barcode-placeholder { border: 1px solid #aaa; padding: 10px; display: inline-block; font-family: monospace; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Budar Energy PMS</h2>
        <div style="font-size: 16px; margin-top: 5px; font-weight: bold; letter-spacing: 1px;">Goods Dispatch Challan (Gate Pass)</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="50%">
                <div><span class="label">Source Warehouse:</span> Head Office Warehouse</div>
                <div style="margin-top: 5px;"><span class="label">Destination Warehouse:</span> Saddar Outlet</div>
                <div style="margin-top: 5px;"><span class="label">Dispatch Date:</span> 2026-06-28 01:15</div>
            </td>
            <td width="50%" style="text-align: right;">
                <div><span class="label">Challan Number:</span> CHN-20260628-01</div>
                <div style="margin-top: 5px;"><span class="label">Vehicle No:</span> LHR-9988</div>
                <div style="margin-top: 5px;"><span class="label">Driver Name:</span> Muhammad Ali</div>
                <div style="margin-top: 10px;">
                    <div class="barcode-placeholder">*TRF-2026-0001*</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="30%">Product Model</th>
                <th width="50%">Serial Numbers</th>
                <th width="20%" style="text-align: right;">Quantity</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Budar Premium 12V 100AH</td>
                <td>SN-10029384, SN-10029385</td>
                <td style="text-align: right;">2.00</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
        <span class="label">Dispatcher Remarks:</span> Handed over to courier driver for immediate dispatch.
    </div>

    <table class="signatures">
        <tr>
            <td>Prepared By</td>
            <td>Warehouse In-Charge Signature</td>
            <td>Driver Signature</td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 40px;" class="no-print">
        <button onclick="window.print();" style="padding: 10px 20px; font-size: 14px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Challan</button>
    </div>
</body>
</html>
