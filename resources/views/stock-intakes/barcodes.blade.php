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

        /* Container for each label */
        .barcode-label {
            width: 50mm;
            height: 30mm;
            padding: 2mm;
            box-sizing: border-box;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-after: always; /* Force print split per label */
        }

        /* SVG / Image barcode container */
        .barcode-svg {
            width: 100%;
            max-height: 14mm;
            display: flex;
            justify-content: center;
            margin-bottom: 1.5mm;
        }

        .barcode-svg svg {
            width: auto;
            height: 100%;
            max-width: 100%;
        }

        /* Content text styling */
        .serial-text {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .product-name {
            font-size: 8px;
            color: #555;
            margin: 0.5mm 0 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        /* Print Specific CSS Rules */
        @media print {
            body {
                background-color: #fff;
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
            <div class="barcode-svg">
                {!! $barcode['svg'] !!}
            </div>
            <div class="serial-text">
                {{ $barcode['serial_number'] }}
            </div>
            <div class="product-name">
                {{ $barcode['product_name'] }}
            </div>
        </div>
    @endforeach

</body>
</html>
