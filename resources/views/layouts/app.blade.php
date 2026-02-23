<!DOCTYPE html>
<html lang="en">
@php
    use Illuminate\Support\Facades\Auth;

    $brandingSettings = $brandingSettings ?? \App\Models\LandingPageSetting::first();
    $appName = optional($brandingSettings)->app_name ?? config('app.name', 'Protekta Apps');
    $appIcon = optional($brandingSettings)->app_icon_url ?? optional($brandingSettings)->logo_url;
    $brandPrimary = optional($brandingSettings)->primary_color ?? '#2563eb';
    $normalizeHex = function (?string $hex) {
        $hex = ltrim($hex ?? '2563eb', '#');
        if (strlen($hex) === 3) {
            $hex = implode('', array_map(fn($c) => $c . $c, str_split($hex)));
        }
        return str_pad($hex, 6, '0');
    };
    $brandPrimaryRgb = implode(', ', [
        hexdec(substr($normalizeHex($brandPrimary), 0, 2)),
        hexdec(substr($normalizeHex($brandPrimary), 2, 2)),
        hexdec(substr($normalizeHex($brandPrimary), 4, 2)),
    ]);
    // Check for Dual Login State (Impersonation)
    // If Admin is logged in AND (Dosen OR Mahasiswa is logged in), assume Impersonation.
    $adminLogged = Auth::guard('admin')->check();
    $targetLogged = Auth::guard('dosen')->check() || Auth::guard('mahasiswa')->check();
    
    $isImpersonating = session()->has('impersonated_by') || ($adminLogged && $targetLogged);
    
    // If impersonating, prioritize target guards. Otherwise, prioritize Admin.
    $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

    $currentGuard = null;
    $currentUser = null;
    foreach ($guardPriority as $guard) {
        if (Auth::guard($guard)->check()) {
            $currentGuard = $guard;
            $currentUser = Auth::guard($guard)->user();
            break;
        }
    }
    $currentUser = $currentUser ?? auth()->user();
    $currentRoleLabel = match ($currentGuard) {
        'admin' => 'Admin',
        'dosen' => 'Dosen',
        'mahasiswa' => 'Mahasiswa',
        default => 'Tamu'
    };
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - @yield('title')</title>
    @if($brandingSettings && $brandingSettings->favicon_url)
        <link rel="icon" href="{{ $brandingSettings->favicon_url }}?v={{ $brandingSettings->updated_at?->timestamp ?? time() }}" type="image/png">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'], 'build')
    <script>
        // Global Shim for Protekta (prevents race conditions)
        window.Protekta = window.Protekta || {
            initRegistry: [],
            registerInit: function(fn) { this.initRegistry.push(fn); }
        };
    </script>
    <style>
        :root {
            --brand-gradient: linear-gradient(135deg, #2563eb, #7c3aed);
            --brand-gradient-soft: linear-gradient(135deg, rgba(37, 99, 235, .1), rgba(124, 58, 237, .15));
            --brand-primary-rgb:
                {{ $brandPrimaryRgb }}
            ;
        }

        body .btn-gradient, .btn-pill-primary {
            background-image: var(--brand-gradient);
            color: #fff;
            border: none;
            border-radius: 9999px;
            padding: 0.65rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            box-shadow: 0 12px 30px rgba(76, 81, 191, 0.35);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 35px rgba(76, 81, 191, 0.4);
        }

        .btn-ghost {
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, .5);
            padding: 0.55rem 1.5rem;
            font-weight: 600;
            color: #475569;
            transition: all .2s ease;
        }

        .btn-ghost:hover {
            border-color: rgba(59, 130, 246, .4);
            color: #2563eb;
            background-color: rgba(37, 99, 235, .08);
        }

        .btn-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 9999px;
            padding: 0.6rem 1.7rem;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all .2s ease;
            border: none;
        }

        body .btn-pill-primary {
            background-image: var(--brand-gradient);
            color: #fff;
            box-shadow: 0 8px 25px rgba(76, 81, 191, 0.25);
        }

        .btn-pill-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(76, 81, 191, 0.35);
        }

        body .btn-pill-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid rgba(148, 163, 184, .5);
        }

        .btn-pill-secondary:hover {
            background: #e2e8f0;
            border-color: rgba(59, 130, 246, .35);
            color: #2563eb;
        }

        body .btn-pill-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.25);
        }

        .btn-pill-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(220, 38, 38, 0.35);
        }

        body .btn-pill-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.25);
        }

        .btn-pill-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(22, 163, 74, 0.35);
        }

        /* Live Search Animations */
        @keyframes pulse-search {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        .searching-active {
            animation: pulse-search 1s infinite ease-in-out;
            color: #3b82f6;
        }

        body .btn-pill-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.25);
        }

        .btn-pill-info:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.35);
        }

        body .btn-pill-purple {
            background: linear-gradient(135deg, #a855f7, #7c3aed);
            color: #fff;
            box-shadow: 0 8px 25px rgba(168, 85, 247, 0.25);
        }

        .btn-pill-purple:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(124, 58, 237, 0.35);
        }

        body .btn-pill-warning {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff;
            box-shadow: 0 8px 25px rgba(234, 88, 12, 0.25);
        }

        .btn-pill-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 30px rgba(249, 115, 22, 0.35);
        }

        /* Prevent icons inside pill buttons from scaling on hover */
        /* Only the button itself should move (translateY), not the icons */
        .btn-pill:hover i[class*="fa-"],
        .btn-pill-primary:hover i[class*="fa-"],
        .btn-pill-secondary:hover i[class*="fa-"],
        .btn-pill-info:hover i[class*="fa-"],
        .btn-pill-success:hover i[class*="fa-"],
        .btn-pill-danger:hover i[class*="fa-"],
        .btn-pill-warning:hover i[class*="fa-"],
        .btn-pill-purple:hover i[class*="fa-"] {
            transform: none;
        }

        .modern-navbar .nav-icon-btn {
            border-radius: 9999px;
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, .08);
            color: #2563eb;
            border: none;
            transition: all .2s ease;
        }

        .modern-navbar .nav-icon-btn:hover {
            background: rgba(37, 99, 235, .15);
            transform: translateY(-1px);
        }

        .modern-navbar {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(148, 163, 184, .2);
            box-shadow: 0 12px 40px rgba(15, 23, 42, .08);
        }

        .modern-navbar .modern-brand {
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.03em;
            color: #0f172a;
        }

        .app-name-text {
            display: inline-block;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modern-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(var(--brand-primary-rgb), 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 0.75rem;
            box-shadow: 0 6px 14px rgba(var(--brand-primary-rgb), 0.18);
        }

        .modern-navbar .profile-chip {
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, .35);
            padding: 0.35rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            color: #475569;
            transition: all .2s ease;
            text-decoration: none; /* Ensure no underline from .nav-link */
        }

        .modern-navbar .profile-chip:hover,
        .modern-navbar .profile-chip:focus,
        .modern-navbar .profile-chip:active,
        .modern-navbar .profile-chip.show {
            border-color: rgba(59, 130, 246, .45);
            color: #1d4ed8;
            background-color: transparent; /* Override Bootstrap nav-link hover bg */
        }



        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: 0.7rem 1.1rem;
            margin: 0.25rem 1rem !important;
            /* Force margin override */
            border-radius: 0.95rem;
            font-weight: 600;
            color: #4b5563;
            text-decoration: none !important;
            /* Prevent underlines */
            border-bottom: none;
            font-size: 0.9rem;
            line-height: 1.25rem;
            min-height: 2.4rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all .2s ease;
        }

        .sidebar-nav-link i {
            width: 1.25rem;
            text-align: center;
        }

        .sidebar-nav-link:hover,
        .sidebar-nav-link.is-active {
            background-image: var(--brand-gradient);
            color: #fff;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);
            text-decoration: none;
            border-bottom: none;
        }

        .sidebar-nav-link.is-active i {
            color: #fff;
        }

        .sidebar-section-title {
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            color: #94a3b8;
            padding: 0.75rem 1.5rem 0.5rem;
        }

        .app-shell {
            min-height: calc(100vh - 4rem);
            padding-top: 4.5rem;
            /* Space for fixed navbar */
        }

        .app-main {
            padding: 1.5rem 0.75rem 2rem;
            /* Default padding for content (desktop/tablet) */
            min-height: calc(100vh - 4rem);
            transition: margin-left .3s ease;
        }

        .app-main-inner {
            transition: border-radius .2s ease, box-shadow .2s ease, background-color .2s ease, padding .2s ease;
        }

        @media (max-width: 768px) {
            .app-shell {
                padding-top: 4rem;
                /* Adjusted space for mobile navbar */
            }

            .app-main {
                padding: 0.5rem 0rem 1rem;
                /* Much tighter horizontal padding on mobile */
            }

            .app-main-inner {
                background-color: #ffffff;
                border-radius: 1rem;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
                margin: 0.5rem;
                padding: 1rem 0.75rem;
            }

            /* Better File Input for Mobile */
            .file-input-mobile {
                display: block;
                width: 100%;
                font-size: 0.85rem;
                color: #475569;
                border: 1px solid #cbd5e1;
                border-radius: 0.5rem;
                padding: 0;
                background: #fff;
            }

            .file-input-mobile::-webkit-file-upload-button {
                padding: 0.5rem 0.85rem;
                margin-right: 0.6rem;
                border: none;
                border-right: 1px solid #cbd5e1;
                background: #f1f5f9;
                color: #1e293b;
                font-weight: 700;
                font-size: 0.8rem;
                cursor: pointer;
            }

            .file-input-mobile:hover::-webkit-file-upload-button {
                background: #e2e8f0;
            }

            .existing-file-info {
                display: flex;
                flex-direction: column;
                margin-top: 0.4rem;
                padding: 0.5rem 0.75rem;
                background: #f8fafc;
                border-radius: 0.5rem;
                border: 1px dashed #cbd5e1;
            }

            /* Common white card on dashboards and forms (third-level child) */
            #main-content>.app-main-inner>div>div>.bg-white {
                border-radius: 0.85rem;
            }

            .app-name-text {
                font-size: 0.9rem;
                max-width: 140px;
            }

            .modern-brand-icon {
                width: 36px;
                height: 36px;
            }
        }

        .app-sidebar {
            width: 16rem;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0; /* Ensures it fills to the bottom of the viewport regardless of URL bar */
            height: auto !important; /* Override any height classes */
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(18px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, .15);
            transform: translateX(-100%);
            transition: transform .3s ease;
            z-index: 40;
            padding-top: 4.5rem;
            padding-bottom: 5rem; /* Large enough padding for mobile bars */
            padding-bottom: calc(5rem + env(safe-area-inset-bottom));
            /* Account for fixed navbar */
            overscroll-behavior: contain; /* Prevents scroll chaining to background */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling for iOS Safari */
        }

        @media (max-width: 1023px) {
            .app-sidebar {
                transform: translateX(-100%) !important;
                padding-top: 4rem;
                /* Adjust for mobile navbar height */
            }

            body.sidebar-mobile-open .app-sidebar {
                transform: translateX(0);
            }
        }

        @media (min-width: 1024px) {
            .app-sidebar {
                padding-top: 4.5rem;
                /* Desktop navbar padding */
                transform: translateX(0) !important;
                /* Ensure sidebar is visible by default on desktop */
            }

            body.sidebar-desktop-collapsed .app-sidebar {
                transform: translateX(-100%) !important;
            }
        }

        .app-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .app-sidebar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .6);
            border-radius: 9999px;
        }

        body.sidebar-mobile-open .app-sidebar,
        .app-sidebar.is-open {
            transform: translateX(0) !important;
        }

        @media (min-width: 1024px) {
            .app-sidebar {
                transform: translateX(0) !important;
            }

            body.sidebar-desktop-collapsed .app-sidebar {
                transform: translateX(-100%) !important;
            }
        }

        @media (max-width: 1023px) {
            .app-sidebar {
                transform: translateX(-100%) !important;
            }

            body.sidebar-mobile-open .app-sidebar {
                transform: translateX(0);
            }
        }

        .app-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 30;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        @media (min-width: 1024px) {
            .app-sidebar-overlay {
                display: none;
            }
        }

        body.sidebar-mobile-open .app-sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        body.sidebar-mobile-open {
            overflow: hidden;
        }

        footer {
            transition: margin-left .3s ease;
        }

        @media (min-width: 1024px) {
            .app-main {
                margin-left: 16rem;
            }

            footer {
                margin-left: 16rem;
            }

            body.sidebar-desktop-collapsed .app-main {
                margin-left: 0;
            }

            body.sidebar-desktop-collapsed footer {
                margin-left: 0;
            }
        }

        .app-table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .app-table-wrapper table {
            width: 100%;
        }

        @media (max-width: 1023px) {
            .app-table-wrapper table {
                min-width: 720px;
            }
        }

        @media (max-width: 640px) {
            .app-table-wrapper {
                margin: 0 -0.75rem 1rem;
                padding: 0.25rem 0.75rem 0.75rem;
            }

            .app-table-wrapper table {
                font-size: 0.8rem;
            }

            .app-table-wrapper th,
            .app-table-wrapper td {
                padding: 0.5rem 0.75rem;
                white-space: nowrap;
            }
        }

        /* High specificity to override Bootstrap .navbar-expand-lg .navbar-nav .nav-link (0-3-0) */
        .modern-navbar .navbar-nav .nav-item .navbar-notif-link {
            border-radius: 9999px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            box-sizing: border-box;
            background: rgba(148, 163, 184, 0.12);
            color: #0f172a;
            transition: all .2s ease;
            position: relative;
        }

        .modern-navbar .navbar-nav .nav-item .navbar-notif-link:hover,
        .modern-navbar .navbar-nav .nav-item .navbar-notif-link:focus, 
        .modern-navbar .navbar-nav .nav-item .navbar-notif-link.show {
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
        }

        .navbar-notif-menu {
            max-height: 360px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, .35);
            padding: 0.25rem 0;
        }

        .navbar-notif-item-title {
            font-size: 0.8rem;
        }

        .navbar-notif-item-text {
            font-size: 0.75rem;
        }

        /* High specificity to ensure custom border-radius applies over Bootstrap defaults */
        .modern-navbar .dropdown-menu.navbar-dropdown-menu {
            border-radius: 1.5rem; /* Significantly more rounded */
            border: 1px solid rgba(148, 163, 184, .35);
            padding: 0.5rem;
        }

        /* Fix slight dropdown offset on the right side of navbar (profile dropdown) */
        .navbar .dropdown-menu.dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }

        .navbar .profile-chip {
            position: relative;
        }

        .navbar .profile-chip+.dropdown-menu {
            right: 0 !important;
            left: auto !important;
            margin-top: 0.5rem;
        }

        /* Keep navbar dropdown alignment consistent (bell + profile) */
        .navbar .dropdown-menu {
            margin-top: 0.5rem;
        }

        .navbar-nav .nav-item.dropdown {
            display: flex;
            align-items: center;
        }

        /* High specificity to ensure border-radius works without !important */
        .modern-navbar .dropdown-menu .navbar-dropdown-item {
            display: flex;
            align-items: center;
            border-radius: 0.75rem; /* This rounds the corners */
            margin: 2px 0;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Hover states */
        .modern-navbar .dropdown-menu .navbar-dropdown-item:active,
        .modern-navbar .dropdown-menu .navbar-dropdown-item:focus,
        .modern-navbar .dropdown-menu .navbar-dropdown-item:hover {
            background-color: rgba(37, 99, 235, .08);
            color: #0f172a;
            border-radius: 0.75rem; /* Reinforce rounded corners on hover */
        }
        
        /* Preserve text-danger color on hover */
        .modern-navbar .dropdown-menu .navbar-dropdown-item.text-danger:active,
        .modern-navbar .dropdown-menu .navbar-dropdown-item.text-danger:focus,
        .modern-navbar .dropdown-menu .navbar-dropdown-item.text-danger:hover {
            color: #dc3545 !important;
            background-color: rgba(220, 53, 69, .08);
        }

        .sidebar-user-name {
            font-size: 0.9rem;
        }

        .sidebar-user-meta {
            font-size: 0.7rem;
        }

        /* Responsive tweaks for signature canvas on mobile */
        @media (max-width: 640px) {

            .signature-pad-wrapper-p1,
            .signature-pad-wrapper-p2,
            .signature-pad-wrapper-pembahas {
                margin-left: -1.25rem;
                margin-right: -1.25rem;
            }

            .signature-pad-wrapper-p1 canvas,
            .signature-pad-wrapper-p2 canvas,
            .signature-pad-wrapper-pembahas canvas {
                width: 100% !important;
                height: 180px !important;
            }
        }

        /* Mobile floating notification dropdown */
        @media (max-width: 768px) {
            .navbar-notif-menu {
                position: fixed !important;
                top: 56px;
                right: 0.75rem;
                left: auto !important;
                width: min(320px, 100% - 1.5rem);
                transform: none !important;
                z-index: 1055;
            }
        }
        @if($isImpersonating)
            .impersonate-banner {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 40px;
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
                z-index: 1060;
                display: flex;
                align-items: center;
                padding: 0 1rem;
                font-size: 0.875rem;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .modern-navbar { top: 40px !important; }
            .app-sidebar { top: 40px !important; }
            .app-shell { padding-top: 40px !important; }
            @media (max-width: 1023px) {
                .navbar-notif-menu { top: 96px !important; }
            }
            #toast-container { top: calc(5rem + 40px) !important; }
        @endif
    </style>
</head>

<body class="min-h-screen bg-gray-50" data-user-role="{{ $currentGuard ?? 'guest' }}">
    @if($isImpersonating)
    <div class="impersonate-banner">
        <div class="container-fluid flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-user-secret"></i>
                <span><strong>Mode Impersonate:</strong> Login sebagai <strong>{{ $currentUser->nama ?? $currentUser->name }}</strong></span>
            </div>
            <a href="{{ route('impersonate.leave') }}" class="bg-white text-amber-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-amber-50 transition-colors" data-no-ajax>
                Kembali ke Admin
            </a>
        </div>
    </div>
    @endif
    <!-- Navigation -->
    <!-- Navigation (Pure Tailwind) -->
    <nav class="modern-navbar fixed top-0 left-0 right-0 z-50 flex h-16 w-full items-center border-b border-slate-200/60 bg-white/90 px-4 backdrop-blur-md transition-all sm:px-6">
        <div class="flex w-full items-center justify-between">
            <!-- Left: Sidebar Toggle & Brand -->
            <div class="flex items-center gap-4">
                <button id="sidebarToggle" class="nav-icon-btn flex h-10 w-10 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-primary-600 focus:outline-none" type="button" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <a class="flex items-center gap-3" href="
                    @if($currentGuard === 'admin')
                        {{ route('admin.dashboard') }}
                    @elseif($currentGuard === 'dosen')
                        {{ route('dosen.dashboard') }}
                    @elseif($currentGuard === 'mahasiswa')
                        {{ route('mahasiswa.dashboard') }}
                    @else
                        {{ route('login') }}
                    @endif
                ">
                    <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl shadow-sm">
                        @if($appIcon)
                            <img src="{{ $appIcon }}?v={{ optional($brandingSettings)->updated_at?->timestamp ?? time() }}" alt="App Icon" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-primary-50 text-primary-600">
                                <i class="fas fa-university text-lg"></i>
                            </div>
                        @endif
                    </span>
                    <span class="hidden text-lg font-bold tracking-tight text-slate-800 sm:block">{{ $appName }}</span>
                </a>
            </div>

            <!-- Right: Notifications & Profile -->
            <div class="flex items-center gap-2 sm:gap-4">
                @if($currentUser)
                    @php
                        $notif = $navbarNotifications ?? ['items' => [], 'count' => 0, 'guard' => $currentGuard];
                    @endphp
                    
                    <!-- Notification Dropdown -->
                    <div class="relative" id="notifDropdownContainer">
                        <button class="nav-icon-btn relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-primary-600 focus:outline-none" 
                                id="notifBtn" 
                                onclick="toggleNavbarDropdown('notifMenu')">
                            <i class="fas fa-bell text-lg"></i>
                            @if(($notif['count'] ?? 0) > 0)
                                <span class="hidden absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                                     {{ $notif['count'] > 9 ? '9+' : $notif['count'] }}
                                 </span>
                            @endif
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="notifMenu" class="absolute right-0 mt-3 hidden w-80 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl ring-1 ring-slate-900/5 transition-all md:w-96">
                            <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3">
                                <h3 class="text-sm font-semibold text-slate-900">Notifikasi</h3>
                            </div>
                            <ul class="max-h-[24rem] overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
                                @forelse($notif['items'] as $item)
                                    <li>
                                        <a class="block border-b border-slate-50 px-4 py-3 hover:bg-slate-50" 
                                           href="{{ $item['url'] ?? '#' }}"
                                           data-notif-key="{{ $item['key'] ?? '' }}">
                                            <div class="mb-0.5 text-sm font-semibold text-slate-800">{{ $item['title'] ?? 'Notifikasi' }}</div>
                                            <div class="text-xs leading-relaxed text-slate-500">{{ $item['message'] ?? '' }}</div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-4 py-8 text-center">
                                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                            <i class="far fa-bell-slash"></i>
                                        </div>
                                        <p class="text-sm text-slate-500">Belum ada notifikasi baru</p>
                                    </li>
                                @endforelse
                            </ul>
                            {{-- Notification footer removed --}}
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" id="profileDropdownContainer">
                        <button class="hidden md:flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-sm transition-all hover:border-slate-300 hover:shadow-md focus:outline-none" 
                                id="profileBtn" 
                                onclick="toggleNavbarDropdown('profileMenu')">
                            
                            <span class="text-sm font-semibold text-slate-700">{{ $currentUser->nama ?? $currentUser->name }}</span>
                            <i class="fas fa-chevron-down ml-1 text-xs text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="profileMenu" class="absolute right-0 mt-3 hidden w-56 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl ring-1 ring-slate-900/5 transition-all">
                            <div class="border-b border-slate-100 bg-slate-50/50 px-4 py-3 sm:hidden">
                                <div class="font-semibold text-slate-900">{{ $currentUser->nama ?? $currentUser->name }}</div>
                                <div class="text-xs text-slate-500">{{ $currentRoleLabel }}</div>
                            </div>
                            <div class="p-1">
                                <a class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-blue-600" href="
                                    @if($currentGuard === 'admin') {{ route('admin.profile.edit') }}
                                    @elseif($currentGuard === 'dosen') {{ route('dosen.profile.edit') }}
                                    @elseif($currentGuard === 'mahasiswa') {{ route('mahasiswa.profile.edit') }}
                                    @endif
                                ">
                                    <i class="fas fa-user-circle w-5 text-center text-slate-400 transition-colors group-hover:text-blue-600"></i>
                                    Profil Saya
                                </a>
                                <div class="my-1 h-px bg-slate-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt w-5 text-center text-red-400 group-hover:text-red-500"></i>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Navbar Dropdown Logic -->
    <script>
        function toggleNavbarDropdown(targetId) {
            const el = document.getElementById(targetId);
            if (!el) return;
            
            // Toggle visibility
            const isHidden = el.classList.contains('hidden');
            
            // Close all others first
            document.querySelectorAll('#notifMenu, #profileMenu').forEach(menu => {
                menu.classList.add('hidden');
            });

            if (isHidden) {
                el.classList.remove('hidden');
            }
        }

        // Close on click outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('#notifDropdownContainer') && !e.target.closest('#profileDropdownContainer')) {
                 document.querySelectorAll('#notifMenu, #profileMenu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });
    </script>

    <!-- Sidebar and Content -->
    <div class="app-shell w-full"> <!-- Padding handled by CSS for fixed navbar spacing -->
        <!-- Sidebar -->
        <aside id="sidebar"
            class="app-sidebar w-64 bg-white/95 backdrop-blur shadow-2xl fixed left-0 top-0 z-40 transform transition-transform duration-300 ease-in-out overflow-y-auto">
            <!-- User Profile Section -->
            @if($currentUser)
                <div class="p-4 border-b bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
                    <div class="flex items-center">
                        @if($currentUser->foto ?? false)
                            <img src="{{ asset('uploads/' . $currentUser->foto) }}" alt="Profile Photo"
                                class="w-16 h-16 rounded-xl object-cover border-2 border-white" loading="eager"
                                decoding="async">
                        @else
                            <div
                                class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center text-gray-500">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                        @endif
                        <div class="ml-3 overflow-hidden">
                            <h3 class="sidebar-user-name font-semibold truncate m-0 leading-tight">
                                {{ $currentUser->nama ?? $currentUser->name ?? 'User' }}
                            </h3>
                            <p class="sidebar-user-meta opacity-80 mt-1 mb-0 leading-tight">
                                @if($currentGuard === 'admin')
                                    <i class="fas fa-user-shield text-yellow-300 me-1"></i> Admin
                                @elseif($currentGuard === 'dosen')
                                    <i class="fas fa-chalkboard-teacher text-emerald-300 me-1"></i> Dosen
                                @elseif($currentGuard === 'mahasiswa')
                                    <i class="fas fa-graduation-cap text-sky-300 me-1"></i> Mahasiswa
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-col gap-1 sidebar-user-meta text-white">
                        @if($currentGuard === 'admin' || $currentGuard === 'dosen')
                            <p class="m-0"><i class="fas fa-id-card me-1"></i> NIP: {{ $currentUser->nip ?? 'N/A' }}</p>
                        @elseif($currentGuard === 'mahasiswa')
                            <p class="m-0"><i class="fas fa-graduation-cap me-1"></i> NPM: {{ $currentUser->npm ?? 'N/A' }}</p>
                        @endif
                        <p class="m-0"><i class="fas fa-envelope me-1"></i> {{ $currentUser->email }}</p>
                    </div>
                </div>

                <!-- Navigation items based on role -->
                <div class="sidebar-section-title">Navigasi</div>
                <ul class="py-2 pb-32 m-0 list-none ps-0">
                    @if($currentGuard === 'admin')
                        <li>
                            <a href="{{ route('admin.dashboard') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.dosen.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.dosen.*') ? 'is-active' : '' }}">
                                <i class="fas fa-chalkboard-teacher"></i> Kelola Dosen
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.mahasiswa.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.mahasiswa.*') ? 'is-active' : '' }}">
                                <i class="fas fa-user-graduate"></i>
                                <span>Kelola Mahasiswa</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pa.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.pa.*') ? 'is-active' : '' }}">
                                <i class="fas fa-users-cog"></i>
                                <span>Pembimbing Akad.</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.seminar.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.seminar.*') ? 'is-active' : '' }}">
                                <i class="fas fa-calendar-check"></i> Kelola Seminar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.seminarjenis.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.seminarjenis.*') ? 'is-active' : '' }}">
                                <i class="fas fa-layer-group"></i> Kelola Jenis Seminar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.surat.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.surat.*') ? 'is-active' : '' }}">
                                <i class="fas fa-mail-bulk"></i> Kelola Surat
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.suratjenis.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.suratjenis.*') ? 'is-active' : '' }}">
                                <i class="fas fa-envelope-open-text"></i> Kelola Jenis Surat
                            </a>
                        </li>
                        
                        {{-- Approval System Menu (Admin) --}}
                        <li>
                            <a href="{{ route('admin.surat-role.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.surat-role.*') ? 'is-active' : '' }}">
                                <i class="fas fa-user-tie"></i> Role Persetujuan
                            </a>
                        </li>
                        

                        
                        <li>
                            <a href="{{ route('admin.gdrive.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.gdrive.*') ? 'is-active' : '' }}">
                                <i class="fas fa-folder-open"></i> Folder GDrive
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.admins.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.admins.*') ? 'is-active' : '' }}">
                                <i class="fas fa-user-shield"></i> Kelola Admin
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings.landing') }}"
                                class="sidebar-nav-link {{ request()->routeIs('admin.settings.landing*') ? 'is-active' : '' }}">
                                <i class="fas fa-palette"></i> Kelola Beranda
                            </a>
                        </li>
                    @elseif($currentGuard === 'dosen')
                        <li>
                            <a href="{{ route('dosen.dashboard') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.dashboard') ? 'is-active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.evaluasi.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.evaluasi.*') ? 'is-active' : '' }}">
                                <i class="fas fa-clipboard-list"></i> Tugas Evaluasi
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.manage-seminar.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.manage-seminar.*') ? 'is-active' : '' }}">
                                <i class="fas fa-calendar-check"></i> Kelola Seminar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.surat.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.surat.*') ? 'is-active' : '' }}">
                                <i class="fas fa-envelope"></i> Permohonan Surat
                            </a>
                        </li>
                        
                        {{-- Approval Dashboard Menu (Dosen) - Renamed to Kelola Surat --}}
                        @if($currentUser && $currentUser->exists)
                            @php
                                // Check if dosen has any approvals at all (past or present)
                                $hasAnyApprovals = \App\Models\SuratApproval::where('dosen_id', $currentUser->id)->exists();

                                // Check if user has explicit SuratRole (Kajur, Sekjur, etc) - for persistent menu access
                                $hasSuratRole = \App\Models\SuratRole::where('dosen_id', $currentUser->id)->exists();
                            @endphp
                            
                            @if($hasAnyApprovals || $hasSuratRole)
                            <li>
                                <a href="{{ route('admin.approval.dashboard') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.approval.dashboard') ? 'is-active' : '' }}">
                                    <i class="fas fa-file-signature"></i> 
                                    <span>Kelola Surat</span>
                                </a>
                            </li>
                            @endif

                            <li>
                                <a href="{{ route('admin.approval.stamping.index') }}"
                                    class="sidebar-nav-link {{ request()->routeIs('admin.approval.stamping.*') ? 'is-active' : '' }}">
                                    <i class="fas fa-stamp"></i> Pembubuhan TTD
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('dosen.mahasiswa.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.mahasiswa.*') && !request('filter') ? 'is-active' : '' }}">
                                <i class="fas fa-user-graduate"></i> Profil Mahasiswa
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.mahasiswa.index', ['filter' => 'pa']) }}"
                                class="sidebar-nav-link {{ request('filter') === 'pa' ? 'is-active' : '' }}">
                                <i class="fas fa-user-check"></i> Mhs. Bimbingan Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dosen.gdrive.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('dosen.gdrive.*') ? 'is-active' : '' }}">
                                <i class="fas fa-folder-open"></i> Folder GDrive
                            </a>
                        </li>
                    @elseif($currentGuard === 'mahasiswa')
                        <li>
                            <a href="{{ route('mahasiswa.dashboard') }}"
                                class="sidebar-nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'is-active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mahasiswa.seminar.register') }}"
                                class="sidebar-nav-link {{ request()->routeIs('mahasiswa.seminar.*') ? 'is-active' : '' }}">
                                <i class="fas fa-plus-circle"></i> Daftar Seminar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mahasiswa.dashboard') }}#seminar-saya" class="sidebar-nav-link">
                                <i class="fas fa-calendar-check"></i> Seminar Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mahasiswa.surat.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('mahasiswa.surat.*') ? 'is-active' : '' }}">
                                <i class="fas fa-envelope"></i> Permohonan Surat
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mahasiswa.dosen.index') }}"
                                class="sidebar-nav-link {{ request()->routeIs('mahasiswa.dosen.*') ? 'is-active' : '' }}">
                                <i class="fas fa-chalkboard-teacher"></i> Daftar Dosen
                            </a>
                        </li>
                    @endif



                    <div class="sidebar-section-title">Akun</div>
                    <li>
                        <a href="{{ route($currentGuard . '.profile.edit') }}"
                            class="sidebar-nav-link {{ request()->routeIs($currentGuard . '.profile.*') ? 'is-active' : '' }}">
                            <i class="fas fa-user"></i> Profil Saya
                        </a>
                    </li>
                    <!-- Logout -->
                    <li>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"
                            class="sidebar-nav-link text-red-500 hover:text-red-100 hover:bg-rose-500">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>

                <!-- Hidden logout form -->
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            @endif
        </aside>

        <div id="sidebarOverlay" class="app-sidebar-overlay lg:hidden"></div>

        <!-- Main Content Area -->
        <main id="main-content" class="app-main flex-1 transition-all duration-300 ease-in-out">
            <!-- Page Content -->
            <div class="w-full max-w-screen-2xl 2xl:max-w-screen-3xl mx-auto app-main-inner">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-10 border-t">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Bihikmi. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2">
        <!-- Toasts will be injected here by JavaScript -->
    </div>

    <div id="page-scripts">
        @yield('scripts')
        @stack('scripts')
    </div>

    <!-- Flash Messages Container (hidden) -->
    <div id="flash-messages" style="display: none;">
        @if(session('success'))
            <span data-flash-success="{{ session('success') }}"></span>
        @endif
        @if(session('error'))
            <span data-flash-error="{{ session('error') }}"></span>
        @endif
        @if(session('warning'))
            <span data-flash-warning="{{ session('warning') }}"></span>
        @endif
        @if(session('info'))
            <span data-flash-info="{{ session('info') }}"></span>
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                <span data-flash-error="{{ $error }}"></span>
            @endforeach
        @endif
    </div>
    <!-- Quill Editor CSS & JS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        // Notification system reinitialize function
        function reinitializeNotifications() {
            // Only run if notification elements exist
            if (!document.querySelector('#notifDropdownContainer')) {
                return;
            }

            const STORAGE_KEY = 'app.navbar.readNotifications';

            let readKeys = [];
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                readKeys = raw ? JSON.parse(raw) : [];
            } catch (e) {
                readKeys = [];
            }

            const notifLinks = Array.from(document.querySelectorAll('[data-notif-key]'));

            function isVisibleNotif(el) {
                const li = el.closest('li');
                return li && li.style.display !== 'none';
            }

            function updateBadge() {
                const badge = document.querySelector('#notifBtn span');
                if (!badge) return;

                const visible = notifLinks.filter(isVisibleNotif).length;
                if (visible <= 0) {
                    badge.classList.add('hidden');
                } else {
                    badge.classList.remove('hidden');
                    badge.textContent = visible > 9 ? '9+' : String(visible);
                }
            }

            // Hide already-read notifications
            notifLinks.forEach(link => {
                const key = link.getAttribute('data-notif-key');
                if (key && readKeys.includes(key)) {
                    const li = link.closest('li');
                    if (li) li.style.display = 'none';
                }
            });

            // Initial badge update run immediately to prevent flicker
            updateBadge();

            // When user clicks a notification, mark as read locally
            notifLinks.forEach(link => {
                link.removeEventListener('click', handleNotificationClick); // Remove existing listener
                link.addEventListener('click', handleNotificationClick); // Add new listener
            });

            function handleNotificationClick(e) {
                const key = this.getAttribute('data-notif-key');
                if (!key) return;

                if (!readKeys.includes(key)) {
                    readKeys.push(key);
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(readKeys));
                    } catch (e) {
                        // ignore storage errors
                    }
                }

                const li = this.closest('li');
                if (li) li.style.display = 'none';
                updateBadge();
            }
        }

        // AJAX navigation system - Exposed globally for Live Search integration
        window.loadPageContent = function (url, pushState = true, subtle = false) {
            const parsedUrl = new URL(url, window.location.origin);
            const requestUrl = parsedUrl.pathname + parsedUrl.search;
            const targetHash = parsedUrl.hash;

            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                if (subtle) {
                    mainContent.style.transition = 'opacity 0.2s ease-in-out';
                    mainContent.style.opacity = '0.5';
                } else {
                    mainContent.innerHTML = '<div class="flex justify-center items-center h-64"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div></div>';
                }
            }

            fetch(requestUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Reset opacity if subtle loading was used
                    if (mainContent && subtle) {
                        mainContent.style.opacity = '1';
                    }

                    // Parse the response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Extract the main content from the fetched page
                    const newMainContent = doc.getElementById('main-content');
                    const newTitle = doc.querySelector('title');

                    if (newMainContent) {
                        // Persistence: Store current active element and its selection state
                        const activeId = document.activeElement ? document.activeElement.id : null;
                        const selectionStart = document.activeElement ? document.activeElement.selectionStart : null;
                        const selectionEnd = document.activeElement ? document.activeElement.selectionEnd : null;

                        // Update the main content
                        if (mainContent) {
                            mainContent.innerHTML = newMainContent.innerHTML;
                        }

                        // Persistence: Restore focus and selection
                        if (activeId) {
                            const newActiveElement = document.getElementById(activeId);
                            if (newActiveElement) {
                                newActiveElement.focus();
                                if (selectionStart !== null && (newActiveElement.type === 'text' || newActiveElement.type === 'search')) {
                                    newActiveElement.setSelectionRange(selectionStart, selectionEnd);
                                }
                            }
                        }
                    }

                    // Execute page-specific scripts when content is loaded via AJAX navigation.
                    document.querySelectorAll('script[data-ajax-script="1"]').forEach((el) => el.remove());

                    const injectScriptsFrom = async (rootEl) => {
                        if (!rootEl) return;
                        const scripts = Array.from(rootEl.querySelectorAll('script'));
                        for (const s of scripts) {
                            const type = (s.getAttribute('type') || '').toLowerCase();
                            if (type === 'application/json') continue;

                            const script = document.createElement('script');
                            script.setAttribute('data-ajax-script', '1');

                            Array.from(s.attributes).forEach((attr) => {
                                const name = (attr.name || '').toLowerCase();
                                if (!name || name === 'src' || name === 'id' || name === 'data-ajax-script') return;
                                script.setAttribute(attr.name, attr.value);
                            });

                            if (s.src) {
                                script.src = s.src;
                                await new Promise((resolve) => {
                                    script.onload = resolve;
                                    script.onerror = () => {
                                        console.warn('Failed to load AJAX script:', s.src);
                                        resolve();
                                    };
                                    document.body.appendChild(script);
                                });
                            } else {
                                script.textContent = s.textContent || '';
                                document.body.appendChild(script);
                            }
                        }
                    };

                    (async function() {
                        await injectScriptsFrom(newMainContent);
                        const pageScripts = doc.getElementById('page-scripts');
                        await injectScriptsFrom(pageScripts);

                        if (newTitle) {
                            document.title = newTitle.textContent;
                        }

                        if (pushState) {
                            const historyUrl = parsedUrl.pathname + parsedUrl.search + targetHash;
                            history.pushState({ url: historyUrl }, '', historyUrl);
                        }

                        reinitializePageScripts(doc, url);
                        window.dispatchEvent(new CustomEvent('page-loaded', { detail: { url: url } }));
                        
                        if (targetHash) {
                            requestAnimationFrame(() => {
                                const target = document.querySelector(targetHash);
                                if (target) {
                                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            });
                        }
                    })();
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    window.location.href = url;
                });
        };

        // Function to reinitialize page-specific scripts - Exposed globally
        window.reinitializePageScripts = function (parsedDoc, url = window.location.href) {
            clearToasts();
            if (parsedDoc) {
                displayFlashMessages(parsedDoc);
            }
            updateSidebarActiveLinks();
            
            // Dispatch app:init to signal components like Live Search to re-initialize
            window.dispatchEvent(new CustomEvent('app:init', { detail: { url: url } }));

            requestAnimationFrame(function () {
                reinitializeNotifications();
            });
        };

        // Sidebar active link update helper - Exposed globally
        window.updateSidebarActiveLinks = function() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            const links = Array.from(sidebar.querySelectorAll('a.sidebar-nav-link'))
                .filter((a) => a instanceof HTMLAnchorElement);

            links.forEach((a) => a.classList.remove('is-active'));

            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const currentSearch = window.location.search;
            const current = currentPath + currentSearch;

            let best = null;
            let bestLen = -1;

            links.forEach((a) => {
                try {
                    const u = new URL(a.href, window.location.origin);
                    if (u.origin !== window.location.origin) return;

                    const linkPath = u.pathname.replace(/\/+$/, '') || '/';
                    const link = linkPath + (u.search || '');

                    if (link === current && linkPath.length > bestLen) {
                        best = a;
                        bestLen = linkPath.length;
                        return;
                    }

                    if (currentPath !== '/' && linkPath !== '/' && currentPath.startsWith(linkPath) && linkPath.length > bestLen) {
                        best = a;
                        bestLen = linkPath.length;
                    }
                } catch (e) {}
            });

            if (best) {
                best.classList.add('is-active');
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Handle navigation links with AJAX
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!link || link.hasAttribute('target') || link.hasAttribute('download')) return;
                if (link.hasAttribute('onclick') || link.hasAttribute('data-no-ajax')) return;

                const href = link.getAttribute('href') || '';
                if (!href || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:') || href.startsWith('#')) return;

                let url;
                try {
                    url = new URL(href, window.location.origin);
                } catch (e) { return; }

                if (url.origin !== window.location.origin) return;

                event.preventDefault();
                window.loadPageContent(url.href);
            });

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function (event) {
                if (event.state && event.state.url) {
                    window.loadPageContent(event.state.url, false);
                }
            });

            // Initial setup for the current page
            window.reinitializePageScripts();
        });

        // Toast notification system
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            // Toast styles based on type
            let bgClass, iconClass, titleText;

            switch (type) {
                case 'success':
                    bgClass = 'bg-green-100 border-green-500 text-green-800';
                    iconClass = 'fa-check-circle text-green-500';
                    titleText = 'Success';
                    break;
                case 'error':
                    bgClass = 'bg-red-100 border-red-500 text-red-800';
                    iconClass = 'fa-exclamation-circle text-red-500';
                    titleText = 'Error';
                    break;
                case 'warning':
                    bgClass = 'bg-yellow-100 border-yellow-500 text-yellow-800';
                    iconClass = 'fa-exclamation-triangle text-yellow-500';
                    titleText = 'Warning';
                    break;
                default:
                    bgClass = 'bg-blue-100 border-blue-500 text-blue-800';
                    iconClass = 'fa-info-circle text-blue-500';
                    titleText = 'Info';
            }

            // Create toast element
            toast.className = `flex items-center w-full max-w-xs p-4 mb-2 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg ${bgClass.split(' ')[0]}">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="ml-3 text-sm font-normal text-gray-800">${message}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" onclick="this.parentElement.remove()">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    if (toast.parentElement) toast.remove();
                }, 300);
            }, 5000);
        }

        // Clear existing toasts function
        function clearToasts() {
            const container = document.getElementById('toast-container');
            if (container) {
                container.innerHTML = '';
            }
        }

        // Display flash messages from DOM elements
        function displayFlashMessages(doc = document) {
            // Check for flash messages in the document
            const successElements = doc.querySelectorAll('[data-flash-success]');
            const errorElements = doc.querySelectorAll('[data-flash-error]');
            const warningElements = doc.querySelectorAll('[data-flash-warning]');
            const infoElements = doc.querySelectorAll('[data-flash-info]');

            successElements.forEach(el => {
                showToast(el.getAttribute('data-flash-success'), 'success');
            });

            errorElements.forEach(el => {
                showToast(el.getAttribute('data-flash-error'), 'error');
            });

            warningElements.forEach(el => {
                showToast(el.getAttribute('data-flash-warning'), 'warning');
            });

            infoElements.forEach(el => {
                showToast(el.getAttribute('data-flash-info'), 'info');
            });
        }

        // Display server-side session flash messages (only for initial load)
        function displaySessionFlashMessages() {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            @if(session('warning'))
                showToast("{{ session('warning') }}", 'warning');
            @endif

            @if(session('info'))
                showToast("{{ session('info') }}", 'info');
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast("{{ $error }}", 'error');
                @endforeach
            @endif
        }

        // Display flash messages on initial page load
        document.addEventListener('DOMContentLoaded', function () {
            displayFlashMessages();
            // Note: displaySessionFlashMessages removed to prevent duplicate toasts
            // Flash messages are rendered as DOM elements and read by displayFlashMessages
        });

        const seminarFileRouteTemplate = @json(route('admin.seminar.files.show', ['path' => '__PATH__']));

        function openPdf(filename) {
            if (!filename) {
                return;
            }

            const encoded = encodeURIComponent(filename);
            const targetUrl = seminarFileRouteTemplate.replace('__PATH__', encoded);
            window.open(targetUrl, '_blank');
        }

        // Sidebar interactions & responsive helpers
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const body = document.body;
            const desktopQuery = window.matchMedia('(min-width: 1024px)');
            const STORAGE_KEY = 'app.sidebar.desktopCollapsed';

            const storage = {
                get() {
                    try {
                        return localStorage.getItem(STORAGE_KEY) === '1';
                    } catch (error) {
                        return false;
                    }
                },
                set(value) {
                    try {
                        localStorage.setItem(STORAGE_KEY, value ? '1' : '0');
                    } catch (error) {
                        // ignore
                    }
                }
            };

            const setAriaExpanded = (expanded) => {
                if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                }
            };

            const syncAriaState = () => {
                if (desktopQuery.matches) {
                    setAriaExpanded(!body.classList.contains('sidebar-desktop-collapsed'));
                    return;
                }

                setAriaExpanded(body.classList.contains('sidebar-mobile-open'));
            };

            const applyDesktopPreference = () => {
                if (!desktopQuery.matches) {
                    body.classList.remove('sidebar-desktop-collapsed');
                    return;
                }

                const collapsed = storage.get();
                body.classList.toggle('sidebar-desktop-collapsed', collapsed);
            };

            const closeMobileSidebar = (returnFocus = false) => {
                if (body.classList.contains('sidebar-mobile-open')) {
                    body.classList.remove('sidebar-mobile-open');

                    if (sidebar) {
                        sidebar.classList.remove('is-open');
                    }
                }

                if (returnFocus && toggleButton) {
                    toggleButton.focus();
                }

                syncAriaState();
            };

            const openMobileSidebar = () => {
                if (!body.classList.contains('sidebar-mobile-open')) {
                    body.classList.add('sidebar-mobile-open');
                }

                if (sidebar) {
                    sidebar.classList.add('is-open');
                }

                syncAriaState();
            };

            const handleToggle = () => {
                if (!sidebar) {
                    return;
                }

                if (desktopQuery.matches) {
                    const willCollapse = !body.classList.contains('sidebar-desktop-collapsed');
                    body.classList.toggle('sidebar-desktop-collapsed', willCollapse);
                    storage.set(willCollapse);
                    syncAriaState();
                    return;
                }

                if (body.classList.contains('sidebar-mobile-open')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            };

            if (toggleButton) {
                toggleButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    handleToggle();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    closeMobileSidebar(true);
                });
            }

            if (sidebar) {
                sidebar.addEventListener('click', function (event) {
                    if (!desktopQuery.matches && event.target instanceof Element && event.target.closest('a')) {
                        closeMobileSidebar();
                    }
                });
            }

            document.addEventListener('click', function (event) {
                if (desktopQuery.matches) {
                    return;
                }

                if (!body.classList.contains('sidebar-mobile-open')) {
                    return;
                }

                const clickInsideSidebar = sidebar && sidebar.contains(event.target);
                const clickOnToggle = toggleButton && toggleButton.contains(event.target);
                const clickOnOverlay = overlay && overlay.contains(event.target);

                if (!clickInsideSidebar && !clickOnToggle && !clickOnOverlay) {
                    closeMobileSidebar();
                }
            }, true);

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMobileSidebar(true);
                }
            });

            const handleViewportChange = () => {
                if (desktopQuery.matches) {
                    closeMobileSidebar();
                    applyDesktopPreference();
                } else {
                    body.classList.remove('sidebar-desktop-collapsed');
                }

                syncAriaState();
            };

            if (typeof desktopQuery.addEventListener === 'function') {
                desktopQuery.addEventListener('change', handleViewportChange);
            } else if (typeof desktopQuery.addListener === 'function') {
                desktopQuery.addListener(handleViewportChange);
            }

            applyDesktopPreference();
            syncAriaState();
        });

        // Global SPA-aware initialization
        (function() {
            let lastPath = window.location.pathname;
            function triggerInit() {
                console.log('[App] Initializing page scripts...');
                const detail = { url: window.location.href };
                window.dispatchEvent(new CustomEvent('app:init', { detail: detail }));
                window.dispatchEvent(new CustomEvent('page-loaded', { detail: detail }));
            }
            document.addEventListener('DOMContentLoaded', triggerInit);
            document.addEventListener('livewire:navigated', () => {
                const currentPath = window.location.pathname;
                if (currentPath !== lastPath) {
                    lastPath = currentPath;
                    // Small delay to ensure Livewire finished updating DOM
                    setTimeout(triggerInit, 50);
                }
            });
        })();

        // Optimized AJAX Live Search
        window.addEventListener('app:init', function() {
            const searchInputs = document.querySelectorAll('input[name="search"]');
            searchInputs.forEach(input => {
                let debounceTimer;
                let lastValue = input.value;
                const form = input.closest('form');
                if (!form) return;

                const submitViaAjax = () => {
                    const currentVal = input.value.trim();
                    const icon = input.parentElement.querySelector('svg, i');
                    
                    if (currentVal !== lastValue) {
                        lastValue = currentVal;
                        
                        if (icon) icon.classList.add('searching-active');
                        
                        const formData = new FormData(form);
                        const params = new URLSearchParams(formData);
                        const url = new URL(form.action || window.location.href, window.location.origin);
                        
                        // Reset pagination on new search
                        url.searchParams.delete('page');
                        
                        for (const [key, value] of params.entries()) {
                            url.searchParams.set(key, value);
                        }
                        
                        if (window.loadPageContent) {
                            window.loadPageContent(url.href, true, true);
                        } else {
                            form.submit();
                        }
                    } else if (icon) {
                        icon.classList.remove('searching-active');
                    }
                };

                input.addEventListener('input', function() {
                    const icon = input.parentElement.querySelector('svg, i');
                    if (icon && input.value.trim() !== lastValue) {
                        icon.classList.add('searching-active');
                    }
                    
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitViaAjax, 300);
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(debounceTimer);
                        submitViaAjax();
                    }
                });
            });
        });

        window.addEventListener('app:init', function () {
            const tables = document.querySelectorAll('#main-content table');

            tables.forEach(function (table) {
                if (table.closest('.app-table-wrapper')) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'app-table-wrapper';
                const parent = table.parentNode;

                if (parent) {
                    parent.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
        });

        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function () {
            const profileLink = document.getElementById('navbarDropdown');
            const profileMenu = profileLink ? profileLink.nextElementSibling : null;

            if (profileLink && profileMenu) {
                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!profileLink.contains(e.target) && !profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('show');
                    }
                });
            }
        });

    </script>
    @stack('scripts')
</body>

</html>