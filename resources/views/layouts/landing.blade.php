<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Business Suite' }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        hand: ['Kalam', 'cursive'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#f05931',
                            50: '#fffcfb',
                            100: '#ffe3d6',
                            200: '#ffc1a4',
                            500: '#f05931',
                            600: '#d93e16',
                        },
                        accent: {
                            DEFAULT: '#ed1a6e',
                            soft: '#ffe0ec',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --brand: #f05931;
            --brand-soft: #ffe3d6;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #FAFAFA; /* Slightly warmer white */
            overflow-x: hidden;
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        /* Organic Shapes */
        .blob-bg {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
        }
        
        /* Marker Highlight */
        .marker-highlight {
            position: relative;
            display: inline-block;
            z-index: 10;
        }
        .marker-highlight::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: -4px;
            right: -4px;
            height: 12px;
            background: #ffe3d6;
            z-index: -1;
            transform: rotate(-1deg);
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--brand);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px 0 rgba(240, 89, 49, 0.39);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(240, 89, 49, 0.23);
            background: #d93e16;
        }
    </style>
</head>
<body class="antialiased text-slate-900">
    <div class="relative min-h-screen">
        @yield('content')
    </div>
</body>
</html>
