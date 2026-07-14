<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Delivery Order - {{ $deliveryOrder->delivery_order_number }}</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ global_setting()->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <style>
        body {
            margin: 0;
            font-size: 13px;
        }

        .bg-grey {
            background-color: #F2F4F7;
        }

        .bg-white {
            background-color: #fff;
        }

        .border-radius-25 {
            border-radius: 0.25rem;
        }

        .p-25 {
            padding: 1.25rem;
        }

        .f-11 {
            font-size: 11px;
        }

        .f-13 {
            font-size: 13px;
        }

        .f-14 {
            font-size: 13px;
        }

        .f-15 {
            font-size: 13px;
        }

        .f-21 {
            font-size: 17px;
        }

        .text-black {
            color: #28313c;
        }

        .text-grey {
            color: #616e80;
        }

        .font-weight-700 {
            font-weight: 700;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-capitalize {
            text-transform: capitalize;
        }

        .line-height {
            line-height: 20px;
        }

        .mt-1 {
            margin-top: 1rem;
        }

        .mb-0 {
            margin-bottom: 0px;
        }

        .b-collapse {
            border-collapse: collapse;
        }

        .heading-table-left {
            padding: 6px;
            border: 1px solid #DBDBDB;
            font-weight: bold;
            background-color: #f1f1f3;
            border-right: 0;
        }

        .heading-table-right {
            padding: 6px;
            border: 1px solid #DBDBDB;
            border-left: 0;
        }

        .unpaid {
            color: #000000;
            border: 1px solid #000000;
            position: relative;
            padding: 11px 22px;
            font-size: 14px;
            border-radius: 0.25rem;
            width: 120px;
            text-align: center;
            margin-top: 50px;
        }

        .main-table-heading {
            border: 1px solid #DBDBDB;
            background-color: #f1f1f3;
            font-weight: 700;
        }

        .main-table-heading td, .main-table-heading th {
            padding: 5px 8px;
            border: 1px solid #DBDBDB;
        }

        .main-table-items td {
            padding: 5px 8px;
            border: 1px solid #e7e9eb;
        }

        .centered {
            margin: 0 auto;
        }

        .rightaligned {
            margin-right: 0;
            margin-left: auto;
        }

        .leftaligned {
            margin-left: 0;
            margin-right: auto;
        }

        .word-break {
            max-width:175px;
            word-wrap:break-word;
        }

        .border-left-0 {
            border-left: 0 !important;
        }

        .border-right-0 {
            border-right: 0 !important;
        }

        .border-top-0 {
            border-top: 0 !important;
        }

        .border-bottom-0 {
            border-bottom: 0 !important;
        }

        .signatures {
            margin-top: 60px;
            width: 100%;
        }

        .sig-box {
            text-align: center;
            vertical-align: bottom;
            padding: 10px;
        }

        .sig-line {
            border-top: 1px solid #28313c;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
        }

    </style>
</head>

<body class="content-wrapper">
    <table class="bg-white" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tbody>
            <!-- Table Row Start -->
            <tr>
                <td><img src="{{ company()->logo_url }}" alt="{{ mb_ucwords(company()->company_name) }}"
                        style="height: 50px;" /></td>
                <td align="right" class="f-21 text-black font-weight-700 text-uppercase">DELIVERY ORDER</td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td>
                    <p class="line-height mt-1 mb-0 f-14 text-black">
                        {{ mb_ucwords(company()->company_name) }}<br>
                        {{ company()->address }}<br>
                        {{ company()->company_phone }}<br>
                    </p>
                </td>
                <td>
                    <table class="text-black mt-1 f-13 b-collapse rightaligned">
                        <tr>
                            <td class="heading-table-left">DO Number</td>
                            <td class="heading-table-right">{{ $deliveryOrder->delivery_order_number }}</td>
                        </tr>
                        <tr>
                            <td class="heading-table-left">DO Date</td>
                            <td class="heading-table-right">{{ $deliveryOrder->issue_date->format(company()->date_format) }}</td>
                        </tr>
                        @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
                            <tr>
                                <td class="heading-table-left">Transfer No.</td>
                                <td class="heading-table-right">{{ $deliveryOrder->stockTransfer->transfer_number }}</td>
                            </tr>
                        @elseif($deliveryOrder->invoice)
                            <tr>
                                <td class="heading-table-left">Invoice No.</td>
                                <td class="heading-table-right">{{ $deliveryOrder->invoice->invoice_number }}</td>
                            </tr>
                        @endif
                        @if($deliveryOrder->dispatcher)
                            <tr>
                                <td class="heading-table-left">Dispatcher</td>
                                <td class="heading-table-right">{{ $deliveryOrder->dispatcher->name }}</td>
                            </tr>
                        @endif
                        @php
                            $drvName = $deliveryOrder->driver_name ?: ($deliveryOrder->stockTransfer->driver_name ?? null);
                            $vehNo = $deliveryOrder->vehicle_number ?: ($deliveryOrder->stockTransfer->vehicle_number ?? null);
                        @endphp
                        @if($drvName)
                            <tr>
                                <td class="heading-table-left">Driver Name</td>
                                <td class="heading-table-right">{{ $drvName }}</td>
                            </tr>
                        @endif
                        @if($vehNo)
                            <tr>
                                <td class="heading-table-left">Vehicle No.</td>
                                <td class="heading-table-right">{{ $vehNo }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td height="10"></td>
            </tr>
            <!-- Table Row End -->
            <!-- Table Row Start -->
            <tr>
                <td colspan="2">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="f-14 text-black" valign="top">
                                @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
                                    <p class="line-height mb-0">
                                        <span class="text-grey text-capitalize">Destination Warehouse</span><br>
                                        <strong>{{ $deliveryOrder->stockTransfer->destinationWarehouse->name }}</strong><br>
                                        {!! nl2br($deliveryOrder->stockTransfer->destinationWarehouse->address ?? '--') !!}
                                    </p>
                                @else
                                    @php
                                        $client = $deliveryOrder->invoice->client ?? $deliveryOrder->order->client ?? null;
                                    @endphp
                                    @if($client)
                                        <p class="line-height mb-0">
                                            <span class="text-grey text-capitalize">@lang("modules.invoices.billedTo")</span><br>
                                            <strong>{{ mb_ucwords($client->name) }}</strong><br>
                                            @if($client->clientDetails)
                                                {!! nl2br($client->clientDetails->address) !!}<br>
                                                {{ $client->clientDetails->city }} ({{ $client->clientDetails->area }})<br>
                                            @endif
                                            {{ $client->mobile ?? ($client->clientDetails->cell ?? '--') }}
                                        </p>
                                    @endif
                                @endif
                            </td>
                            <td class="f-14 text-black" valign="top">
                                @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
                                    <p class="line-height">
                                        <span class="text-grey text-capitalize">Source Warehouse</span><br>
                                        <strong>{{ $deliveryOrder->stockTransfer->sourceWarehouse->name }}</strong>
                                    </p>
                                @else
                                    @php
                                        $orderSource = $deliveryOrder->order ?? ($deliveryOrder->invoice ? $deliveryOrder->invoice->order : null);
                                    @endphp
                                    @if ($orderSource && $orderSource->show_shipping_address == 'yes' && $client && $client->clientDetails->shipping_address)
                                        <p class="line-height"><span
                                                class="text-grey text-capitalize">@lang('app.shippingAddress')</span><br>
                                            {!! nl2br($client->clientDetails->shipping_address) !!}</p>
                                    @endif
                                @endif
                            </td>
                            <td align="right" valign="top">
                                <br />
                                <div class="text-uppercase bg-white unpaid rightaligned" style="border: 1px solid #000; width: 130px; margin-top: 10px;">
                                    {{ $deliveryOrder->status }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table width="100%" class="f-14 b-collapse" style="margin-top: 20px;">
        <thead>
            <tr class="main-table-heading text-grey">
                <td width="5%">#</td>
                <td width="55%">Product / Model</td>
                <th width="15%" class="qty" align="right">Qty Ordered</th>
                <td width="25%" align="right">Serials Dispatched</td>
            </tr>
        </thead>
        <tbody>
            @if($deliveryOrder->source_type === 'transfer' && $deliveryOrder->stockTransfer)
                @foreach ($deliveryOrder->stockTransfer->items as $index => $item)
                    @if($item->product_id)
                        @php
                            $serials = $item->serials->map(fn($ts) => $ts->serial->serial_number)->toArray();
                        @endphp
                        <tr class="main-table-items text-black">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->description)
                                    <br><small style="color: #616e80;">{{ $item->product->description }}</small>
                                @endif
                            </td>
                            <td align="right">{{ (int)$item->quantity }}</td>
                            <td align="right">
                                @if(!empty($serials))
                                    {{ implode(', ', $serials) }}
                                @else
                                    <span class="text-grey" style="font-style: italic;">Non-serialized</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @elseif($deliveryOrder->source_type === 'order' && !$deliveryOrder->invoice_id && $deliveryOrder->order)
                @foreach ($deliveryOrder->order->items as $index => $item)
                    @if($item->product_id)
                        <tr class="main-table-items text-black">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->description)
                                    <br><small style="color: #616e80;">{{ $item->product->description }}</small>
                                @endif
                            </td>
                            <td align="right">{{ (int)$item->quantity }}</td>
                            <td align="right">
                                <span class="text-grey" style="font-style: italic;">Pending Invoice & Dispatch</span>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @elseif($deliveryOrder->invoice)
                @foreach ($deliveryOrder->invoice->items as $index => $item)
                    @if($item->product_id)
                        @php
                            $serials = \App\Models\ProductSerial::where('invoice_id', $deliveryOrder->invoice_id)
                                ->where('product_id', $item->product_id)
                                ->pluck('serial_number')
                                ->toArray();
                        @endphp
                        <tr class="main-table-items text-black">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->product->name }}</strong>
                                @if($item->product->description)
                                    <br><small style="color: #616e80;">{{ $item->product->description }}</small>
                                @endif
                            </td>
                            <td align="right">{{ (int)$item->quantity }}</td>
                            <td align="right">
                                @if(!empty($serials))
                                    {{ implode(', ', $serials) }}
                                @else
                                    <span class="text-grey" style="font-style: italic;">Non-serialized</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <table class="signatures" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td class="sig-box" width="33%">
                <div class="sig-line">Prepared By (Warehouse Officer)</div>
            </td>
            <td class="sig-box" width="33%">
                <div class="sig-line">Dispatcher: {{ $deliveryOrder->dispatcher->name ?? 'Carrier/Driver' }}</div>
            </td>
            <td class="sig-box" width="33%">
                <div class="sig-line">Receiver Signature & Stamp</div>
            </td>
        </tr>
    </table>

</body>
</html>
