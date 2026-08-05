<?php
// index.php — Aff Digital (Redesigned & Refactored Edition)
require_once __DIR__ . '/db.php';

$packagesByCategory = [];
$portfoliosList     = [];

try {
    $packagesByCategory = get_active_packages();
    $portfoliosList     = get_active_portfolios();
} catch (Exception $e) {
    $packagesByCategory = [];
    $portfoliosList     = [];
}

// Load Comments
$dataFile = __DIR__ . '/data/comments.json';
if (!file_exists($dataFile) && file_exists(__DIR__ . '/comments.json')) {
    $dataFile = __DIR__ . '/comments.json';
}

$comments = [];
if (file_exists($dataFile)) {
    $raw = file_get_contents($dataFile);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $comments = $decoded;
    }
}

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aff Digital — Jasa Pembuatan Website, Sistem Custom &amp; Foto Video Promosi</title>
    <meta name="description" content="Aff Digital membantu UMKM &amp; Brand mengubah ide bisnis menjadi website berkinerja tinggi, sistem custom (POS, HR, Inventori), serta foto &amp; video promosi profesional.">

    <!-- Favicons -->
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="favicon.ico">
    <link rel="apple-touch-icon" href="assets/images/logo.jpg">

    <!-- Fonts, Icons & Tailwind CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/tailwind.min.css">

    <style>
        :root {
            --ink: #0f172a;
            --paper: #f8fafc;
            --card: #ffffff;
            --muted: #64748b;
            --teal: #2fb8ae;
            --teal-dark: #1f8a82;
            --amber: #e8a33d;
            --border: rgba(15, 23, 42, 0.08);
            --border-dark: rgba(255, 255, 255, 0.1);
            --radius: 20px;
            --shadow-sm: 0 4px 16px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 12px 32px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.12);
        }

        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; background: var(--paper); color: var(--ink);
            font-family: 'Inter', sans-serif; line-height: 1.6;
            -webkit-font-smoothing: antialiased; overflow-x: hidden;
        }
        h1, h2, h3, h4, h5 { font-family: 'Space Grotesk', sans-serif; margin: 0; letter-spacing: -0.03em; }
        a { color: inherit; text-decoration: none; }

        /* BACKGROUND GRID PATTERN */
        .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
        @media (min-width: 640px) { .wrap { padding: 0 24px; } }

        /* HEADER & NAV */
        .site-header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        .header-inner {
            display: flex; align-items: center; justify-content: space-between;
            height: 76px;
        }
        .brand-logo {
            font-family: 'Space Grotesk', sans-serif; font-size: 24px; font-weight: 800;
            color: var(--ink); letter-spacing: -0.04em; display: flex; align-items: center; gap: 10px;
        }
        .brand-logo img { height: 38px; width: auto; border-radius: 8px; object-fit: cover; }
        .brand-logo span.dot { color: var(--teal); }
        .nav-menu { display: flex; align-items: center; gap: 32px; font-size: 14px; font-weight: 500; color: var(--muted); }
        .nav-menu a { transition: color 0.2s; }
        .nav-menu a:hover, .nav-menu a.active { color: var(--ink); font-weight: 600; }
        .nav-actions { display: flex; align-items: center; gap: 14px; }
        .btn-outline-login {
            display: inline-flex; align-items: center; gap: 8px;
            border: 1.5px solid var(--border); background: transparent; color: var(--ink);
            padding: 8px 18px; border-radius: 999px; font-size: 13.5px; font-weight: 600;
            transition: all 0.25s ease;
        }
        .btn-outline-login:hover { border-color: var(--ink); background: var(--ink); color: #fff; }

        /* MOBILE DRAWER NAV */
        .mobile-toggle-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 10px; background: transparent;
            border: 1.5px solid var(--border); color: var(--ink); font-size: 22px; cursor: pointer;
        }
        .mobile-nav-drawer {
            position: absolute; top: 76px; left: 0; right: 0; background: #ffffff;
            border-bottom: 1px solid var(--border); box-shadow: var(--shadow-lg);
            padding: 20px 24px; display: flex; flex-direction: column; gap: 12px;
            opacity: 0; pointer-events: none; transform: translateY(-10px);
            transition: all 0.25s cubic-bezier(0.16,1,0.3,1); z-index: 9999;
        }
        .mobile-nav-drawer.open { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .mobile-nav-drawer a {
            font-size: 15px; font-weight: 600; color: var(--ink); padding: 10px 0;
            border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;
        }
        .mobile-nav-drawer a:last-child { border-bottom: none; }

        /* HERO SECTION */
        .hero-section { position: relative; padding: 32px 0 48px; overflow: hidden; }
        @media (min-width: 768px) { .hero-section { padding: 48px 0 64px; } }
        .hero-section .wrap { position: relative; z-index: 2; }

        /* BACKGROUND PATHS ANIMATION */
        .bg-paths-wrapper {
            position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 1;
        }
        .bg-paths-svg {
            width: 100%; height: 100%;
        }
        .bg-path-line {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: bgPathDash 15s linear infinite;
        }
        @keyframes bgPathDash {
            0% { stroke-dashoffset: 1000; opacity: 0.2; }
            50% { opacity: 0.7; }
            100% { stroke-dashoffset: 0; opacity: 0.2; }
        }
        .floating-tag {
            display: inline-flex; align-items: center; gap: 10px;
            background: #ffffff; border: 1px solid var(--border);
            border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600;
            box-shadow: var(--shadow-sm); margin-bottom: 24px; position: relative;
        }
        .floating-tag .dot-stack { display: flex; margin-right: -4px; }
        .floating-tag .dot-stack span { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: -3px; }
        .hero-title {
            font-size: clamp(26px, 6vw, 56px); font-weight: 300; color: #94a3b8;
            line-height: 1.22; margin-bottom: 16px; max-width: 100%; padding-top: 4px;
        }
        @media (min-width: 768px) { .hero-title { max-width: 24ch; margin-bottom: 20px; } }
        .hero-title .highlight { color: var(--ink); font-weight: 700; }
        .hero-lead {
            font-size: 15px; color: var(--muted); max-width: 100%; margin-bottom: 28px; font-weight: 400; line-height: 1.65;
        }
        @media (min-width: 768px) { .hero-lead { font-size: 17px; max-width: 520px; margin-bottom: 36px; } }
        .feature-card {
            background: #ffffff; border: 1.5px solid var(--border); border-radius: 24px;
            padding: 36px 32px; box-shadow: var(--shadow-md); width: 100%; position: relative;
        }
        
        /* BUTTONS */
        .framed-btn {
            position: relative; display: inline-flex; align-items: center; gap: 10px;
            background: #ffffff; border: 1px solid #cbd5e1; color: var(--ink);
            padding: 14px 24px; border-radius: 6px; font-size: 14px; font-weight: 600;
            transition: all 0.2s ease; cursor: pointer;
        }
        .framed-btn .corner { position: absolute; width: 6px; height: 6px; border-color: #64748b; pointer-events: none; }
        .framed-btn .c-tl { top: -1px; left: -1px; border-top: 1.5px solid; border-left: 1.5px solid; }
        .framed-btn .c-tr { top: -1px; right: -1px; border-top: 1.5px solid; border-right: 1.5px solid; }
        .framed-btn .c-bl { bottom: -1px; left: -1px; border-bottom: 1.5px solid; border-left: 1.5px solid; }
        .framed-btn .c-br { bottom: -1px; right: -1px; border-bottom: 1.5px solid; border-right: 1.5px solid; }
        .framed-btn:hover { border-color: var(--ink); background: #f8fafc; }
        .framed-btn.full-width { width: 100%; justify-content: center; text-align: center; }

        .btn-solid-dark {
            background: var(--ink); color: #ffffff; padding: 14px 28px; border-radius: 6px;
            font-size: 14px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.15); display: inline-block; text-align: center;
        }
        .btn-solid-dark:hover { background: #1e293b; transform: translateY(-2px); }

        /* HERO STATS */
        .hero-stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
            padding-top: 24px; margin-top: 28px; border-top: 1px solid var(--border);
            width: 100%;
        }
        @media (min-width: 768px) { .hero-stats-grid { gap: 24px; padding-top: 36px; margin-top: 48px; } }
        @media (max-width: 480px) { .hero-stats-grid { grid-template-columns: 1fr; gap: 16px; } }
        .stat-item { display: flex; flex-direction: column; }
        .stat-item b { font-family: 'Space Grotesk', sans-serif; font-size: 26px; font-weight: 700; color: var(--ink); line-height: 1.1; }
        @media (min-width: 768px) { .stat-item b { font-size: 32px; } }
        .stat-item .label { font-size: 13px; font-weight: 700; color: var(--ink); margin-top: 6px; }
        .stat-item .sub { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.4; }

        /* METHODOLOGY DARK SECTION */
        .dark-section { background: #111111; color: #ffffff; padding: 64px 0; position: relative; z-index: 20; }
        @media (min-width: 768px) { .dark-section { padding: 100px 0; } }
        .dark-title { font-size: clamp(26px, 5vw, 52px); font-weight: 300; color: #94a3b8; line-height: 1.15; margin-bottom: 18px; }
        @media (min-width: 768px) { .dark-title { margin-bottom: 24px; } }
        .dark-title .highlight { color: #ffffff; font-weight: 700; }
        .dark-lead { color: #94a3b8; font-size: 15px; line-height: 1.7; margin-bottom: 28px; max-width: 100%; }
        @media (min-width: 768px) { .dark-lead { font-size: 16px; margin-bottom: 40px; max-width: 480px; } }
        .skill-tags { display: flex; flex-wrap: wrap; gap: 12px 18px; font-size: 13px; font-weight: 600; color: #cbd5e1; }
        @media (min-width: 768px) { .skill-tags { gap: 16px 24px; } }
        .skill-tag { padding-bottom: 6px; border-bottom: 1.5px solid #475569; transition: border-color 0.2s, color 0.2s; cursor: pointer; }
        .skill-tag:hover, .skill-tag.active { border-color: var(--teal); color: #ffffff; }

        /* FIGMA MOCKUP */
        .figma-mockup {
            background: #1e1e1e; border: 1px solid #334155; border-radius: 12px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.5); overflow: hidden; aspect-ratio: 16/10;
            display: flex; flex-direction: column; transition: transform 0.4s ease;
        }
        @media (min-width: 768px) { .figma-mockup { border-radius: 14px; box-shadow: 0 32px 64px rgba(0,0,0,0.5); } }
        .figma-mockup:hover { transform: scale(1.015); }
        .figma-topbar {
            height: 36px; background: #2c2c2c; border-bottom: 1px solid #383838;
            display: flex; align-items: center; justify-content: space-between; padding: 0 14px; font-size: 11px; color: #94a3b8;
        }
        #ideFilename { transition: opacity 0.25s ease; }
        .window-dots { display: flex; gap: 6px; }
        .w-dot { width: 10px; height: 10px; border-radius: 50%; }
        .figma-body { display: flex; flex: 1; overflow: hidden; }
        .figma-sidebar-left { width: 44px; background: #2c2c2c; border-right: 1px solid #383838; display: flex; flex-direction: column; align-items: center; padding: 14px 0; gap: 16px; color: #64748b; font-size: 16px; }
        .figma-canvas { flex: 1; background: #1e1e1e; display: flex; align-items: center; justify-content: center; position: relative; padding: 20px; }
        .canvas-card {
            background: #ffffff; border-radius: 8px; width: 100%; max-width: 320px; padding: 20px;
            color: var(--ink); box-shadow: 0 12px 28px rgba(0,0,0,0.3); transform: scale(0.95); position: relative;
        }
        .canvas-card .selection-border { position: absolute; inset: -4px; border: 1.5px solid #3b82f6; pointer-events: none; border-radius: 10px; }
        .canvas-card .selection-border .s-handle { position: absolute; width: 7px; height: 7px; background: #ffffff; border: 1.5px solid #3b82f6; }

        /* SECTION HEADERS */
        .sec-head { text-align: center; max-width: 600px; margin: 0 auto 36px; padding: 0 4px; }
        @media (min-width: 768px) { .sec-head { margin: 0 auto 52px; } }
        .sec-head h2 { font-size: clamp(24px, 5vw, 36px); font-weight: 800; margin-bottom: 10px; }
        .sec-head p { color: var(--muted); font-size: 15px; margin: 0; }
        @media (min-width: 768px) { .sec-head p { font-size: 16px; } }

        /* SERVICES CARDS */
        .services-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        @media (min-width: 640px) { .services-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; } }
        @media (min-width: 960px) { .services-grid { grid-template-columns: repeat(3, 1fr); gap: 28px; } }
        .service-card {
            background: #ffffff; border: 1.5px solid var(--border); border-radius: 20px;
            padding: 28px 24px; box-shadow: var(--shadow-sm); transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            position: relative; overflow: hidden; display: flex; flex-direction: column;
        }
        @media (min-width: 768px) { .service-card { padding: 36px 30px; border-radius: 24px; } }
        .service-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); border-color: rgba(47,184,174,0.3); }
        .service-card .icon-box {
            width: 50px; height: 50px; border-radius: 14px; background: rgba(47,184,174,0.12);
            color: var(--teal); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 18px;
        }
        @media (min-width: 768px) { .service-card .icon-box { width: 56px; height: 56px; font-size: 28px; margin-bottom: 24px; } }
        .service-card h3 { font-size: 19px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.02em; }
        @media (min-width: 768px) { .service-card h3 { font-size: 22px; } }
        .service-card p { color: var(--muted); font-size: 14px; line-height: 1.65; margin-bottom: 20px; flex: 1; }
        .service-features { list-style: none; padding: 0; margin: 0 0 24px; font-size: 13px; color: var(--muted); display: grid; gap: 10px; }
        .service-features li { display: flex; align-items: center; gap: 8px; }

        /* PORTFOLIO GRID & TABS */
        .p-filter-tabs { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 40px; }
        .pf-tab {
            background: #ffffff; border: 1px solid var(--border); border-radius: 999px;
            padding: 9px 22px; font-size: 13.5px; font-weight: 600; color: var(--muted);
            cursor: pointer; transition: all 0.2s ease; box-shadow: var(--shadow-sm);
        }
        .pf-tab:hover { border-color: var(--ink); color: var(--ink); }
        .pf-tab.active { background: var(--ink); color: #ffffff; border-color: var(--ink); }
        .portfolio-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        @media (max-width: 900px) { .portfolio-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 580px) { .portfolio-grid { grid-template-columns: 1fr; } }
        .p-card {
            background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius);
            overflow: hidden; cursor: pointer; transition: all 0.35s cubic-bezier(0.16,1,0.3,1); box-shadow: var(--shadow-sm);
        }
        .p-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-md); }
        .p-card.hidden { display: none !important; }
        .p-thumb-box { aspect-ratio: 4/3; position: relative; background: #0f172a; overflow: hidden; }
        .p-thumb-box img, .p-thumb-box video { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.16,1,0.3,1); }
        .p-card:hover .p-thumb-box img { transform: scale(1.08); }
        .p-cat-badge {
            position: absolute; bottom: 12px; left: 14px; font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 600; text-transform: uppercase; background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px); color: #ffffff; padding: 4px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.15);
        }
        .p-card-body { padding: 20px 22px 24px; }
        .p-card-body h4 { font-size: 17px; font-weight: 700; margin-bottom: 6px; }
        .p-card-body p { color: var(--muted); font-size: 13.5px; margin: 0; line-height: 1.6; }

        /* PRICING SECTION */
        .pricing-card {
            background: #ffffff; border: 1.5px solid var(--border); border-radius: 20px;
            padding: 28px 24px; box-shadow: var(--shadow-md); transition: all 0.3s ease; display: flex; flex-direction: column;
        }
        @media (min-width: 768px) { .pricing-card { padding: 40px; border-radius: 24px; } }
        .pricing-card.featured { border-color: var(--teal); box-shadow: 0 20px 50px rgba(47,184,174,0.15); position: relative; }
        .pricing-card.featured::before {
            content: "Paling Populer"; position: absolute; top: -14px; right: 20px;
            background: linear-gradient(135deg, var(--teal), var(--teal-dark)); color: #fff;
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
            padding: 4px 14px; border-radius: 999px;
        }
        .pricing-card h3 { font-size: 20px; font-weight: 800; margin-bottom: 6px; }
        @media (min-width: 768px) { .pricing-card h3 { font-size: 24px; } }
        .pricing-card .price { font-family: 'Space Grotesk', sans-serif; font-size: 28px; font-weight: 800; color: var(--ink); margin: 14px 0; }
        @media (min-width: 768px) { .pricing-card .price { font-size: 34px; margin: 18px 0; } }
        .pricing-card ul { list-style: none; padding: 0; margin: 0 0 24px; font-size: 13.5px; color: var(--muted); display: grid; gap: 10px; flex: 1; }
        .pricing-card li { display: flex; align-items: flex-start; gap: 10px; }
        .pricing-card li i { color: var(--teal); font-size: 18px; flex: none; margin-top: 2px; }

        /* TESTIMONIALS & CONTACT */
        .reviews-grid { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 36px; }
        @media (min-width: 640px) { .reviews-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; } }
        @media (min-width: 960px) { .reviews-grid { grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 48px; } }
        .review-card { background: #ffffff; border: 1px solid var(--border); border-radius: 16px; padding: 22px; box-shadow: var(--shadow-sm); }
        @media (min-width: 768px) { .review-card { padding: 28px; } }
        .review-stars { color: #f59e0b; margin-bottom: 14px; font-size: 16px; }
        .review-quote { font-size: 14px; color: var(--ink); line-height: 1.65; margin-bottom: 20px; font-style: italic; }
        .review-author { font-weight: 700; font-size: 14px; }
        .review-role { font-size: 12px; color: var(--muted); }
        
        .contact-info-card {
            display: flex; align-items: center; gap: 16px; background: #ffffff;
            border: 1.5px solid var(--border); padding: 20px 24px; border-radius: 18px;
            box-shadow: var(--shadow-sm); transition: all 0.2s; margin-bottom: 16px;
        }
        .contact-icon-box {
            width: 50px; height: 50px; border-radius: 14px; background: rgba(47,184,174,0.12);
            color: var(--teal); display: flex; align-items: center; justify-content: center; font-size: 24px; flex: none;
        }

        /* INPUT FORMS */
        .form-input {
            width: 100%; padding: 14px 18px; border: 1.5px solid var(--border);
            border-radius: 12px; font-size: 14.5px; outline: none; transition: border-color 0.2s; background: #f8fafc;
        }
        .form-input:focus { border-color: var(--teal); }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--ink); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }

        /* ===== PORTFOLIO MODAL — REDESIGNED ===== */
        .p-modal-overlay {
            position: fixed; inset: 0; z-index: 10000;
            background: rgba(4, 10, 25, 0.75);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            display: flex; align-items: center; justify-content: center;
            padding: 12px;
            opacity: 0; pointer-events: none;
            transition: opacity 0.35s cubic-bezier(0.16,1,0.3,1);
        }
        @media (min-width: 768px) { .p-modal-overlay { padding: 20px; } }
        .p-modal-overlay.active { opacity: 1; pointer-events: auto; }

        /* Card layout: 2-column on desktop */
        .p-modal-card {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%; max-width: 1000px; max-height: 95vh;
            background: #0d1117;
            border-radius: 18px;
            overflow: hidden; overflow-y: auto;
            box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06);
            transform: scale(0.93) translateY(30px);
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
            position: relative; color: #fff;
        }
        @media (min-width: 720px) {
            .p-modal-card { grid-template-columns: 1fr 360px; max-height: 88vh; overflow: hidden; border-radius: 28px; }
        }
        .p-modal-overlay.active .p-modal-card { transform: scale(1) translateY(0); }

        /* LEFT — media panel */
        .p-modal-left {
            position: relative; background: #080e1a;
            display: flex; flex-direction: column;
            min-height: 240px;
        }
        @media (min-width: 720px) { .p-modal-left { min-height: 420px; } }
        .p-modal-media-wrap {
            flex: 1; position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .p-modal-media-wrap img, .p-modal-media-wrap video {
            width: 100%; height: 100%; object-fit: cover; display: block;
            transition: transform 0.5s ease;
        }
        .p-modal-media-wrap img:hover { transform: scale(1.03); }
        /* Bottom gradient on image */
        .p-modal-media-wrap::after {
            content: ''; position: absolute; inset: auto 0 0;
            height: 40%; pointer-events: none;
            background: linear-gradient(to top, rgba(8,14,26,0.9) 0%, transparent 100%);
        }

        /* Thumbnail strip */
        .p-modal-gallery-thumbs {
            display: flex; gap: 8px; padding: 14px 16px 14px;
            background: rgba(255,255,255,0.03);
            border-top: 1px solid rgba(255,255,255,0.07);
            overflow-x: auto; flex-shrink: 0;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent;
        }
        .pm-thumb {
            width: 52px; height: 52px; border-radius: 10px; object-fit: cover;
            cursor: pointer; border: 2px solid transparent; opacity: 0.5;
            transition: all 0.2s; flex: none;
        }
        .pm-thumb:hover { opacity: 0.85; transform: scale(1.06); }
        .pm-thumb.active { opacity: 1; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(47,184,174,0.25); }

        /* RIGHT — info panel */
        .p-modal-right {
            display: flex; flex-direction: column;
            background: #ffffff; color: var(--ink);
            overflow-y: auto;
            scrollbar-width: thin;
        }

        /* Close button */
        .p-modal-close {
            position: absolute; top: 16px; right: 16px;
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(255,255,255,0.12); color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; cursor: pointer; z-index: 20;
            transition: all 0.2s; backdrop-filter: blur(10px);
        }
        .p-modal-close:hover { background: rgba(255,255,255,0.25); transform: rotate(90deg) scale(1.1); }

        /* Carousel Navigation */
        .p-modal-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 44px; height: 44px; border-radius: 50%;
            background: rgba(255,255,255,0.12); color: #fff;
            border: 1px solid rgba(255,255,255,0.18);
            display: none; align-items: center; justify-content: center;
            font-size: 24px; cursor: pointer; z-index: 20;
            transition: all 0.2s; backdrop-filter: blur(10px);
        }
        .p-modal-nav:hover { background: rgba(255,255,255,0.3); transform: translateY(-50%) scale(1.1); }
        .p-modal-prev { left: 16px; }
        .p-modal-next { right: 16px; }

        /* Info body */
        .p-modal-body {
            padding: 24px 20px 20px;
            flex: 1; display: flex; flex-direction: column;
        }
        @media (min-width: 720px) { .p-modal-body { padding: 36px 32px 28px; } }
        .p-modal-body-top { flex: 1; }
        .p-modal-cat {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--teal); background: rgba(47,184,174,0.1);
            border: 1px solid rgba(47,184,174,0.25);
            padding: 5px 14px; border-radius: 999px; margin-bottom: 18px;
        }
        .p-modal-cat::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--teal); flex-shrink: 0; }
        .p-modal-body h3 {
            font-size: 26px; font-weight: 800; line-height: 1.25;
            margin-bottom: 14px; letter-spacing: -0.02em; color: var(--ink);
        }
        .p-modal-body p {
            font-size: 14.5px; color: var(--muted); line-height: 1.75;
            margin-bottom: 0;
        }

        /* divider */
        .p-modal-divider {
            height: 1px; background: rgba(15,23,42,0.07);
            margin: 24px 0;
        }

        /* Stat chips */
        .p-modal-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 10px; margin-bottom: 0;
        }
        .p-modal-stat {
            background: #f8fafc; border: 1px solid rgba(15,23,42,0.07);
            border-radius: 14px; padding: 12px 14px;
        }
        .p-modal-stat-label { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
        .p-modal-stat-val { font-size: 14px; font-weight: 700; color: var(--ink); }

        /* Footer */
        .p-modal-footer {
            padding: 16px 20px 22px;
            border-top: 1px solid rgba(15,23,42,0.07);
        }
        @media (min-width: 720px) { .p-modal-footer { padding: 20px 32px 28px; } }
        .p-modal-footer .btn-solid-dark {
            width: 100%; text-align: center; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            border-radius: 14px; padding: 14px 20px; font-size: 14px;
        }

        /* CHATBOT WIDGET */
        .chat-widget-trigger {
            position: fixed; bottom: 20px; right: 16px; z-index: 9999;
            width: 52px; height: 52px; border-radius: 50%; background: var(--ink); color: #fff;
            border: none; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.3); display: flex;
            align-items: center; justify-content: center; font-size: 22px; cursor: pointer;
            transition: all 0.3s ease;
        }
        @media (min-width: 640px) { .chat-widget-trigger { bottom: 28px; right: 28px; width: 60px; height: 60px; font-size: 26px; } }
        .chat-widget-trigger:hover { transform: scale(1.08); background: #1e293b; }
        .chat-window {
            position: fixed; bottom: 82px; right: 12px; z-index: 9999; width: calc(100vw - 24px); max-width: 360px;
            background: #ffffff; border-radius: 18px; border: 1px solid var(--border); box-shadow: var(--shadow-lg);
            display: flex; flex-direction: column; overflow: hidden; opacity: 0; pointer-events: none;
            transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        @media (min-width: 640px) { .chat-window { bottom: 100px; right: 28px; width: 380px; max-width: calc(100vw - 32px); } }
        .chat-window.open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
        .chat-header { background: var(--ink); color: #fff; padding: 16px 18px; display: flex; align-items: center; justify-content: space-between; }
        @media (min-width: 640px) { .chat-header { padding: 18px 20px; } }
        .chat-messages { height: 280px; overflow-y: auto; padding: 16px; background: #f8fafc; display: flex; flex-direction: column; gap: 10px; }
        @media (min-width: 640px) { .chat-messages { height: 320px; padding: 20px; gap: 12px; } }
        .chat-msg { max-width: 88%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55; }
        .chat-msg.bot { background: #ffffff; border: 1px solid var(--border); color: var(--ink); align-self: flex-start; }
        .chat-msg.user { background: var(--teal); color: #ffffff; align-self: flex-end; }
        .chat-input-area { padding: 12px; background: #ffffff; border-top: 1px solid var(--border); display: flex; gap: 8px; }
        .chat-input-area input { flex: 1; border: 1px solid var(--border); border-radius: 10px; padding: 9px 12px; font-size: 13px; outline: none; }
        .chat-input-area button { background: var(--ink); color: #fff; border: none; padding: 10px 14px; border-radius: 10px; cursor: pointer; }

        /* MOBILE SECTION PADDING OVERRIDES */
        @media (max-width: 767px) {
            .py-24 { padding-top: 56px !important; padding-bottom: 56px !important; }
            .pt-10 { padding-top: 24px !important; }
            .gap-12 { gap: 32px !important; }
            .gap-16 { gap: 32px !important; }
            .p-10 { padding: 24px 20px !important; }
            .p-8 { padding: 20px 16px !important; }
            .rounded-3xl { border-radius: 20px !important; }
        }
        @media (max-width: 767px) {
            section.py-24.bg-white, section.py-24.bg-slate-50, section.py-24 {
                padding-top: 52px;
                padding-bottom: 52px;
            }
        }
    </style>
</head>
<body>

<!-- SITE HEADER -->
<header class="site-header">
    <div class="wrap relative">
        <div class="header-inner">
            <a href="#" class="brand-logo">
                <img src="assets/images/logo.jpg" alt="Aff Digital">
                <span>aff digital<span class="dot">.</span></span>
            </a>
            <nav class="nav-menu hidden md:flex">
                <a href="#tentang">Tentang</a>
                <a href="#layanan">Layanan</a>
                <a href="#portofolio">Portofolio</a>
                <a href="#harga">Harga</a>
                <a href="#testimoni">Testimoni</a>
                <a href="#kontak">Kontak</a>
            </nav>
            <div class="nav-actions">
                <a href="admin/login.php" class="btn-outline-login hidden sm:inline-flex">
                    <i class="ph ph-user-key text-base"></i> Login Admin
                </a>
                <button class="mobile-toggle-btn md:hidden" id="mobileNavToggle" aria-label="Buka Menu">
                    <i class="ph ph-list"></i>
                </button>
            </div>
        </div>
        
        <!-- MOBILE NAV DRAWER -->
        <div class="mobile-nav-drawer md:hidden" id="mobileNavDrawer">
            <a href="#tentang" class="m-link"><span>Tentang</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="#layanan" class="m-link"><span>Layanan</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="#portofolio" class="m-link"><span>Portofolio</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="#harga" class="m-link"><span>Harga Paket</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="#testimoni" class="m-link"><span>Testimoni</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="#kontak" class="m-link"><span>Hubungi Kami</span> <i class="ph ph-caret-right text-gray-400"></i></a>
            <a href="admin/login.php" class="m-link text-teal-600"><span>🔑 Login Admin</span> <i class="ph ph-caret-right text-gray-400"></i></a>
        </div>
    </div>
</header>

<main>

    <!-- HERO SECTION -->
    <section class="hero-section bg-grid-pattern">
        <div class="bg-paths-wrapper" id="bgPathsContainer"></div>
        <div class="wrap">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Left Content -->
                <div class="lg:col-span-7">
                    <div class="floating-tag">
                        <div class="dot-stack">
                            <span class="bg-red-500"></span>
                            <span class="bg-purple-500"></span>
                            <span class="bg-blue-500"></span>
                            <span class="bg-green-500"></span>
                        </div>
                        <span>Aff Digital Studio <span class="text-slate-400 font-normal">/ WEBSITE &amp; VISUAL AGENCY</span></span>
                    </div>

                    <h1 class="hero-title">
                        Turning <span class="highlight">complex ideas</span> into <span class="highlight">intuitive experiences.</span>
                    </h1>

                    <p class="hero-lead">
                        Kami membantu UMKM &amp; Brand mengubah ide bisnis menjadi website berkinerja tinggi, sistem custom yang efisien, serta konten foto &amp; video promosi profesional.
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#kontak" class="framed-btn">
                            <div class="corner c-tl"></div><div class="corner c-tr"></div>
                            <div class="corner c-bl"></div><div class="corner c-br"></div>
                            <i class="ph ph-chat-circle-dots text-lg"></i>
                            <span>Konsultasi Sekarang</span>
                        </a>
                        <a href="#portofolio" class="btn-solid-dark">Lihat Portofolio</a>
                    </div>
                </div>

                <!-- Right Content Visual (Person Portrait Photo) -->
                <div class="lg:col-span-5 flex justify-center items-end relative mt-6 lg:mt-0">
                    <div class="relative w-full max-w-[320px] sm:max-w-[380px] lg:max-w-[420px] aspect-[4/5] flex items-end justify-center">
                        <!-- Soft Gradient Backdrop Podium -->
                        <div class="absolute inset-0 bg-gradient-to-b from-slate-100 to-teal-500/10 rounded-t-full border border-slate-200/60 -z-10 shadow-sm"></div>
                        
                        <!-- Person Portrait Image -->
                        <img 
                            src="assets/images/orang.png" 
                            alt="Aff Digital Lead Specialist" 
                            class="w-full h-auto object-contain max-h-[400px] sm:max-h-[500px] drop-shadow-xl relative z-10"
                            loading="eager"
                        />

                        <!-- Floating Accent Badge -->
                        <div class="absolute -bottom-2 -left-1 sm:-bottom-3 sm:-left-2 bg-white/95 backdrop-blur-md border border-slate-200 px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl shadow-lg flex items-center gap-2 sm:gap-3 z-20">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center font-bold text-base sm:text-lg">
                                <i class="ph ph-sparkle"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800">Expert Developers</div>
                                <div class="text-[10px] sm:text-[11px] text-slate-500">Ready for your project</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Hero Stats -->
            <div class="border-t border-slate-200 mt-10 sm:mt-14 pt-6 sm:pt-10">
                <div class="grid grid-cols-3 sm:grid-cols-3 gap-4 sm:gap-8">
                    <div class="stat-item">
                        <b>50+</b>
                        <div class="label">Proyek Selesai</div>
                        <div class="sub hidden sm:block">Website, Toko Online &amp; Sistem Custom</div>
                    </div>
                    <div class="stat-item sm:border-l border-slate-200 sm:pl-6">
                        <b>99%</b>
                        <div class="label">Klien Puas</div>
                        <div class="sub hidden sm:block">Hasil Kualitas Terbaik &amp; Fast Response</div>
                    </div>
                    <div class="stat-item sm:border-l border-slate-200 sm:pl-6">
                        <b>3+</b>
                        <div class="label">Tahun Kerja</div>
                        <div class="sub hidden sm:block">Pengembangan Ekosistem Digital</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- METHODOLOGY DARK SECTION -->
    <section class="dark-section" id="tentang">
        <div class="wrap">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-10 lg:gap-16">
                <!-- Left Text Content -->
                <div class="w-full lg:w-1/2">
                    <h2 class="dark-title">
                        Developing with<br>
                        <span class="highlight">Clarity, Performance and Intention.</span>
                    </h2>
                    <p class="dark-lead">
                        Kami percaya bahwa website &amp; sistem bukan hanya tentang estetika visual, tetapi juga tentang kecepatan akses, alur kerja yang efisien, dan dampak konversi yang nyata bagi bisnis Anda.
                    </p>

                    <!-- Tambahkan atribut data-tech di sini -->
                    <div class="skill-tags" id="techTags">
                        <div class="skill-tag active" data-tech="php">PHP &amp; PDO</div>
                        <div class="skill-tag" data-tech="mysql">MySQL Database</div>
                        <div class="skill-tag" data-tech="tailwind">Tailwind &amp; CSS3</div>
                        <div class="skill-tag" data-tech="uiux">UI/UX Design</div>
                        <div class="skill-tag" data-tech="midtrans">Midtrans Gateway</div>
                        <div class="skill-tag" data-tech="ipaymu">iPaymu Gateway</div>
                    </div>
                </div>

                <!-- Right Content / Figma IDE Mockup -->
                <div class="w-full lg:w-1/2">
                    <div class="figma-mockup">
                        <div class="figma-topbar">
                            <div class="window-dots">
                                <div class="w-dot bg-red-500"></div>
                                <div class="w-dot bg-amber-500"></div>
                                <div class="w-dot bg-green-500"></div>
                            </div>
                            <div id="ideFilename">aff-digital-architecture.config</div>
                            <div class="flex gap-2">
                                <i class="ph ph-play"></i>
                                <i class="ph ph-share-network"></i>
                            </div>
                        </div>
                        <div class="figma-body">
                            <div class="figma-sidebar-left">
                                <i class="ph ph-cursor text-white"></i>
                                <i class="ph ph-selection text-gray-500"></i>
                                <i class="ph ph-code text-gray-500"></i>
                                <i class="ph ph-brackets-curly text-gray-500"></i>
                            </div>
                            <div class="figma-canvas">
                                <!-- Tambahkan ID dan transisi pada canvas-card -->
                                <div class="canvas-card transition-all duration-300 ease-in-out" id="ideCard">
                                    <div class="text-xs text-teal-500 font-bold mb-1" id="ideSubtitle">SYSTEM ENGINE</div>
                                    <div class="text-base font-extrabold mb-2" id="ideTitle">Aff Digital Core</div>
                                    <div class="text-xs text-slate-500 leading-relaxed" id="ideDesc">Optimized database query + instant payment notification callbacks.</div>
                                    <div class="selection-border">
                                        <div class="s-handle -top-1 -left-1"></div>
                                        <div class="s-handle -top-1 -right-1"></div>
                                        <div class="s-handle -bottom-1 -left-1"></div>
                                        <div class="s-handle -bottom-1 -right-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES SECTION -->
    <section class="py-24 bg-white" id="layanan">
        <div class="wrap">
            <div class="sec-head">
                <div class="text-xs font-bold tracking-widest text-teal-500 mb-2 uppercase">SOLUSI &amp; LAYANAN UNGGULAN</div>
                <h2>Layanan Berstandar Tinggi</h2>
                <p>Setiap solusi dirancang presisi dengan arsitektur modern untuk mendorong pertumbuhan bisnis Anda.</p>
            </div>

            <div class="services-grid">
                <!-- Service Card 1 -->
                <div class="service-card">
                    <div class="icon-box"><i class="ph ph-globe font-bold"></i></div>
                    <h3>Pembuatan Website</h3>
                    <p>Company Profile, Toko Online (E-Commerce), dan Landing Page berorientasi konversi yang cepat &amp; responsive.</p>
                    <ul class="service-features">
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Desain Premium &amp; Responsive HP/PC</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Optimasi SEO &amp; Performa Cepat</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Integrasi WhatsApp &amp; Payment Gateway</span></li>
                    </ul>
                    <a href="#kontak" class="framed-btn full-width">
                        <div class="corner c-tl"></div><div class="corner c-tr"></div>
                        <div class="corner c-bl"></div><div class="corner c-br"></div>
                        <span>Konsultasi Website &rarr;</span>
                    </a>
                </div>

                <!-- Service Card 2 -->
                <div class="service-card">
                    <div class="icon-box"><i class="ph ph-cpu font-bold"></i></div>
                    <h3>Sistem Web Custom</h3>
                    <p>Sistem Kasir (POS), Manajemen Absensi &amp; HR, Inventori Gudang, hingga portal internal sesuai kebutuhan.</p>
                    <ul class="service-features">
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Dashboard Rekap &amp; Analytics Laporan</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Akses Multi-Role &amp; Otorisasi User</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Keamanan Database &amp; Data Backup</span></li>
                    </ul>
                    <a href="#kontak" class="framed-btn full-width">
                        <div class="corner c-tl"></div><div class="corner c-tr"></div>
                        <div class="corner c-bl"></div><div class="corner c-br"></div>
                        <span>Konsultasi Sistem Custom &rarr;</span>
                    </a>
                </div>

                <!-- Service Card 3 -->
                <div class="service-card">
                    <div class="icon-box"><i class="ph ph-camera font-bold"></i></div>
                    <h3>Foto &amp; Video Promosi</h3>
                    <p>Konten visual produk studio &amp; video promosi brand profesional untuk meningkatkan kepercayaan calon pembeli.</p>
                    <ul class="service-features">
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Peralatan Studio Lighting Lengkap</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Editing Profesional &amp; Color Grading</span></li>
                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span>Format Konten Siap Upload Sosmed</span></li>
                    </ul>
                    <a href="#kontak" class="framed-btn full-width">
                        <div class="corner c-tl"></div><div class="corner c-tr"></div>
                        <div class="corner c-bl"></div><div class="corner c-br"></div>
                        <span>Konsultasi Foto &amp; Video &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- PORTFOLIO SECTION -->
    <section class="py-24 bg-slate-50" id="portofolio">
        <div class="wrap">
            <div class="sec-head">
                <h2>Katalog &amp; Portofolio Proyek</h2>
                <p>Lihat contoh jenis website, sistem custom, serta karya foto &amp; video profesional dari Aff Digital.</p>
            </div>

            <!-- Category Tabs -->
            <div class="p-filter-tabs">
                <button class="pf-tab active" data-filter="all">Semua</button>
                <button class="pf-tab" data-filter="Website">Website</button>
                <button class="pf-tab" data-filter="Sistem">Sistem</button>
                <button class="pf-tab" data-filter="Foto Produk">Foto Produk</button>
                <button class="pf-tab" data-filter="Video Promosi">Video Promosi</button>
                <button class="pf-tab" data-filter="Desain Grafis">Desain Grafis</button>
                <button class="pf-tab" data-filter="Lainnya">Lainnya</button>
            </div>

            <!-- Portfolio Grid -->
            <div class="portfolio-grid">
                <?php
                if (empty($portfoliosList)) {
                    $portfoliosList = [
                        ['title' => 'Company Profile UMKM', 'category_label' => 'Website', 'description' => 'Landing page satu halaman, fokus konversi.', 'media_type' => 'image', 'media_url' => 'assets/images/company_profile.jpg'],
                        ['title' => 'Toko Online', 'category_label' => 'Website', 'description' => 'Katalog produk lengkap dengan halaman detail.', 'media_type' => 'image', 'media_url' => 'assets/images/toko_online.jpg'],
                        ['title' => 'Sistem Absensi HR', 'category_label' => 'Sistem', 'description' => 'Pencatatan kehadiran karyawan berbasis web.', 'media_type' => 'image', 'media_url' => 'assets/images/sistem_absensi.jpg'],
                        ['title' => 'Sistem Retail / Kasir (POS)', 'category_label' => 'Sistem', 'description' => 'Transaksi, stok, dan laporan penjualan.', 'media_type' => 'image', 'media_url' => 'assets/images/sistem_pos.jpg'],
                        ['title' => 'Sistem Manajemen Gudang', 'category_label' => 'Sistem', 'description' => 'Stok masuk-keluar, lokasi rak, dan laporan gudang.', 'media_type' => 'image', 'media_url' => 'assets/images/sistem_gudang.jpg'],
                        ['title' => 'Foto Produk', 'category_label' => 'Foto Produk', 'description' => 'Set foto katalog untuk marketplace.', 'media_type' => 'image', 'media_url' => 'assets/images/foto_produk.jpg'],
                        ['title' => 'Video Promosi', 'category_label' => 'Video Promosi', 'description' => 'Video pendek untuk media sosial.', 'media_type' => 'image', 'media_url' => 'assets/images/video_promosi.jpg'],
                    ];
                }
                foreach ($portfoliosList as $p):
                    $dataJson = json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    $catLabel = $p['category_label'] ?? 'Lainnya';
                ?>
                <div class="p-card" data-category="<?php echo h($catLabel); ?>" data-portfolio="<?php echo h($dataJson); ?>">
                    <div class="p-thumb-box">
                        <?php if (($p['media_type'] ?? 'image') === 'video'): ?>
                            <video src="<?php echo h($p['media_url']); ?>" autoplay loop muted playsinline></video>
                        <?php else: ?>
                            <img src="<?php echo h($p['media_url']); ?>" alt="<?php echo h($p['title']); ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="p-cat-badge"><?php echo h($catLabel); ?></span>
                    </div>
                    <div class="p-card-body">
                        <h4><?php echo h($p['title']); ?></h4>
                        <p><?php echo h($p['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- PRICING SECTION -->
    <?php if (!empty($packagesByCategory)): ?>
    <section class="py-24" id="harga">
        <div class="wrap">
            <div class="sec-head">
                <h2>Paket Layanan &amp; Transparan</h2>
                <p>Pilih paket investasi digital yang sesuai dengan kebutuhan dan skala bisnis Anda.</p>
            </div>

            <?php foreach ($packagesByCategory as $catKey => $pkgList): ?>
                <div class="mb-12">
                    <h3 class="text-xl font-bold mb-5 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>
                        <?php echo $catKey === 'website' ? 'Paket Pembuatan Website' : 'Paket Foto &amp; Video Promosi'; ?>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                        <?php foreach ($pkgList as $pkg):
                            $isFeatured = strtolower($pkg['name']) === 'pro';
                            $features = array_filter(array_map('trim', explode("\n", (string)$pkg['features'])));
                        ?>
                            <div class="pricing-card <?php echo $isFeatured ? 'featured' : ''; ?>">
                                <div class="text-xs font-bold tracking-widest text-teal-500 uppercase mb-1"><?php echo h($pkg['name']); ?></div>
                                <h3><?php echo h($pkg['name']); ?></h3>
                                <div class="text-sm text-slate-500"><?php echo h($pkg['tagline']); ?></div>
                                <div class="price"><?php echo format_rupiah($pkg['price']); ?></div>
                                <ul>
                                    <?php foreach ($features as $f): ?>
                                        <li><i class="ph ph-check-circle text-teal-500 text-lg"></i> <span><?php echo h($f); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="checkout.php?package=<?php echo $pkg['id']; ?>" class="btn-solid-dark w-full">Pesan Sekarang &rarr;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- REVIEWS SECTION -->
    <section class="py-24 bg-slate-50" id="testimoni">
        <div class="wrap">
            <div class="sec-head">
                <h2>Kata Pelanggan</h2>
                <p>Ulasan jujur dari klien yang mempercayakan kebutuhan website dan konten visual mereka kepada Aff Digital.</p>
            </div>

            <div class="reviews-grid">
                <?php if (!empty($comments)): ?>
                    <?php foreach (array_slice($comments, 0, 6) as $c): ?>
                        <div class="review-card">
                            <div class="review-stars">★★★★★</div>
                            <p class="review-quote">"<?php echo h($c['comment'] ?? $c['text'] ?? ''); ?>"</p>
                            <div class="review-author"><?php echo h($c['name'] ?? 'Klien'); ?></div>
                            <div class="review-role"><?php echo h($c['date'] ?? date('d M Y')); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="review-card"><div class="review-stars">★★★★★</div><p class="review-quote">"Website toko online kami selesai tepat waktu dan sangat cepat dibuka di HP."</p><div class="review-author">Budi Santoso</div><div class="review-role">Owner UMKM</div></div>
                    <div class="review-card"><div class="review-stars">★★★★★</div><p class="review-quote">"Sistem kasir POS dari Aff Digital sangat membantu rekap laporan harian toko kami."</p><div class="review-author">Dewi Lestari</div><div class="review-role">Manager Retail</div></div>
                    <div class="review-card"><div class="review-stars">★★★★★</div><p class="review-quote">"Hasil foto produk studio dan videonya bikin omset jualan di marketplace naik pesat."</p><div class="review-author">Rizky Pratama</div><div class="review-role">Brand Fashion</div></div>
                <?php endif; ?>
            </div>

            <!-- Form Ulasan -->
            <div class="bg-white border border-slate-200 rounded-3xl p-8 max-w-xl mx-auto shadow-sm">
                <h4 class="text-lg font-bold mb-2 text-center">Tulis Ulasan Anda</h4>
                <p class="text-sm text-slate-500 text-center mb-5">Bagikan pengalaman Anda menggunakan jasa Aff Digital.</p>
                <form method="POST" action="process_comment.php">
                    <input type="hidden" name="redirect_to" value="index.php#testimoni">
                    <div class="mb-4">
                        <input type="text" name="name" required placeholder="Nama Anda / Nama Usaha" class="form-input">
                    </div>
                    <div class="mb-4">
                        <textarea name="comment" required placeholder="Tuliskan komentar atau ulasan Anda..." class="form-input min-h-[80px]"></textarea>
                    </div>
                    <button type="submit" class="btn-solid-dark w-full">Kirim Ulasan</button>
                </form>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="py-24 bg-slate-50" id="kontak">
        <div class="wrap">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                <!-- Info Kolom Kiri -->
                <div class="lg:col-span-5">
                    <div class="text-xs font-bold tracking-widest text-teal-500 mb-3 uppercase">HUBUNGI KAMI</div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold mb-4 leading-tight tracking-tight">Mari Konsultasikan Proyek Digital Anda</h2>
                    <p class="text-slate-500 text-sm sm:text-base leading-relaxed mb-8 sm:mb-10">Tim ahli kami siap membantu memilihkan solusi terbaik sesuai anggaran dan tujuan bisnis Anda.</p>
                    
                    <div class="grid gap-4">
                        <a href="https://wa.me/6289612339608" target="_blank" rel="noopener" class="contact-info-card hover:border-teal-400 hover:shadow-md transition-all duration-200">
                            <div class="contact-icon-box"><i class="ph ph-whatsapp-logo"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">WhatsApp Fast Response</div>
                                <div class="font-extrabold text-base text-slate-900 mt-0.5">+62 896-1233-9608</div>
                            </div>
                        </a>

                        <a href="mailto:owener@affdigital.my.id" class="contact-info-card hover:border-teal-400 hover:shadow-md transition-all duration-200">
                            <div class="contact-icon-box"><i class="ph ph-envelope"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Email Konsultasi</div>
                                <div class="font-extrabold text-base text-slate-900 mt-0.5">owener@affdigital.my.id</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Form Kolom Kanan -->
                <div class="lg:col-span-7">
                    <div class="bg-white border-2 border-slate-100 rounded-2xl sm:rounded-3xl p-6 sm:p-8 lg:p-10 shadow-md">
                        <h3 class="text-xl sm:text-2xl font-extrabold mb-2">Formulir Pesan &amp; Diskusi</h3>
                        <p class="text-sm text-slate-500 mb-7">Isi formulir di bawah dan pesan Anda langsung dikirim ke WhatsApp kami. <span class="text-teal-600 font-semibold">Respon cepat!</span></p>
                        <form id="contactWaForm" onsubmit="return handleWaContactForm(event)">
                            <div class="grid gap-5">
                                <div>
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" id="waName" required placeholder="Contoh: Budi Santoso" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Email / Nomor WhatsApp Anda</label>
                                    <input type="text" name="contact" id="waContact" required placeholder="081234567890 atau email@domain.com" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Jenis Layanan yang Dibutuhkan</label>
                                    <select name="service" id="waService" class="form-input">
                                        <option value="Website / Landing Page">Website / Landing Page</option>
                                        <option value="Toko Online (E-Commerce)">Toko Online (E-Commerce)</option>
                                        <option value="Sistem POS / HR Custom">Sistem POS / HR Custom</option>
                                        <option value="Foto &amp; Video Promosi">Foto &amp; Video Promosi</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Pesan / Detail Kebutuhan Proyek</label>
                                    <textarea name="message" id="waMessage" required placeholder="Jelaskan kebutuhan, target, atau anggaran proyek Anda..." class="form-input min-h-[120px] resize-y"></textarea>
                                </div>
                                <button type="submit" class="btn-solid-dark w-full py-4 text-base rounded-xl mt-2 flex items-center justify-center gap-2">
                                    <i class="ph ph-whatsapp-logo text-xl"></i>
                                    <span>Kirim ke WhatsApp Sekarang &rarr;</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<!-- FOOTER -->
<footer class="bg-slate-900 text-slate-400 py-12 sm:py-16 text-sm">
    <div class="wrap">
        <div class="flex flex-col sm:flex-row flex-wrap justify-between gap-8 pb-8 sm:pb-10 border-b border-white/10">
            <div class="max-w-xs">
                <div class="flex items-center gap-2.5 mb-3.5">
                    <img src="assets/images/logo.jpg" alt="Aff Digital" class="h-9 w-auto rounded-lg object-cover">
                    <span class="font-['Space_Grotesk'] text-[22px] font-extrabold text-white">aff digital.</span>
                </div>
                <p class="leading-relaxed">Agency Jasa Pembuatan Website, Sistem Custom, dan Foto/Video Promosi Profesional Indonesia.</p>
            </div>
            <div>
                <div class="text-white font-bold mb-3.5">Legality &amp; Policy</div>
                <div class="grid gap-2">
                    <a href="syarat-ketentuan.php" class="hover:text-white transition-colors">Syarat &amp; Ketentuan</a>
                    <a href="refund-policy.php" class="hover:text-white transition-colors">Refund Policy</a>
                    <a href="faq.php" class="hover:text-white transition-colors">Pertanyaan Umum (FAQ)</a>
                </div>
            </div>
        </div>
        <div class="text-center pt-6 sm:pt-7 text-[13px]">&copy; <?php echo date('Y'); ?> Aff Digital. All rights reserved.</div>
    </div>
</footer>

<!-- PORTFOLIO MODAL — REDESIGNED -->
<div class="p-modal-overlay" id="portfolioModal" aria-hidden="true">
    <div class="p-modal-card">

        <!-- LEFT: Media Panel -->
        <div class="p-modal-left">
            <button class="p-modal-close" id="modalCloseBtn" aria-label="Tutup Detail">&times;</button>
            <button class="p-modal-nav p-modal-prev" id="modalPrevBtn" aria-label="Previous">&#10094;</button>
            <button class="p-modal-nav p-modal-next" id="modalNextBtn" aria-label="Next">&#10095;</button>
            <div class="p-modal-media-wrap" id="modalMediaContainer"></div>
            <div class="p-modal-gallery-thumbs" id="modalGalleryContainer" style="display:none;"></div>
        </div>

        <!-- RIGHT: Info Panel -->
        <div class="p-modal-right">
            <div class="p-modal-body">
                <div class="p-modal-body-top">
                    <span class="p-modal-cat" id="modalCategory">Kategori</span>
                    <h3 id="modalTitle">Judul Proyek</h3>
                    <p id="modalDescription">Deskripsi proyek...</p>
                    <div class="p-modal-divider"></div>
                    <div class="p-modal-stats">
                        <div class="p-modal-stat">
                            <div class="p-modal-stat-label">Status</div>
                            <div class="p-modal-stat-val">Selesai</div>
                        </div>
                        <div class="p-modal-stat">
                            <div class="p-modal-stat-label">Kategori</div>
                            <div class="p-modal-stat-val" id="modalCatStat">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-modal-footer">
                <a href="#kontak" onclick="closePortfolioModal()" class="btn-solid-dark">
                    <i class="ph ph-chat-circle-dots"></i>
                    <span>Konsultasikan Proyek Serupa</span>
                </a>
            </div>
        </div>

    </div>
</div>

<!-- CHATBOT WIDGET -->
<button class="chat-widget-trigger" id="chatTrigger" title="Tanya Aff Assistant">
    <i class="ph ph-chat-circle-dots"></i>
</button>
<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center font-extrabold text-xs text-white">AFF</div>
            <div>
                <div class="font-bold text-sm">Aff Assistant</div>
                <div class="text-[11px] text-white/70">Online</div>
            </div>
        </div>
        <button id="chatClose" class="bg-transparent border-none text-white text-2xl cursor-pointer">&times;</button>
    </div>
    <div class="chat-messages" id="chatMsgs">
        <div class="chat-msg bot">Halo! 👋 Ada yang bisa kami bantu mengenai pembuatan website, sistem custom, atau harga paket?</div>
    </div>
    <form class="chat-input-area" id="chatForm">
        <input type="text" id="chatInput" placeholder="Ketik pertanyaan..." autocomplete="off">
        <button type="submit"><i class="ph ph-paper-plane-right"></i></button>
    </form>
</div>

<script>
// === WHATSAPP CONTACT FORM HANDLER ===
function handleWaContactForm(e) {
    e.preventDefault();

    const name    = document.getElementById('waName').value.trim();
    const contact = document.getElementById('waContact').value.trim();
    const service = document.getElementById('waService').value;
    const message = document.getElementById('waMessage').value.trim();

    if (!name || !contact || !message) return false;

    const template =
`Halo Aff Digital!

Saya ingin berkonsultasi mengenai kebutuhan proyek digital:

*Nama Lengkap:* ${name}
*Kontak (WA/Email):* ${contact}
*Layanan yang Diminati:* ${service}
*Detail Kebutuhan:*
${message}

Mohon informasi lebih lanjut dan estimasi penawaran. Terima kasih!`;

    window.open('https://wa.me/6289612339608?text=' + encodeURIComponent(template), '_blank');
    return false;
}

document.addEventListener('DOMContentLoaded', () => {
    // CHATBOT LOGIC
    const chatTrigger = document.getElementById('chatTrigger');
    const chatWindow = document.getElementById('chatWindow');
    const chatClose = document.getElementById('chatClose');
    const chatMsgs = document.getElementById('chatMsgs');
    const chatInput = document.getElementById('chatInput');
    const chatForm = document.getElementById('chatForm');

    chatTrigger.addEventListener('click', () => chatWindow.classList.toggle('open'));
    chatClose.addEventListener('click', () => chatWindow.classList.remove('open'));

    function appendChatMsg(text, sender = 'bot') {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${sender}`;
        msgDiv.innerHTML = text;
        chatMsgs.appendChild(msgDiv);
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    chatForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;
        appendChatMsg(text, 'user');
        chatInput.value = '';
        setTimeout(() => {
            appendChatMsg('Terima kasih! Tim Aff Digital akan segera membantu Anda. Anda juga bisa langsung berkonsultasi via WhatsApp.');
        }, 600);
    });

    // PORTFOLIO FILTER & MODAL
    const filterTabs = document.querySelectorAll('.pf-tab');
    const portfolioCards = document.querySelectorAll('.p-card');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const filter = tab.getAttribute('data-filter');
            portfolioCards.forEach(card => {
                const cat = card.getAttribute('data-category');
                if (filter === 'all' || cat === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });

    const modal = document.getElementById('portfolioModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalMediaContainer = document.getElementById('modalMediaContainer');
    const modalGalleryContainer = document.getElementById('modalGalleryContainer');
    const modalCategory = document.getElementById('modalCategory');
    const modalCatStat = document.getElementById('modalCatStat');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');

    let currentCarouselIndex = 0;
    
    function openPortfolioModal(data) {
        if (!data) return;
        const catLabel = data.category_label || 'Portofolio';
        modalCategory.textContent = catLabel;
        if (modalCatStat) modalCatStat.textContent = catLabel;
        modalTitle.textContent = data.title || 'Judul Proyek';
        modalDescription.textContent = data.description || '';

        let images = [];
        if (data.images_json) {
            try {
                const parsed = JSON.parse(data.images_json);
                if (Array.isArray(parsed) && parsed.length > 0) images = parsed;
            } catch (e) {}
        }
        if (images.length === 0 && data.media_url) images = [data.media_url];

        currentCarouselIndex = 0;

        function setMainMedia(url, idx) {
            currentCarouselIndex = idx !== undefined ? idx : 0;
            if (data.media_type === 'video' && url === data.media_url) {
                modalMediaContainer.innerHTML = `<video src="${url}" controls autoplay loop muted style="width:100%;height:100%;object-fit:contain;background:#0d1117;"></video>`;
            } else {
                modalMediaContainer.innerHTML = `<img src="${url}" alt="${data.title}" style="width:100%;height:100%;object-fit:contain;background:#0d1117;">`;
            }
            document.querySelectorAll('.pm-thumb').forEach((t, i) => {
                if (i === currentCarouselIndex) t.classList.add('active');
                else t.classList.remove('active');
            });
        }

        setMainMedia(images[0] || data.media_url, 0);

        const prevBtn = document.getElementById('modalPrevBtn');
        const nextBtn = document.getElementById('modalNextBtn');

        if (images.length > 1) {
            modalGalleryContainer.style.display = 'flex';
            modalGalleryContainer.innerHTML = '';
            images.forEach((imgUrl, idx) => {
                const thumb = document.createElement('img');
                thumb.src = imgUrl;
                thumb.className = `pm-thumb ${idx === 0 ? 'active' : ''}`;
                thumb.addEventListener('click', () => {
                    setMainMedia(imgUrl, idx);
                });
                modalGalleryContainer.appendChild(thumb);
            });
            
            if (prevBtn) {
                prevBtn.style.display = 'flex';
                prevBtn.onclick = () => {
                    let nextIdx = (currentCarouselIndex - 1 + images.length) % images.length;
                    setMainMedia(images[nextIdx], nextIdx);
                };
            }
            if (nextBtn) {
                nextBtn.style.display = 'flex';
                nextBtn.onclick = () => {
                    let nextIdx = (currentCarouselIndex + 1) % images.length;
                    setMainMedia(images[nextIdx], nextIdx);
                };
            }
        } else {
            modalGalleryContainer.style.display = 'none';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
        }

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    window.closePortfolioModal = function() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        modalMediaContainer.innerHTML = '';
    };

    portfolioCards.forEach(card => {
        card.addEventListener('click', () => {
            const json = card.getAttribute('data-portfolio');
            if (json) {
                try { openPortfolioModal(JSON.parse(json)); } catch(e) {}
            }
        });
    });

    // MOBILE NAV
    const mobileNavToggle = document.getElementById('mobileNavToggle');
    const mobileNavDrawer = document.getElementById('mobileNavDrawer');
    if (mobileNavToggle && mobileNavDrawer) {
        mobileNavToggle.addEventListener('click', () => {
            mobileNavDrawer.classList.toggle('open');
        });
        document.querySelectorAll('.m-link').forEach(link => {
            link.addEventListener('click', () => mobileNavDrawer.classList.remove('open'));
        });
    }

    // === GIMICK IDE/FIGMA MOCKUP INTERAKTIF ===
    const techData = {
        'php': {
            filename: 'backend-engine.php',
            subtitle: 'SYSTEM ENGINE',
            title: 'PHP 8.x + PDO Wrapper',
            desc: 'Secure, object-oriented database interactions with prepared statements to prevent SQL injection & optimize speed.'
        },
        'mysql': {
            filename: 'database-schema.sql',
            subtitle: 'DATA STORAGE',
            title: 'MySQL Relational DB',
            desc: 'Optimized indexing, normalized tables, and fast query execution for handling high-traffic system requests.'
        },
        'tailwind': {
            filename: 'tailwind.config.js',
            subtitle: 'FRONTEND STYLING',
            title: 'Utility-First CSS',
            desc: 'Rapid UI development with Tailwind CSS, ensuring responsive design and consistent atomic classes.'
        },
        'uiux': {
            filename: 'wireframe-prototype.fig',
            subtitle: 'USER EXPERIENCE',
            title: 'UI/UX Pixel Perfect',
            desc: 'User-centric designs focusing on accessibility, high conversion rates, and seamless user flow.'
        },
        'midtrans': {
            filename: 'payment-midtrans.json',
            subtitle: 'PAYMENT INTEGRATION',
            title: 'Midtrans Core API',
            desc: 'Automated invoice creation, instant payment notification (IPN) callbacks, and secure transaction handling.'
        },
        'ipaymu': {
            filename: 'payment-ipaymu.json',
            subtitle: 'PAYMENT INTEGRATION',
            title: 'iPaymu Seamless API',
            desc: 'Direct bank transfers, QRIS, and e-wallets integrated natively without redirecting the user out of the app.'
        }
    };

    const skillTags = document.querySelectorAll('.skill-tag');
    const ideCard = document.getElementById('ideCard');
    const ideFilename = document.getElementById('ideFilename');
    const ideSubtitle = document.getElementById('ideSubtitle');
    const ideTitle = document.getElementById('ideTitle');
    const ideDesc = document.getElementById('ideDesc');

    if (skillTags.length > 0 && ideCard) {
        skillTags.forEach(tag => {
            tag.addEventListener('click', function() {
                // Hapus class active dari semua tag
                skillTags.forEach(t => t.classList.remove('active'));
                // Tambahkan class active ke tag yang diklik
                this.classList.add('active');

                const techKey = this.getAttribute('data-tech');
                const data = techData[techKey];

                if (data) {
                    // Animasi Fade Out & Scale Down
                    ideCard.style.opacity = '0';
                    ideCard.style.transform = 'scale(0.9)';
                    if (ideFilename) ideFilename.style.opacity = '0';

                    setTimeout(() => {
                        // Update Konten
                        if (ideFilename) ideFilename.textContent = data.filename;
                        if (ideSubtitle) ideSubtitle.textContent = data.subtitle;
                        if (ideTitle) ideTitle.textContent = data.title;
                        if (ideDesc) ideDesc.textContent = data.desc;

                        // Animasi Fade In & Scale Up
                        ideCard.style.opacity = '1';
                        ideCard.style.transform = 'scale(0.95)';
                        if (ideFilename) ideFilename.style.opacity = '1';
                    }, 250);
                }
            });
        });
    }

    // BACKGROUND PATHS ANIMATION
    function initBackgroundPaths() {
        const container = document.getElementById('bgPathsContainer');
        if (!container) return;
        
        const createPaths = (position) => {
            let pathStrings = [];
            for (let i = 0; i < 36; i++) {
                const d = `M-${380 - i * 5 * position} -${189 + i * 6}C-${
                    380 - i * 5 * position
                } -${189 + i * 6} -${312 - i * 5 * position} ${216 - i * 6} ${
                    152 - i * 5 * position
                } ${343 - i * 6}C${616 - i * 5 * position} ${470 - i * 6} ${
                    684 - i * 5 * position
                } ${875 - i * 6} ${684 - i * 5 * position} ${875 - i * 6}`;
                const width = 0.5 + i * 0.03;
                const opacity = 0.1 + i * 0.03;
                const animDuration = 12 + Math.random() * 10;
                const animDelay = Math.random() * 5;
                pathStrings.push(`
                    <path
                        d="${d}"
                        stroke="rgba(15,23,42,${opacity})"
                        stroke-width="${width}"
                        fill="none"
                        class="bg-path-line"
                        style="animation-duration: ${animDuration}s; animation-delay: -${animDelay}s;"
                    />
                `);
            }
            return pathStrings.join('');
        };

        const svgContent = `
            <svg class="bg-paths-svg" viewBox="0 0 696 316" fill="none" preserveAspectRatio="xMidYMid slice">
                <title>Background Paths</title>
                ${createPaths(1)}
                ${createPaths(-1)}
            </svg>
        `;
        container.innerHTML = svgContent;
    }
    initBackgroundPaths();

    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closePortfolioModal);
    if (modal) {
        modal.addEventListener('click', (e) => { if (e.target === modal) closePortfolioModal(); });
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) closePortfolioModal();
    });
});
</script>

</body>
</html>