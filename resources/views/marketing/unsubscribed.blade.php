<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribed — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #14161a;
            color: #f4f4f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }
        .card {
            background-color: #1c1e24;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 40px 32px;
            max-width: 420px;
            text-align: center;
        }
        h1 { font-size: 20px; margin: 0 0 12px; }
        p { color: #a1a1aa; line-height: 1.5; margin: 0 0 24px; }
        a {
            display: inline-block;
            background-color: #e50914;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 22px;
            border-radius: 999px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>You've been unsubscribed</h1>
        <p>You won't receive any more marketing emails from {{ config('app.name') }}. You'll still get important account and order notifications.</p>
        <a href="{{ route('home') }}">Back to {{ config('app.name') }}</a>
    </div>
</body>
</html>
