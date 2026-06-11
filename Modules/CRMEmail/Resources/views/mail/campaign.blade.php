<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f4;
            padding: 20px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .email-body {
            padding: 30px 40px;
            line-height: 1.6;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 20px 40px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 12px;
            color: #888888;
        }
        .email-footer a {
            color: #888888;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-body">
                {!! $htmlBody !!}
            </div>
            <div class="email-footer">
                @if (!empty($unsubscribeUrl))
                    <p>
                        You received this email because you are a contact in our system.<br>
                        If you no longer wish to receive emails, you may
                        <a href="{{ $unsubscribeUrl }}">unsubscribe here</a>.
                    </p>
                @endif
                <p>&copy; {{ date('Y') }} All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
