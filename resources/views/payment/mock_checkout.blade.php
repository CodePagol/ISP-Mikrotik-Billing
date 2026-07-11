<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Gateway Simulator') }} - {{ $gateway }}</title>
    <style>
        :root {
            --primary: #10B981;
            --primary-hover: #059669;
            --bg-dark: #0F172A;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
            radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.08) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10B981, #6366F1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 1rem;
            color: var(--text-muted);
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .bill-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            text-align: left;
        }

        .bill-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .bill-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .bill-row:first-child {
            padding-top: 0;
        }

        .bill-label {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .bill-value {
            font-weight: 600;
            color: var(--text-main);
        }

        .amount-highlight {
            font-size: 1.5rem;
            color: #10B981;
            font-weight: 800;
        }

        .alert-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #FBBF24;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.9rem;
            text-align: left;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #F87171;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #EF4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{{ $gateway }} {{ __('Sandbox') }}</div>
        <div class="subtitle">{{ __('Payment Simulator') }}</div>

        @if($reason)
            <div class="alert-box">
                <strong>{{ __('Gateway Bypass Redirected:') }}</strong> {{ $reason }}<br>
                {{ __('This simulator is loaded because local development hosts (.test/localhost) cannot receive incoming server callbacks or SSL verify requests.') }}
            </div>
        @else
            <div class="alert-box">
                {{ __('You are running on a local development host. Use this page to simulate a payment transaction result.') }}
            </div>
        @endif

        <div class="bill-card">
            <div class="bill-row">
                <span class="bill-label">{{ __('Customer ID') }}</span>
                <span class="bill-value">{{ $customer->customer_unique_id }}</span>
            </div>
            <div class="bill-row">
                <span class="bill-label">{{ __('Customer Name') }}</span>
                <span class="bill-value">{{ $customer->customer_name }}</span>
            </div>
            <div class="bill-row" style="align-items: center;">
                <span class="bill-label">{{ __('Amount Payable') }}</span>
                <span class="bill-value amount-highlight">BDT {{ number_format($amount, 2) }}</span>
            </div>
        </div>

        <form action="{{ route('payment.mock.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <input type="hidden" name="amount" value="{{ $amount }}">
            <input type="hidden" name="gateway" value="{{ $gateway }}">

            <div class="actions">
                <button type="submit" class="btn btn-success">{{ __('Simulate Successful Payment') }}</button>
                <a href="{{ route('filament.portal.pages.pay-bill') }}" class="btn btn-danger">{{ __('Simulate Cancel / Decline') }}</a>
            </div>
        </form>
    </div>
</body>
</html>
