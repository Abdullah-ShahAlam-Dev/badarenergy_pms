<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { max-width: 480px; width: 100%; border: none; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; background: #d4edda; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body p-5 text-center">
            <div class="icon-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#28a745" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>

            <h4 class="font-weight-bold mb-2">You've been unsubscribed</h4>
            <p class="text-muted mb-4">
                The email address <strong>{{ $email }}</strong> has been removed from our marketing list.<br>
                You will not receive future marketing emails.
            </p>

            <a href="/" class="btn btn-secondary btn-sm">Return to Homepage</a>
        </div>
    </div>
</body>
</html>
