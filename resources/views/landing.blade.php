<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ optional($settings)->hero_title ?: config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
    @if(optional($settings)->favicon_url)
        <link rel="icon" href="{{ $settings->favicon_url }}?v={{ optional($settings)->updated_at?->timestamp ?? time() }}" type="image/png">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'], 'build')
    @php
        $primary = optional($settings)->primary_color ?? '#1d4ed8';
        $secondary = optional($settings)->secondary_color ?? '#0f172a';
        $accent = optional($settings)->accent_color ?? '#f97316';
        $button = optional($settings)->button_color ?? '#0ea5e9';
        $tableHeaderFrom = optional($settings)->table_header_from ?? '#0f172a';
        $tableHeaderTo = optional($settings)->table_header_to ?? '#2563eb';
        $tableHeaderText = optional($settings)->table_header_text_color ?? '#e2e8f0';
        $tableRowOdd = optional($settings)->table_row_odd_color ?? '#fdfdfd';
        $tableRowEven = optional($settings)->table_row_even_color ?? '#f7f8fb';
        $tableRowText = optional($settings)->table_row_text_color ?? '#0f172a';
        $tableBorder = optional($settings)->table_border_color ?? '#e2e8f0';
        $headerOverlayFrom = optional($settings)->header_overlay_from ?? '#0f172a';
        $headerOverlayTo = optional($settings)->header_overlay_to ?? '#172554';
        $heroOverlayOpacity = optional($settings)->hero_overlay_opacity ?? 0.9;
        $landingBgOpacity = optional($settings)->landing_background_opacity ?? 0.95;
        $contentBackgroundOpacity = optional($settings)->content_background_opacity ?? 0.92;
        $headerHeight = optional($settings)->header_height ?? 500;
        $landingSlides = method_exists($settings, 'getLandingBackgroundSlideUrlsAttribute')
            ? ($settings->landing_background_slide_urls ?? [])
            : (optional($settings)->landing_background_url ? [optional($settings)->landing_background_url] : []);
        $contentBackground = optional($settings)->content_background_url;
        $heroSuperTitle = optional($settings)->hero_super_title;
        $sliderEnabled = (bool) (optional($settings)->landing_slider_enabled ?? true);
        $sliderIntervalMs = (int) (optional($settings)->landing_slider_interval_ms ?? 6000);

        $normalizeHex = function (?string $color): string {
            $color = ltrim($color ?? '000000', '#');
            if (strlen($color) === 3) {
                $color = implode('', array_map(fn($c) => $c . $c, str_split($color)));
            }
            return str_pad($color, 6, '0');
        };

        $hexToRgbString = function (?string $color) use ($normalizeHex): string {
            $hex = $normalizeHex($color);
            return implode(', ', [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ]);
        };

        $headerOverlayFromRgb = $hexToRgbString($headerOverlayFrom);
        $headerOverlayToRgb = $hexToRgbString($headerOverlayTo);
        $heroBackground = "linear-gradient(135deg, rgba({$headerOverlayFromRgb}, {$heroOverlayOpacity}), rgba({$headerOverlayToRgb}, {$heroOverlayOpacity}))";
        $contentShellClass = $contentBackground ? 'content-shell content-shell--image' : 'content-shell content-shell--animated';
        $contentShellStyle = $contentBackground ? "background-image: url('{$contentBackground}');" : '';
    @endphp
    <style>
        :root {
            --color-primary: {{ $primary }};
            --color-secondary: {{ $secondary }};
            --color-accent: {{ $accent }};
            --color-button: {{ $button }};
            --table-header-from: {{ $tableHeaderFrom }};
            --table-header-to: {{ $tableHeaderTo }};
            --table-header-text: {{ $tableHeaderText }};
            --table-row-odd: {{ $tableRowOdd }};
            --table-row-even: {{ $tableRowEven }};
            --table-row-text: {{ $tableRowText }};
            --table-border: {{ $tableBorder }};
            --hero-overlay-from-rgb: {{ $headerOverlayFromRgb }};
            --hero-overlay-to-rgb: {{ $headerOverlayToRgb }};
            --hero-overlay-opacity: {{ $heroOverlayOpacity }};
            --landing-bg-opacity: {{ $landingBgOpacity }};
        --content-bg-opacity: {{ $contentBackgroundOpacity }};
        --header-height: {{ $headerHeight }}px;
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            @if($contentBackground)
                background-image: url('{{ $contentBackground }}');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                background-color: transparent;
            @else
                background-color: rgba(248, 250, 252, var(--content-bg-opacity));
            @endif
            color: #0f172a;
        }

        .content-shell {
            position: relative;
            border-radius: 40px;
            padding: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(15, 23, 42, 0.12);
        }

        .content-shell-inner {
            position: relative;
            z-index: 1;
        }

        .content-shell--image {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .content-shell--image::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(248, 250, 252, var(--content-bg-opacity));
            backdrop-filter: blur(3px);
        }

        .content-shell--animated {
            background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.18), transparent 45%),
                        radial-gradient(circle at 80% 0%, rgba(236, 72, 153, 0.15), transparent 55%),
                        #f8fafc;
        }

        .content-shell--animated::before,
        .content-shell--animated::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.3), transparent 60%);
            filter: blur(10px);
            animation: floatBlob 22s ease-in-out infinite;
        }

        .content-shell--animated::before {
            top: -120px;
            left: -80px;
        }

        .content-shell--animated::after {
            bottom: -160px;
            right: -100px;
            animation-duration: 28s;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.25), transparent 60%);
        }

        @keyframes floatBlob {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(40px, -20px, 0) scale(1.15);
            }
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        .hero-section {
            background-size: cover;
            background-position: center;
            position: relative;
            /* Keep overflow visible so button shadows/tooltips can pop out */
            border-radius: 40px;
            box-shadow: 0 35px 90px rgba(15, 23, 42, 0.35);
            min-height: var(--header-height);
            height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .hero-slides, .hero-overlay {
            border-radius: inherit; /* Follow parent's rounded corners */
            overflow: hidden;       /* Clip the image/gradient to the border radius */
        }

        .hero-slides {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 900ms ease;
            will-change: opacity;
        }

        .hero-slide.is-active {
            opacity: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        @media (max-width: 640px) {
            .hero-section {
                border-radius: 28px;
                min-height: 420px;
            }

            .hero-content {
                padding: 0 16px;
            }

            .stat-card {
                padding: 1.1rem;
                border-radius: 22px;
            }

            .stat-card::after {
                width: 92px;
                height: 92px;
                border-width: 10px;
            }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(var(--hero-overlay-from-rgb), var(--hero-overlay-opacity)), rgba(var(--hero-overlay-to-rgb), var(--hero-overlay-opacity)));
        }

        .stat-card {
            position: relative;
            border-radius: 26px;
            padding: 1.75rem;
            color: white;
            overflow: hidden;
            box-shadow: 0 25px 45px rgba(15, 23, 42, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.25);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 12px solid rgba(255, 255, 255, 0.12);
        }

        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, var(--color-primary), #60a5fa);
        }

        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, var(--color-secondary), #475569);
        }

        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #16a34a, #22d3ee);
        }

        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #9333ea, #d946ef);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-sparkline {
            margin-top: 1.25rem;
            height: 32px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }

        .stat-sparkline span {
            position: absolute;
            inset: 0;
            width: calc(var(--fill, 60) * 1%);
            background: rgba(255, 255, 255, 0.65);
            border-radius: inherit;
        }

        .schedule-card {
            border-radius: 32px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            background: white;
            box-shadow: 0 35px 90px rgba(15, 23, 42, 0.08);
        }

        /* Mobile Card Layout for Schedule */
        @media (max-width: 767px) {
            #scheduleScroll.schedule-table-wrapper {
                display: none !important;
            }

            .schedule-mobile-cards {
                display: block !important;
                padding-bottom: 1rem;
            }

            .mobile-schedule-card {
                background: white;
                border-radius: 16px;
                padding: 1rem;
                margin-bottom: 0.75rem;
                border: 1px solid rgba(15, 23, 42, 0.08);
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .mobile-schedule-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
            }

            .mobile-schedule-card.highlight {
                background-color: rgba(59, 130, 246, 0.08);
                border-color: rgba(59, 130, 246, 0.2);
            }

            .mobile-card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
                flex-wrap: wrap;
            }

            .mobile-card-date {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                background: linear-gradient(135deg, var(--table-header-from), var(--table-header-to));
                color: white;
                font-size: 0.75rem;
                font-weight: 600;
                padding: 0.35rem 0.75rem;
                border-radius: 999px;
            }

            .mobile-card-time {
                font-size: 0.75rem;
                color: #64748b;
                font-weight: 500;
            }

            .mobile-card-jenis {
                display: inline-block;
                background: rgba(59, 130, 246, 0.1);
                color: #2563eb;
                font-size: 0.7rem;
                font-weight: 600;
                padding: 0.25rem 0.6rem;
                border-radius: 999px;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .mobile-card-title {
                font-size: 0.95rem;
                font-weight: 600;
                color: #0f172a;
                line-height: 1.4;
                margin-bottom: 0.75rem;
            }

            .mobile-card-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                font-size: 0.8rem;
            }

            .mobile-card-info-item {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
            }

            .mobile-card-info-label {
                color: #94a3b8;
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .mobile-card-info-value {
                color: #334155;
                font-weight: 500;
            }

            .mobile-card-location {
                grid-column: 1 / -1;
                margin-top: 0.25rem;
                padding-top: 0.5rem;
                border-top: 1px solid rgba(15, 23, 42, 0.06);
            }

            .schedule-card {
                padding: 1rem !important;
                border-radius: 20px;
            }

            .schedule-card h3 {
                font-size: 1.25rem !important;
            }

            #scheduleScroll {
                max-height: 70vh;
                overflow-y: auto;
            }

            .content-shell {
                padding: 1rem;
                border-radius: 24px;
            }

            .stat-value {
                font-size: 1.75rem !important;
            }

            .stats-grid {
                gap: 0.75rem !important;
            }

            .stat-card {
                padding: 0.875rem !important;
                border-radius: 18px !important;
                min-height: auto !important;
            }

            .stat-sparkline {
                height: 24px !important;
                margin-top: 0.75rem !important;
            }
        }

        @media (min-width: 768px) {
            .schedule-mobile-cards {
                display: none !important;
            }

            #scheduleScroll.schedule-table-wrapper {
                display: block !important;
            }
        }

        /* Default: hide mobile cards, show on mobile only */
        .schedule-mobile-cards {
            display: none;
        }

        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .schedule-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: linear-gradient(135deg, var(--table-header-from), var(--table-header-to));
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }

        .schedule-table thead th {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--table-header-text);
            padding: 0;
        }

        .schedule-table thead button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1.25rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.72rem;
            color: inherit;
            font-weight: 600;
        }

        .schedule-table tbody tr {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .schedule-table tbody tr:nth-child(odd) {
            background-color: var(--table-row-odd);
        }

        .schedule-table tbody tr:nth-child(even) {
            background-color: var(--table-row-even);
        }

        .schedule-table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
        }

        .schedule-table tbody td {
            border-bottom: 1px solid var(--table-border);
            color: var(--table-row-text);
        }

        .icon-pill {
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.15);
            background: #fff;
            padding: 0.5rem;
            color: #0f172a;
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            transition: all 0.2s ease;
        }

        .icon-pill:hover {
            border-color: rgba(15, 23, 42, 0.35);
            transform: translateY(-1px);
        }

        .scroll-toggle {
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.15);
            background: #fff;
            width: 42px;
            height: 42px;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            transition: all 0.2s ease;
        }

        .scroll-toggle:hover {
            border-color: rgba(15, 23, 42, 0.35);
            transform: translateY(-1px);
        }

        .fade-section {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-section.show {
            opacity: 1;
            transform: translateY(0);
        }

        .schedule-table tbody tr.highlight {
            background-color: rgba(59, 130, 246, 0.1);
            animation: highlightFade 2s ease-out;
        }

        @keyframes highlightFade {
            0% {
                background-color: rgba(59, 130, 246, 0.3);
            }
            100% {
                background-color: rgba(59, 130, 246, 0.1);
            }
        }

        .schedule-table tbody tr.no-results {
            opacity: 0.6;
        }

        .search-match {
            background-color: rgba(251, 191, 36, 0.3);
            padding: 1px 2px;
            border-radius: 2px;
            font-weight: 600;
        }

        .search-input-container {
            position: relative;
            transition: transform 0.3s ease;
        }

        .search-input-container:focus-within {
            transform: translateY(-2px);
        }

        .search-input-container::after {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(45deg, rgba(59, 130, 246, 0.3), rgba(147, 51, 234, 0.3), rgba(236, 72, 153, 0.3));
            border-radius: 24px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
            filter: blur(8px);
        }

        .search-input-container:focus-within::after {
            opacity: 0.7;
        }

        .search-input-container input {
            /* Pastikan input itu sendiri tidak di atas pseudo-element blur */
            position: relative;
            z-index: 1;
        }

        #scheduleScroll {
            transition: box-shadow 0.3s ease;
        }

        #scheduleScroll:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
        }

        #schedule {
            scroll-margin-top: 0px;
        }

        /* Aturan untuk mode desktop */
        @media (min-width: 768px) {
            .content-shell {
                transform: none;
                margin: -1.5rem 0 !important;
                width: 100%;
                position: relative;
                z-index: 1;
            }

            /* Pastikan kolom judul di desktop bisa membungkus ke bawah dan tidak terpotong */
            .schedule-table tbody td:nth-child(3) {
                max-width: none;
                overflow: visible;
                white-space: normal;
            }

            .content-shell-inner {
                transform: none;
            }

            .stat-card {
                padding: 2rem;
                min-height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .stat-value {
                font-size: 2.5rem;
            }

            .schedule-card {
                padding: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(4, 1fr) !important;
            }

            .schedule-table tbody td div p {
                margin-bottom: 0 !important;
                display: inline;
            }
        }

    </style>
</head>

<body class="min-h-screen">
    <header class="mx-auto max-w-screen-2xl 2xl:max-w-screen-3xl px-4 pt-6 sm:px-6">
        <div class="hero-section text-white" data-hero-container>
            <div class="hero-slides" data-hero-slides aria-hidden="true">
                @foreach(($landingSlides ?? []) as $idx => $slideUrl)
                    <div class="hero-slide {{ $idx === 0 ? 'is-active' : '' }}" style="background-image: url('{{ $slideUrl }}');"></div>
                @endforeach
            </div>
            <div class="hero-overlay" aria-hidden="true"></div>
            <div class="hero-content">
                <div class="relative mx-auto flex max-w-screen-xl flex-col gap-6 px-0 py-0 md:flex-row md:items-center fade-section">
                    <div class="flex-1 space-y-6 text-center">
                        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                            @if(optional($settings)->logo_url)
                                <img src="{{ $settings->logo_url }}?v={{ optional($settings)->updated_at?->timestamp ?? time() }}" alt="Logo" class="h-12 w-12 rounded-xl object-cover shadow-lg">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 text-lg font-bold backdrop-blur-md">
                                    PA
                                </div>
                            @endif
                            <h1 class="text-2xl font-bold leading-tight sm:text-3xl md:text-4xl">
                                {{ optional($settings)->hero_title }}
                            </h1>
                        </div>
                        
                        <div class="mx-auto max-w-5xl space-y-2">
                            @if(optional($settings)->hero_subtitle)
                                <p class="text-base text-white/90 sm:text-lg font-medium">
                                    {{ $settings->hero_subtitle }}
                                </p>
                            @endif
                            @if(optional($settings)->app_description)
                                <p class="text-xs text-white/70 sm:text-sm leading-relaxed">
                                    {{ $settings->app_description }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-row flex-wrap items-center justify-center gap-4 sm:gap-6">
                            <a href="{{ optional($settings)->cta_link ?? route('login') }}" class="inline-flex min-w-[160px] items-center justify-center gap-2 rounded-full px-8 py-4 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:-translate-y-1.5 hover:brightness-110 hover:shadow-2xl active:scale-95 sm:w-max sm:px-10" style="background-color: var(--color-button); box-shadow: 0 15px 35px rgba(14, 165, 233, 0.35);">
                                {{ optional($settings)->cta_label ?? 'Masuk Sistem' }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7m0 0l-7 7m7-7H4" />
                                </svg>
                            </a>
                            <button id="scrollSchedule" class="inline-flex min-w-[160px] items-center justify-center rounded-full border-2 border-white/50 px-8 py-4 text-sm font-bold text-white transition-all duration-300 hover:bg-white/10 hover:border-white hover:-translate-y-1.5 hover:shadow-lg active:scale-95 sm:w-max sm:px-10">
                                Lihat Seminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>



    <main class="mx-auto max-w-screen-2xl 2xl:max-w-screen-3xl px-4 py-10 sm:px-6 sm:py-12">
        <div class="{{ $contentShellClass }}" @if($contentShellStyle) style="{{ $contentShellStyle }}" @endif>
            <div class="content-shell-inner space-y-12">
                <section class="stats-grid grid grid-cols-2 gap-4 md:grid-cols-4 sm:gap-6 fade-section">
                    @foreach($stats as $stat)
                        <article class="stat-card" style="--fill: {{ rand(55, 95) }}">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/70">{{ $stat['label'] }}</p>
                            <p class="stat-value mt-3 text-2xl sm:mt-4 sm:text-[2.5rem]">{{ $stat['value'] }}</p>
                            <p class="text-xs text-white/80 sm:text-sm">{{ $stat['helper'] }}</p>
                            <div class="stat-sparkline">
                                <span></span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <section id="schedule" class="fade-section">
                    <div class="schedule-card space-y-6 p-6">
                        <div class="flex flex-col items-center gap-4 text-center">
                            <h3 class="text-3xl font-semibold text-slate-900">
                                {{ optional($settings)->schedule_heading ?? 'Jadwal Seminar Hari Ini & Mendatang' }}
                            </h3>
                        </div>

                        <!-- Search Bar Section -->
                        <div class="relative w-full max-w-2xl mx-auto">
                            <div class="search-input-container relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 transition-colors group-focus-within:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    id="searchInput"
                                    placeholder="Cari berdasarkan judul, nama mahasiswa, pembimbing, atau lokasi..."
                                    class="w-full pl-12 pr-12 py-4 text-gray-900 bg-white border-2 border-gray-200 rounded-2xl shadow-sm transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50 hover:border-gray-300"
                                >
                                <div id="searchClear" class="absolute inset-y-0 right-0 flex items-center pr-4 cursor-pointer opacity-0 pointer-events-none transition-opacity duration-200">
                                    <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div id="searchResults" class="mt-2 text-sm text-gray-500 text-center transition-all duration-300 opacity-0 h-0">
                                <span id="searchCount"></span>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-200">
                            <!-- Desktop Table View -->
                            <div id="scheduleScroll" class="relative max-h-[520px] overflow-y-auto schedule-table-wrapper">
                                <!-- Desktop Table View -->
                                <table class="schedule-table divide-y divide-gray-200 text-left text-sm">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="tanggal_raw" data-sort-type="date">
                                                    Tanggal
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="waktu_raw" data-sort-type="time">
                                                    Waktu
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2 w-[320px]">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="judul" data-sort-type="string">
                                                    Judul
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="jenis" data-sort-type="string">
                                                    Jenis
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="mahasiswa" data-sort-type="string">
                                                    Mahasiswa
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="pembimbing" data-sort-type="string">
                                                    Pembimbing
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-2">
                                                <button type="button" class="flex w-full items-center justify-between gap-2 text-left text-slate-600" data-sort-key="lokasi" data-sort-type="string">
                                                    Lokasi
                                                    <span data-sort-icon class="text-xs text-slate-400">↕</span>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="scheduleBody" class="bg-white">
                                        @forelse($schedule as $row)
                                            <tr>
                                                <td class="px-4 py-2 text-sm font-semibold">{{ $row['tanggal'] }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $row['waktu'] }}</td>
                                                <td class="px-4 py-2 text-sm">
                                                    <div class="text-slate-900">{!! $row['judul'] !!}</div>
                                                </td>
                                                <td class="px-4 py-2 text-sm">{{ $row['jenis'] }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $row['mahasiswa'] }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $row['pembimbing'] ?: '—' }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $row['lokasi'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-5 py-6 text-center text-gray-500">Belum ada jadwal seminar yang siap ditampilkan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Card View -->
                            <div class="schedule-mobile-cards p-3 max-h-[70vh] overflow-y-auto" id="scheduleMobileCards">
                                @forelse($schedule as $row)
                                    <div class="mobile-schedule-card">
                                        <div class="mobile-card-header">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="mobile-card-date">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $row['tanggal'] }}
                                                </span>
                                                <span class="mobile-card-time">{{ $row['waktu'] }}</span>
                                            </div>
                                            <span class="mobile-card-jenis">{{ $row['jenis'] }}</span>
                                        </div>
                                        <div class="mobile-card-title">{!! $row['judul'] !!}</div>
                                        <div class="mobile-card-info">
                                            <div class="mobile-card-info-item">
                                                <span class="mobile-card-info-label">Mahasiswa</span>
                                                <span class="mobile-card-info-value">{{ $row['mahasiswa'] }}</span>
                                            </div>
                                            <div class="mobile-card-info-item">
                                                <span class="mobile-card-info-label">Pembimbing</span>
                                                <span class="mobile-card-info-value">{{ $row['pembimbing'] ?: '—' }}</span>
                                            </div>
                                            <div class="mobile-card-info-item mobile-card-location">
                                                <span class="mobile-card-info-label">Lokasi</span>
                                                <span class="mobile-card-info-value">{{ $row['lokasi'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        Belum ada jadwal seminar yang siap ditampilkan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="flex flex-col gap-4 pt-6 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                <button id="schedulePrev" class="icon-pill" aria-label="Halaman sebelumnya">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button id="autoScrollToggle" class="scroll-toggle" aria-label="Toggle auto scroll">
                                    <svg data-icon="pause" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" d="M10 5v14M14 5v14" />
                                    </svg>
                                    <svg data-icon="play" xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </button>
                                <button id="scheduleNext" class="icon-pill" aria-label="Halaman berikutnya">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <span id="schedulePaginationInfo" class="text-slate-500 min-w-[130px] text-center">Halaman 1 dari 1</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        (function () {
            const enabled = @json($sliderEnabled);
            const intervalMs = @json($sliderIntervalMs);
            if (!enabled) return;

            const slides = Array.from(document.querySelectorAll('[data-hero-slides] .hero-slide'));
            if (slides.length <= 1) return;

            let index = 0;
            const tick = () => {
                slides[index].classList.remove('is-active');
                index = (index + 1) % slides.length;
                slides[index].classList.add('is-active');
            };

            window.setInterval(tick, Math.max(2000, intervalMs || 6000));
        })();
    </script>

    <!-- Footer -->
    <footer class="bg-white mt-10 border-t">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Bihikmi. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scheduleSection = document.getElementById('schedule');
            const heroContainer = document.querySelector('[data-hero-container]');
            
            // Event listener untuk tombol desktop
            document.getElementById('scrollSchedule')?.addEventListener('click', (event) => {
                event.preventDefault();
                if (!scheduleSection) return;
                const offset = heroContainer ? 90 : 40;
                const targetTop = scheduleSection.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: Math.max(targetTop, 0), behavior: 'smooth' });
            });

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            document.querySelectorAll('.fade-section').forEach(section => observer.observe(section));

            const scheduleDataRaw = @json($schedule);
            const scheduleData = Array.isArray(scheduleDataRaw) ? scheduleDataRaw : [];
            const rowsPerPage = 8;
            const stepDurationMs = 30; // 30ms interval = ~33fps (Smooth)
            let scrollSpeed = 0.25; // 0.25px per tick = ~8px/sec (Readable speed)
            let sortKey = 'tanggal_raw';
            let sortDirection = 'asc';
            let autoPlay = true;
            let autoTimer = null;
            let sortedCache = [];
            let totalPages = 1;
            let currentPage = 1;
            let searchTerm = '';
            let filteredData = [];
            let isHovering = false;
            let preciseScrollTop = -1; // Tracker untuk posisi scroll presisi (float)

            const tableBody = document.getElementById('scheduleBody');
            const autoToggle = document.getElementById('autoScrollToggle');
            const scrollContainerDesktop = document.getElementById('scheduleScroll');
            const scrollContainerMobile = document.getElementById('scheduleMobileCards');
            
            const sortButtons = document.querySelectorAll('[data-sort-key]');
            const pauseIcon = autoToggle?.querySelector('[data-icon="pause"]');
            const playIcon = autoToggle?.querySelector('[data-icon="play"]');
            const paginationInfo = document.getElementById('schedulePaginationInfo');
            const prevButton = document.getElementById('schedulePrev');
            const nextButton = document.getElementById('scheduleNext');

            // Search elements
            const searchInput = document.getElementById('searchInput');
            const searchClear = document.getElementById('searchClear');
            const searchResults = document.getElementById('searchResults');
            const searchCount = document.getElementById('searchCount');

            if (!tableBody || !scrollContainerDesktop) {
                return;
            }

            const getActiveContainer = () => {
                // Cek visibility container mobile
                if (scrollContainerMobile && getComputedStyle(scrollContainerMobile).display !== 'none') {
                    return scrollContainerMobile;
                }
                return scrollContainerDesktop;
            };

            // Search functionality
            const highlightText = (text, term, isHTML = false) => {
                if (!text) return '';
                if (!term) return isHTML ? text : escapeHTML(text);
                
                const safeText = isHTML ? text : escapeHTML(text);
                const escapedTerm = escapeRegex(term);
                
                // Use a regex that avoids matching inside tag names or attributes if isHTML is true
                const regexPattern = isHTML 
                    ? `(${escapedTerm})(?![^<]*>)`
                    : `(${escapedTerm})`;
                const regex = new RegExp(regexPattern, 'gi');
                
                return safeText.replace(regex, '<span class="search-match">$1</span>');
            };

            const escapeRegex = (string) => {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            };

            const filterData = () => {
                if (!searchTerm.trim()) {
                    filteredData = [...scheduleData];
                } else {
                    const term = searchTerm.toLowerCase();
                    filteredData = scheduleData.filter(row => {
                        return (
                            (row.judul && row.judul.toLowerCase().includes(term)) ||
                            (row.mahasiswa && row.mahasiswa.toLowerCase().includes(term)) ||
                            (row.pembimbing && row.pembimbing.toLowerCase().includes(term)) ||
                            (row.lokasi && row.lokasi.toLowerCase().includes(term)) ||
                            (row.jenis && row.jenis.toLowerCase().includes(term)) ||
                            (row.tanggal && row.tanggal.toLowerCase().includes(term)) ||
                            (row.waktu && row.waktu.toLowerCase().includes(term))
                        );
                    });
                }

                // Update search results info
                if (searchTerm.trim()) {
                    searchResults.style.opacity = '1';
                    searchResults.style.height = 'auto';
                    searchCount.textContent = `Ditemukan ${filteredData.length} hasil untuk "${searchTerm}"`;
                } else {
                    searchResults.style.opacity = '0';
                    searchResults.style.height = '0';
                }

                return filteredData;
            };

            // Search event listeners
            searchInput?.addEventListener('input', (e) => {
                searchTerm = e.target.value;
                if (searchTerm) {
                    searchClear.style.opacity = '1';
                    searchClear.style.pointerEvents = 'auto';
                } else {
                    searchClear.style.opacity = '0';
                    searchClear.style.pointerEvents = 'none';
                }

                renderTable();
                updateSortIndicators();
                restartAutoScroll();
            });

            searchClear?.addEventListener('click', () => {
                searchTerm = '';
                searchInput.value = '';
                searchClear.style.opacity = '0';
                searchClear.style.pointerEvents = 'none';

                renderTable();
                updateSortIndicators();
                restartAutoScroll();
            });

            const getSortType = (key) => document.querySelector(`[data-sort-key="${key}"]`)?.dataset.sortType || 'string';

            const normalizeValue = (value, type) => {
                if (type === 'date') {
                    return value ? new Date(value).getTime() : 0;
                }
                if (type === 'time') {
                    if (!value) return 0;
                    const [hour = '0', minute = '0'] = value.split(':');
                    return Number(hour) * 60 + Number(minute);
                }
                return (value ?? '').toString().toLowerCase();
            };

            const escapeHTML = (value) => (value ?? '').toString().replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char] || char));

            const getSortedData = () => {
                const dataToSort = [...filteredData];
                return dataToSort.sort((a, b) => {
                    const type = getSortType(sortKey);
                    const valA = normalizeValue(a[sortKey], type);
                    const valB = normalizeValue(b[sortKey], type);
                    if (valA === valB) return 0;
                    const comparison = valA > valB ? 1 : -1;
                    return sortDirection === 'asc' ? comparison : -comparison;
                });
            };

            const updateSortIndicators = () => {
                sortButtons.forEach((button) => {
                    const icon = button.querySelector('[data-sort-icon]');
                    if (!icon) return;
                    if (button.dataset.sortKey === sortKey) {
                        icon.textContent = sortDirection === 'asc' ? '↑' : '↓';
                        button.classList.add('text-blue-200');
                    } else {
                        icon.textContent = '↕';
                        button.classList.remove('text-blue-200');
                    }
                });
            };

            const updateToggleIcon = () => {
                if (!autoToggle) return;
                if (autoPlay) {
                    pauseIcon?.classList.remove('hidden');
                    playIcon?.classList.add('hidden');
                } else {
                    pauseIcon?.classList.add('hidden');
                    playIcon?.classList.remove('hidden');
                }
            };

            const updatePaginationInfo = () => {
                if (!paginationInfo) return;
                paginationInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
            };

            const updateCurrentPageFromScroll = (container) => {
                if (!container) return;
                const scrollPercent = container.scrollTop / (container.scrollHeight - container.clientHeight);
                const page = Math.floor(scrollPercent * totalPages) + 1;
                currentPage = Math.min(totalPages, Math.max(1, page));
            };

            const scrollToPage = (page) => {
                const container = getActiveContainer();
                if (!container) return;
                
                currentPage = Math.min(totalPages, Math.max(1, page));
                const totalHeight = container.scrollHeight - container.clientHeight;
                const targetTop = (totalHeight / totalPages) * (currentPage - 1);
                
                container.scrollTo({ top: targetTop, behavior: 'smooth' });
                // Reset tracker
                preciseScrollTop = targetTop;
                updatePaginationInfo();
            };

            const renderTable = () => {
                filterData();
                sortedCache = getSortedData();

                if (!sortedCache.length) {
                    const message = searchTerm.trim()
                        ? `Tidak ada hasil untuk "${searchTerm}"`
                        : 'Belum ada jadwal seminar yang siap ditampilkan.';
                    tableBody.innerHTML = `<tr><td colspan="7" class="px-5 py-6 text-center text-gray-500">${message}</td></tr>`;
                    if (scrollContainerMobile) {
                        scrollContainerMobile.innerHTML = `<div class="text-center py-8 text-gray-500">${message}</div>`;
                    }
                    totalPages = 1;
                    currentPage = 1;
                    updatePaginationInfo();
                    return;
                }

                const baseMarkup = sortedCache.map(row => {
                    const highlightClass = searchTerm.trim() ? 'highlight' : '';
                    return `
                        <tr class="${highlightClass}">
                            <td class="px-5 py-3 text-sm font-semibold">${searchTerm ? highlightText(row.tanggal, searchTerm) : escapeHTML(row.tanggal)}</td>
                            <td class="px-5 py-3 text-sm">${searchTerm ? highlightText(row.waktu, searchTerm) : escapeHTML(row.waktu)}</td>
                            <td class="px-5 py-3 text-sm">
                                <div class="text-slate-900">${searchTerm ? highlightText(row.judul, searchTerm, true) : (row.judul ?? '')}</div>
                            </td>
                            <td class="px-5 py-3 text-sm">${searchTerm ? highlightText(row.jenis, searchTerm) : escapeHTML(row.jenis)}</td>
                            <td class="px-5 py-3 text-sm">${searchTerm ? highlightText(row.mahasiswa, searchTerm) : escapeHTML(row.mahasiswa)}</td>
                            <td class="px-5 py-3 text-sm">${searchTerm ? highlightText(row.pembimbing || '—', searchTerm) : escapeHTML(row.pembimbing || '—')}</td>
                            <td class="px-5 py-3 text-sm">${searchTerm ? highlightText(row.lokasi, searchTerm) : escapeHTML(row.lokasi)}</td>
                        </tr>
                    `;
                }).join('');

                tableBody.innerHTML = baseMarkup;

                // Render Mobile
                let mobileMarkup = '';
                if (scrollContainerMobile) {
                    mobileMarkup = sortedCache.map(row => {
                        const highlightClass = searchTerm.trim() ? 'highlight' : '';
                        return `
                            <div class="mobile-schedule-card ${highlightClass}">
                                <div class="mobile-card-header">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="mobile-card-date">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            ${searchTerm ? highlightText(row.tanggal, searchTerm) : escapeHTML(row.tanggal)}
                                        </span>
                                        <span class="mobile-card-time">${searchTerm ? highlightText(row.waktu, searchTerm) : escapeHTML(row.waktu)}</span>
                                    </div>
                                    <span class="mobile-card-jenis">${searchTerm ? highlightText(row.jenis, searchTerm) : escapeHTML(row.jenis)}</span>
                                </div>
                                <div class="mobile-card-title">${searchTerm ? highlightText(row.judul, searchTerm, true) : (row.judul ?? '')}</div>
                                <div class="mobile-card-info">
                                    <div class="mobile-card-info-item">
                                        <span class="mobile-card-info-label">Mahasiswa</span>
                                        <span class="mobile-card-info-value">${searchTerm ? highlightText(row.mahasiswa, searchTerm) : escapeHTML(row.mahasiswa)}</span>
                                    </div>
                                    <div class="mobile-card-info-item">
                                        <span class="mobile-card-info-label">Pembimbing</span>
                                        <span class="mobile-card-info-value">${searchTerm ? highlightText(row.pembimbing || '—', searchTerm) : escapeHTML(row.pembimbing || '—')}</span>
                                    </div>
                                    <div class="mobile-card-info-item mobile-card-location">
                                        <span class="mobile-card-info-label">Lokasi</span>
                                        <span class="mobile-card-info-value">${searchTerm ? highlightText(row.lokasi, searchTerm) : escapeHTML(row.lokasi)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    scrollContainerMobile.innerHTML = mobileMarkup;
                }

                // Data cloning for infinite scroll effect (only if no search active)
                if (sortedCache.length && !searchTerm.trim()) {
                    // Clone for Desktop
                    const firstRow = tableBody.querySelector('tr');
                    const rowHeight = firstRow ? firstRow.getBoundingClientRect().height : 56;
                    if (rowHeight) {
                        let loops = 0;
                        const maxLoops = 6;
                        const minScrollableHeight = scrollContainerDesktop.clientHeight + rowHeight * 4; // Ensure enough buffer
                        while (scrollContainerDesktop.scrollHeight <= minScrollableHeight && loops < maxLoops) {
                            tableBody.insertAdjacentHTML('beforeend', baseMarkup);
                            loops += 1;
                        }
                    }

                    // Clone for Mobile
                    if (scrollContainerMobile) {
                        const firstCard = scrollContainerMobile.querySelector('.mobile-schedule-card');
                        const cardHeight = firstCard ? firstCard.getBoundingClientRect().height : 150;
                        if (cardHeight) {
                            let loops = 0;
                            const maxLoops = 6;
                            const minScrollableHeight = scrollContainerMobile.clientHeight + cardHeight * 3;
                            while (scrollContainerMobile.scrollHeight <= minScrollableHeight && loops < maxLoops) {
                                scrollContainerMobile.insertAdjacentHTML('beforeend', mobileMarkup);
                                loops += 1;
                            }
                        }
                    }
                }

                // Recalculate pages
                // Simple approx: total items / items per page
                totalPages = Math.max(1, Math.ceil(sortedCache.length / rowsPerPage));
                currentPage = 1;
                
                scrollContainerDesktop.scrollTo({ top: 0, behavior: 'auto' });
                if (scrollContainerMobile) scrollContainerMobile.scrollTo({ top: 0, behavior: 'auto' });
                
                // Reset tracker
                preciseScrollTop = 0;
                updatePaginationInfo();
            };

            const stopAutoScroll = () => {
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                }
            };

            const advanceAutoScroll = () => {
                if (sortedCache.length === 0 || isHovering) {
                    stopAutoScroll();
                    return;
                }

                const container = getActiveContainer();
                if (!container) return;

                // Sync tracker if needed (e.g. after manual swipe)
                if (preciseScrollTop === -1 || Math.abs(preciseScrollTop - container.scrollTop) > 20) {
                     preciseScrollTop = container.scrollTop;
                }
                
                const maxScroll = container.scrollHeight - container.clientHeight;
                if (maxScroll <= 0) {
                    container.scrollTo({ top: 0, behavior: 'auto' });
                    preciseScrollTop = 0;
                    return;
                }

                // Increment tracker
                preciseScrollTop += scrollSpeed;

                // Infinite scroll loop logic (approximate)
                if (preciseScrollTop >= maxScroll - 2) { 
                     preciseScrollTop = 0;
                     container.scrollTo({ top: 0, behavior: 'auto' });
                } else {
                    container.scrollTop = preciseScrollTop;
                }
                
                updateCurrentPageFromScroll(container);
            };

            const restartAutoScroll = () => {
                stopAutoScroll();
                const container = getActiveContainer();
                if (container) preciseScrollTop = container.scrollTop; // Sync on restart
                
                if (autoPlay && sortedCache.length > 0) {
                    autoTimer = setInterval(advanceAutoScroll, stepDurationMs);
                }
            };

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAutoScroll();
                } else {
                    restartAutoScroll();
                }
            });

            autoToggle?.addEventListener('click', () => {
                autoPlay = !autoPlay;
                updateToggleIcon();
                restartAutoScroll();
            });

            prevButton?.addEventListener('click', () => {
                if (totalPages <= 1) return;
                scrollToPage(currentPage - 1);
                restartAutoScroll();
            });

            nextButton?.addEventListener('click', () => {
                if (totalPages <= 1) return;
                scrollToPage(currentPage + 1);
                restartAutoScroll();
            });

            sortButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.dataset.sortKey;
                    if (!key) return;
                    if (sortKey === key) {
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortKey = key;
                        sortDirection = 'asc';
                    }
                    renderTable();
                    updateSortIndicators();
                    restartAutoScroll();
                });
            });

            // Attach listeners to BOTH containers
            const containers = [scrollContainerDesktop, scrollContainerMobile];
            containers.forEach(cont => {
                if(!cont) return;
                
                cont.addEventListener('scroll', () => {
                    // Only update info if this is the active container
                    if (cont === getActiveContainer()) {
                         // updateCurrentPageFromScroll(cont); 
                         // Disabled auto-page update during scroll to avoid flickering numbers
                    }
                });

                cont.addEventListener('mouseenter', () => {
                    isHovering = true;
                    stopAutoScroll();
                });

                cont.addEventListener('mouseleave', () => {
                    isHovering = false;
                    restartAutoScroll();
                });
                
                // Touch events for mobile
                cont.addEventListener('touchstart', () => {
                     isHovering = true;
                     stopAutoScroll();
                }, {passive: true});
                
                cont.addEventListener('touchend', () => {
                     isHovering = false;
                     restartAutoScroll();
                }, {passive: true});
            });

            // Initialize filtered data
            filteredData = [...scheduleData];

            updateSortIndicators();
            updateToggleIcon();
            renderTable();
            restartAutoScroll();
        });
    </script>

</body>

</html>
