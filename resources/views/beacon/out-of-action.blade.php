<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beacon — Out of Service</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #e4e4e7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            max-width: 480px;
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            opacity: 0.4;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 1rem;
        }
        p {
            color: #a1a1aa;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        a {
            display: inline-block;
            color: #0a0a0a;
            background: #E7FF57;
            padding: 0.75rem 2rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: filter 0.2s;
        }
        a:hover { filter: brightness(0.9); }
    </style>
</head>
<body>
    <div class="container">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <h1>Out of Service</h1>
        <p>{{ $message ?? 'This beacon is currently not available. Please try again later.' }}</p>
        <a href="/">Go to Homepage</a>
    </div>
</body>
</html>
