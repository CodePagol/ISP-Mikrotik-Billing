<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') – {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            padding: 2rem;
        }

        .container {
            text-align: center;
            max-width: 620px;
            width: 100%;
        }

        .icon {
            font-size: 5rem;
            margin-bottom: 1.25rem;
            display: block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }

        .badge {
            display: inline-block;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid #f59e0b;
            color: #f59e0b;
            padding: 0.3rem 1.1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .message {
            font-size: 1.05rem;
            line-height: 1.75;
            color: #cbd5e1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            margin-top: 0.5rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.7rem 2rem;
            background: linear-gradient(90deg, #f59e0b, #d97706);
            color: #1a1a2e;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 999px;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .back-link:hover { opacity: 0.85; }
    </style>
</head>
<body>
    <div class="container">
        <span class="icon">@yield('icon', '⚠️')</span>
        <div class="badge">@yield('code', '500') – @yield('title', 'Error')</div>
        <h1>@yield('heading', 'Something went wrong')</h1>
        <p class="message">@yield('message', 'An unexpected error occurred. Please try again or contact support.')</p>
        <a href="/" class="back-link">← Go Back Home</a>
    </div>
</body>
</html>
