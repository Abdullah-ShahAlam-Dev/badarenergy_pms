<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { max-width: 480px; width: 100%; border: none; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .card-header { background: #fff; border-bottom: 1px solid #e0e0e0; border-radius: 8px 8px 0 0 !important; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header text-center py-4">
            <h4 class="mb-1 font-weight-bold">Unsubscribe</h4>
            <p class="text-muted mb-0 f-14">You will no longer receive marketing emails at this address.</p>
        </div>
        <div class="card-body p-4">

            @if ($campaign)
                <p class="text-center text-muted mb-4">
                    You received this email as part of the campaign:<br>
                    <strong>{{ $campaign->name }}</strong>
                </p>
            @endif

            <div class="alert alert-light border text-center mb-4">
                <strong>{{ $email }}</strong>
            </div>

            <form action="{{ route('crm-email.unsubscribe') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                @if ($campaign)
                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                @endif

                <button type="submit" class="btn btn-danger btn-block">
                    Confirm Unsubscribe
                </button>
                <a href="/" class="btn btn-link btn-block text-center text-muted mt-2">
                    Never mind, take me back
                </a>
            </form>

        </div>
    </div>
</body>
</html>
