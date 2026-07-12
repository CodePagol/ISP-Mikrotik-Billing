<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ siteUrlSettings('site_name') ?? config('app.name') }}</title>
    <!-- Local Assets via Vite -->
    @vite(['resources/sass/app.scss'])
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #090d16 0%, #0f172a 100%);
            --card-bg: rgba(30, 41, 59, 0.45);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6; /* Electric blue */
            --primary-glow: rgba(59, 130, 246, 0.15);
            --accent: #f59e0b; /* Warm amber for maintenance */
            --accent-glow: rgba(245, 158, 11, 0.15);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-12px) scale(1.02); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.15); }
        }

        @keyframes borderGlow {
            0%, 100% { border-color: rgba(59, 130, 246, 0.2); }
            50% { border-color: rgba(245, 158, 11, 0.4); }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-main);
            padding: 1.5rem;
            overflow-x: hidden;
            position: relative;
        }

        /* Beautiful glowing background blobs */
        .blob {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter: blur(40px);
            z-index: 0;
        }
        .blob-1 {
            top: -100px;
            left: -100px;
        }
        .blob-2 {
            bottom: -150px;
            right: -100px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
        }

        .container {
            position: relative;
            z-index: 10;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 2rem;
            padding: 3rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: borderGlow 6s ease-in-out infinite;
        }

        .logo-area {
            margin-bottom: 2rem;
        }

        .logo-img {
            max-height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .visual-indicator {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            margin-bottom: 1.5rem;
        }

        .visual-icon-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: {{ $is_maintenance ? 'var(--accent-glow)' : 'var(--primary-glow)' }};
            animation: float 4s ease-in-out infinite;
        }

        .visual-icon {
            font-size: 3rem;
            color: {{ $is_maintenance ? 'var(--accent)' : 'var(--primary)' }};
            z-index: 2;
            animation: float 4s ease-in-out infinite;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: {{ $is_maintenance ? 'var(--accent)' : 'var(--primary)' }};
            margin-bottom: 1.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: {{ $is_maintenance ? 'var(--accent)' : 'var(--primary)' }};
            box-shadow: 0 0 10px {{ $is_maintenance ? 'var(--accent)' : 'var(--primary)' }};
            animation: pulse 2s infinite;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.025em;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .message-box {
            background: rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1.25rem;
            padding: 1.5rem;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            text-align: left;
        }

        .contact-section {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 2rem;
        }

        .contact-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.25rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 480px) {
            .contact-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1rem;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            text-align: left;
        }

        .contact-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .contact-card i {
            font-size: 1.25rem;
            color: var(--primary);
        }

        .contact-card .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: block;
        }

        .contact-card .value {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .social-row {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-glow);
            border-color: var(--primary);
            color: var(--text-main);
            transform: translateY(-2px);
        }

        .footer-copyright {
            margin-top: 3rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="container">
        <div class="card">
            <!-- Brand Identity logo -->
            <div class="logo-area">
                @if (siteUrlSettings('site_logo'))
                    <img class="logo-img" src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="{{ siteUrlSettings('site_name') ?? 'Logo' }}">
                @else
                    <span class="logo-text">{{ siteUrlSettings('site_name') ?? config('app.name') }}</span>
                @endif
            </div>

            <!-- Status Indicator -->
            <div>
                <div class="visual-indicator">
                    <div class="visual-icon-bg"></div>
                    <i class="bi {{ $is_maintenance ? 'bi-wrench-adjustable-circle' : 'bi-lock' }} visual-icon"></i>
                </div>
            </div>

            <div class="status-badge">
                <span class="status-dot"></span>
                {{ $title }}
            </div>

            <!-- Main Heading -->
            <h1>{{ $heading }}</h1>

            <!-- Announcement Content -->
            <div class="message-box">
                {{ $message }}
            </div>

            <!-- Contact Section -->
            @if (siteUrlSettings('site_phone') || siteUrlSettings('site_email'))
                <div class="contact-section">
                    <h2 class="contact-title">Contact Support</h2>
                    
                    <div class="contact-grid">
                        @if (siteUrlSettings('site_phone'))
                            <a href="tel:{{ siteUrlSettings('site_phone') }}" class="contact-card">
                                <i class="bi bi-telephone-fill" style="color: var(--accent);"></i>
                                <div>
                                    <span class="label">Call Helpline</span>
                                    <span class="value">{{ siteUrlSettings('site_phone') }}</span>
                                </div>
                            </a>
                        @endif

                        @if (siteUrlSettings('site_email'))
                            <a href="mailto:{{ siteUrlSettings('site_email') }}" class="contact-card">
                                <i class="bi bi-envelope-fill"></i>
                                <div>
                                    <span class="label">Email Support</span>
                                    <span class="value">{{ siteUrlSettings('site_email') }}</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Social Media Links -->
            @php
                $socials = [
                    'facebook' => [
                        'value' => siteUrlSettings('site_facebook'),
                        'icon' => 'bi-facebook',
                        'prefix' => 'https://facebook.com/'
                    ],
                    'twitter' => [
                        'value' => siteUrlSettings('site_twitter'),
                        'icon' => 'bi-twitter-x',
                        'prefix' => 'https://x.com/'
                    ],
                    'instagram' => [
                        'value' => siteUrlSettings('site_instagram'),
                        'icon' => 'bi-instagram',
                        'prefix' => 'https://instagram.com/'
                    ],
                    'whatsapp' => [
                        'value' => siteUrlSettings('site_whatsapp'),
                        'icon' => 'bi-whatsapp',
                        'prefix' => 'https://wa.me/'
                    ],
                    'linkedin' => [
                        'value' => siteUrlSettings('site_linkedin'),
                        'icon' => 'bi-linkedin',
                        'prefix' => ''
                    ],
                    'youtube' => [
                        'value' => siteUrlSettings('site_youtube'),
                        'icon' => 'bi-youtube',
                        'prefix' => ''
                    ]
                ];
            @endphp

            @if(collect($socials)->contains(fn($s) => !empty($s['value'])))
                <div class="social-row">
                    @foreach($socials as $name => $social)
                        @if($social['value'])
                            @php
                                $url = str_starts_with($social['value'], 'http') 
                                    ? $social['value'] 
                                    : $social['prefix'] . ltrim($social['value'], '@/');
                            @endphp
                            <a href="{{ $url }}" target="_blank" class="social-link" title="{{ ucfirst($name) }}">
                                <i class="bi {{ $social['icon'] }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <p class="footer-copyright">
            {{ siteUrlSettings('footer_copyright') ?? '© ' . date('Y') . ' ' . (siteUrlSettings('site_name') ?? config('app.name')) . '. All rights reserved.' }}
        </p>
    </div>
</body>
</html>
