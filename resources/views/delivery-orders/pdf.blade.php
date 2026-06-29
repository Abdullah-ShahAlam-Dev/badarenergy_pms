<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Order #DO-{{ $deliveryOrder->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 40px;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 28px;
            font-weight: 700;
        }
        .logo-section p {
            margin: 5px 0 0 0;
            color: #6b7280;
        }
        .do-details {
            text-align: right;
        }
        .do-details h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 24px;
        }
        .do-details p {
            margin: 5px 0 0 0;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        .info-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1e3a8a;
            font-size: 15px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        .info-box p {
            margin: 4px 0;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f1f5f9;
            color: #1e3a8a;
            font-weight: 600;
        }
        .serial-list {
            font-family: monospace;
            background: #f8fafc;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #0f172a;
            display: inline-block;
            margin-top: 5px;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            gap: 40px;
        }
        .sig-box {
            flex: 1;
            text-align: center;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            color: #475569;
        }
        .no-print-btn {
            background-color: #3b82f6;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background 0.2s;
        }
        .no-print-btn:hover {
            background-color: #2563eb;
        }
        @media print {
            .no-print-btn {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <button class="no-print-btn" onclick="window.print()">Print Delivery Order</button>

    <div class="header">
        <div class="logo-section">
            <h1>{{ company()->company_name }}</h1>
            <p>{{ company()->address }}</p>
        </div>
        <div class="do-details">
            <h2>DELIVERY ORDER</h2>
            <p><strong>DO Number:</strong> #DO-{{ $deliveryOrder->id }}</p>
            <p><strong>Date:</strong> {{ $deliveryOrder->issue_date->format(company()->date_format) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($deliveryOrder->status) }}</p>
        </div>
    </div>

    <div class="info-grid">
        @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
            <div class="info-box">
                <h3>Destination / Recipient details</h3>
                <p><strong>Warehouse Name:</strong> {{ $deliveryOrder->stockTransfer->destinationWarehouse->name }}</p>
                <p><strong>Address:</strong> {{ $deliveryOrder->stockTransfer->destinationWarehouse->address ?? '--' }}</p>
                <p><strong>Driver Name:</strong> {{ $deliveryOrder->driver_name ?? $deliveryOrder->stockTransfer->driver_name ?? '--' }}</p>
                <p><strong>Vehicle Number:</strong> {{ $deliveryOrder->vehicle_number ?? $deliveryOrder->stockTransfer->vehicle_number ?? '--' }}</p>
            </div>

            <div class="info-box">
                <h3>Reference & Logistics</h3>
                <p><strong>Transfer Number:</strong> {{ $deliveryOrder->stockTransfer->transfer_number }}</p>
                <p><strong>Source Warehouse:</strong> {{ $deliveryOrder->stockTransfer->sourceWarehouse->name }}</p>
                <p><strong>Assigned Dispatcher:</strong> {{ $deliveryOrder->dispatcher->name ?? 'Not Assigned' }}</p>
            </div>
        @else
            <div class="info-box">
                <h3>Customer / Dealer Details</h3>
                <p><strong>Name:</strong> {{ $deliveryOrder->invoice->client->name ?? 'N/A' }}</p>
                @if ($deliveryOrder->invoice && $deliveryOrder->invoice->client && $deliveryOrder->invoice->client->clientDetails)
                    <p><strong>Contact Person:</strong> {{ $deliveryOrder->invoice->client->clientDetails->contact_relation ?? $deliveryOrder->invoice->client->name }}</p>
                    <p><strong>Address:</strong> {{ $deliveryOrder->invoice->client->clientDetails->address }}</p>
                    <p><strong>City:</strong> {{ $deliveryOrder->invoice->client->clientDetails->city }} ({{ $deliveryOrder->invoice->client->clientDetails->area }})</p>
                    <p><strong>Phone:</strong> {{ $deliveryOrder->invoice->client->mobile ?? $deliveryOrder->invoice->client->clientDetails->cell ?? '--' }}</p>
                @endif
            </div>

            <div class="info-box">
                <h3>Reference & Logistics</h3>
                <p><strong>Invoice Number:</strong> {{ $deliveryOrder->invoice->invoice_number ?? 'N/A' }}</p>
                <p><strong>Invoice Date:</strong> {{ $deliveryOrder->invoice ? $deliveryOrder->invoice->issue_date->format(company()->date_format) : '--' }}</p>
                <p><strong>Warehouse:</strong> {{ $deliveryOrder->invoice->warehouse->name ?? '--' }}</p>
                <p><strong>Assigned Dispatcher:</strong> {{ $deliveryOrder->dispatcher->name ?? 'Not Assigned' }}</p>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 55%;">Product / Model</th>
                <th style="width: 15%;">Qty Ordered</th>
                <th style="width: 25%;">Serials Dispatched</th>
            </tr>
        </thead>
        <tbody>
            @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
                @foreach ($deliveryOrder->stockTransfer->items as $index => $item)
                    @if($item->product_id)
                        @php
                            $serials = $item->serials->map(fn($ts) => $ts->serial->serial_number)->toArray();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->description)
                                    <br><small style="color: #64748b;">{{ $item->product->description }}</small>
                                @endif
                            </td>
                            <td>{{ (int)$item->quantity }}</td>
                            <td>
                                @if(!empty($serials))
                                    <div class="serial-list">
                                        {{ implode(', ', $serials) }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Non-serialized</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @else
                @foreach ($deliveryOrder->invoice->items as $index => $item)
                    @if($item->product_id)
                        @php
                            $serials = \App\Models\ProductSerial::where('invoice_id', $deliveryOrder->invoice_id)
                                ->where('product_id', $item->product_id)
                                ->pluck('serial_number')
                                ->toArray();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->description)
                                    <br><small style="color: #64748b;">{{ $item->product->description }}</small>
                                @endif
                            </td>
                            <td>{{ (int)$item->quantity }}</td>
                            <td>
                                @if(!empty($serials))
                                    <div class="serial-list">
                                        {{ implode(', ', $serials) }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Non-serialized</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-box">
            <p>Prepared By</p>
            <div style="height: 60px;"></div>
            <strong>Warehouse Officer</strong>
        </div>
        <div class="sig-box">
            <p>Dispatcher Signature</p>
            <div style="height: 60px;"></div>
            <strong>{{ $deliveryOrder->dispatcher->name ?? 'Carrier/Driver' }}</strong>
        </div>
        <div class="sig-box">
            <p>Receiver Signature & Stamp</p>
            <div style="height: 60px;"></div>
            <strong>Customer / Representative</strong>
        </div>
    </div>

</body>
</html>
