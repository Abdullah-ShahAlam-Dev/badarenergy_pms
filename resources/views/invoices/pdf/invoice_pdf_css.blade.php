<style>
    @if($invoiceSetting->locale == 'th')
    @font-face {
        font-family: 'THSarabunNew';
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'THSarabunNew';
        font-style: normal;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Bold.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'THSarabunNew';
        font-style: italic;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Bold_Italic.ttf') }}") format('truetype');
    }
    @font-face {
        font-family: 'THSarabunNew';
        font-style: italic;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Italic.ttf') }}") format('truetype');
    }
    @endif

    @if($invoiceSetting->locale == 'vi')
    @font-face {
            font-family: 'BeVietnamPro';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/BeVietnamPro-Black.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'BeVietnamPro';
            font-style: italic;
            font-weight: normal;
            src: url("{{ storage_path('fonts/BeVietnamPro-BlackItalic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'BeVietnamPro';
            font-style: italic;
            font-weight: bold;
            src: url("{{ storage_path('fonts/BeVietnamPro-bold.ttf') }}") format('truetype');
        }

        @endif

    @if($invoiceSetting->is_chinese_lang)

@font-face {
        font-family: SimHei;
        /*font-style: normal;*/
        font-weight: bold;
        src: url('{{ asset('fonts/simhei.ttf') }}') format('truetype');
    }

    @endif

        @php
            $font = '';
            if($invoiceSetting->locale == 'ja') {
                $font = 'ipag';
            } else if($invoiceSetting->locale == 'hi') {
                $font = 'hindi';
            } else if($invoiceSetting->locale == 'th') {
                $font = 'THSarabunNew';
            } else if($invoiceSetting->is_chinese_lang) {
                $font = 'SimHei';
            }
            else if($invoiceSetting->locale == 'vi') {
                $font = 'BeVietnamPro';
            }
            else {
                $font = 'Verdana';
            }
        @endphp

        @if($invoiceSetting->is_chinese_lang)
            body {
        font-weight: normal !important;
    }

    @endif
        * {
        font-family: DejaVu Sans, {{$font}}, sans-serif;
    }
</style>

@if(isset($printView) && $printView)
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        .print-button-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99999;
            text-align: center;
        }
        .print-btn {
            padding: 12px 28px;
            font-size: 15px;
            font-weight: bold;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .print-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            const container = document.createElement('div');
            container.className = 'print-button-container no-print';
            container.innerHTML = '<button class="print-btn" onclick="window.print();">Print Now</button>';
            document.body.appendChild(container);
            
            // Auto trigger browser print window
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
@endif
