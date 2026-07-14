<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Barcode Labels - {{ $voucher_number }}</title>
    <style>
        /* CSS Reset and Font definition */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #fff;
            color: #000;
        }

        /* Container for each label - standard 100mm x 35mm layout */
        .barcode-label {
            width: 100mm;
            height: 35mm;
            padding: 2mm 3mm;
            box-sizing: border-box;
            background: #fff;
            border: 1px dashed #ccc;
            margin: 5px auto;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            page-break-after: always; /* Force print split per label */
        }

        /* QR Code container on the left */
        .qr-section {
            display: flex;
            align-items: center;
            height: 100%;
            width: 28%;
            border-right: 1px dashed #ddd;
            padding-right: 2mm;
        }

        .qr-code-svg {
            width: 22mm;
            height: 22mm;
        }

        .qr-code-svg svg {
            width: 100%;
            height: 100%;
        }

        .qr-label-text {
            font-size: 8px;
            font-weight: bold;
            color: #333;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            margin-left: 1mm;
            letter-spacing: 0.5px;
        }

        /* Linear Barcode and details section on the right */
        .barcode-section {
            width: 70%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-left: 2mm;
        }

        .serial-text {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 1.5mm 0 0 0;
        }

        .barcode-svg {
            width: 100%;
            height: 12mm;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .barcode-svg svg {
            width: 100%;
            height: 100%;
            max-width: 100%;
        }

        .model-text {
            font-size: 9px;
            font-weight: bold;
            color: #000;
            margin: 0 0 1.5mm 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Print Specific CSS Rules */
        @media print {
            body {
                background-color: #fff;
            }
            .barcode-label {
                border: none;
                margin: 0;
                page-break-after: always;
            }
            .barcode-label:last-child {
                page-break-after: avoid; /* Don't add blank page at end */
            }
        }
    </style>
</head>
<body onload="window.print()">

    @foreach ($barcodes as $barcode)
        <div class="barcode-label">
            <!-- Left side: T&C QR Code and Vertical Text label -->
            <div class="qr-section">
                <div class="qr-code-svg">
                    {!! $barcode['qrcode_svg'] !!}
                </div>
                <div class="qr-label-text">
                    Terms & Conditions
                </div>
            </div>

            <!-- Right side: Serial and Linear Barcode -->
            <div class="barcode-section">
                <!-- Product Name/Model above Barcode -->
                <div class="model-text">
                    Model: {{ $barcode['product_name'] }}
                </div>
                
                <!-- Linear Barcode SVG -->
                <div class="barcode-svg">
                    {!! $barcode['barcode_svg'] !!}
                </div>

                <!-- Serial Text below Barcode -->
                <div class="serial-text">
                    {{ $barcode['serial_number'] }}
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>
