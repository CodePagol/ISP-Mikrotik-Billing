<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Account Expired') }} - {{ siteUrlSettings('site_name') }}</title>
    <!-- Fonts -->
    <style>
        :root {
            --primary: #f87171;
            --background: #0f172a;
            --surface: #1e293b;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: var(--background);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            background: var(--surface);
            padding: 3rem 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.6s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(248, 113, 113, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary);
        }

        .icon-container svg {
            width: 40px;
            height: 40px;
        }

        h1 {
            margin: 0 0 1rem;
            font-size: 1.75rem;
            font-weight: 700;
        }

        p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .action-button {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            width: calc(100% - 4rem);
            box-shadow: 0 4px 6px -1px rgba(248, 113, 113, 0.3);
        }

        .action-button:hover {
            background: #ef4444;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(248, 113, 113, 0.4);
        }

        .voucher-button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            width: calc(100% - 4rem);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
            margin-top: 1rem;
        }

        .voucher-button:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }

        .support-info {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .support-info strong {
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1>{{ __('Account Expired') }}</h1>
        <p>{{ __('Your internet subscription has expired. Please recharge your account or pay any outstanding dues to restore internet access. However, payment gateways remain accessible.') }}</p>
        
        <a href="{{ url('/portal') }}" class="action-button">{{ __('Go to Payment Portal') }}</a>
        <a href="{{ url('/recharge/voucher') }}" class="voucher-button">{{ __('Recharge via Voucher') }}</a>
        
        <div class="support-info">
            {{ __('Need help? Contact support at') }}<br>
            <strong>{{ siteUrlSettings('site_phone') ?? '+880 123 456 789' }}</strong>
        </div>
    </div>
</body>
</html>
