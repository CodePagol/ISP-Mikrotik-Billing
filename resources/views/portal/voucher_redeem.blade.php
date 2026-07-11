<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Recharge via Voucher') }} - {{ siteUrlSettings('site_name') }}</title>
    <!-- Fonts -->
    <style>
        :root {
            --primary: #10b981;
            --background: #0f172a;
            --surface: #1e293b;
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --danger: #ef4444;
            --success: #10b981;
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
            background: rgba(16, 185, 129, 0.1);
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
            margin: 0 0 0.5rem;
            font-size: 1.75rem;
            font-weight: 700;
            text-align: center;
        }

        p.subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        input {
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.5);
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .action-button {
            display: block;
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
            margin-top: 2rem;
        }

        .action-button:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }

        .footer-links {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
        }

        .footer-links a {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
        </div>
        
        <h1>{{ __('Voucher Recharge') }}</h1>
        <p class="subtitle">{{ __('Enter your connection username or Customer ID along with the voucher code to instantly recharge your account.') }}</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('portal.voucher.redeem') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="username">{{ __('PPPoE Username / Customer ID') }}</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required placeholder="e.g. john_pppoe">
            </div>

            <div class="form-group">
                <label for="code">{{ __('Voucher Code') }}</label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" required placeholder="VCH-XXXXXX-XXXXXX" style="text-transform: uppercase;">
            </div>

            <button type="submit" class="action-button">{{ __('Recharge Account Now') }}</button>
        </form>

        <div class="footer-links">
            {{ __('Want to pay online?') }} <a href="{{ url('/') }}">{{ __('Go to Payment Portal') }}</a>
        </div>
    </div>
</body>
</html>
