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
    <script src="https://cdn.tailwindcss.com"></script>

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

        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

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
        .hero-section { position: relative; padding: 48px 0 64px; overflow: hidden; }
        .floating-tag {
            display: inline-flex; align-items: center; gap: 10px;
            background: #ffffff; border: 1px solid var(--border);
            border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600;
            box-shadow: var(--shadow-sm); margin-bottom: 24px; position: relative;
        }
        .floating-tag .dot-stack { display: flex; margin-right: -4px; }
        .floating-tag .dot-stack span { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: -3px; }
        .hero-title {
            font-size: clamp(32px, 4.2vw, 56px); font-weight: 300; color: #94a3b8;
            line-height: 1.22; margin-bottom: 20px; max-width: 24ch; padding-top: 4px;
        }
        .hero-title .highlight { color: var(--ink); font-weight: 700; }
        .hero-lead {
            font-size: 17px; color: var(--muted); max-width: 520px; margin-bottom: 36px; font-weight: 400; line-height: 1.65;
        }
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
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
            padding-top: 36px; margin-top: 48px; border-top: 1px solid var(--border);
            width: 100%;
        }
        @media (max-width: 640px) { .hero-stats-grid { grid-template-columns: 1fr; gap: 20px; } }
        .stat-item { display: flex; flex-direction: column; }
        .stat-item b { font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; color: var(--ink); line-height: 1.1; }
        .stat-item .label { font-size: 13px; font-weight: 700; color: var(--ink); margin-top: 6px; }
        .stat-item .sub { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.4; }

        /* METHODOLOGY DARK SECTION */
        .dark-section { background: #111111; color: #ffffff; padding: 100px 0; position: relative; z-index: 20; }
        .dark-title { font-size: clamp(32px, 4vw, 52px); font-weight: 300; color: #94a3b8; line-height: 1.15; margin-bottom: 24px; }
        .dark-title .highlight { color: #ffffff; font-weight: 700; }
        .dark-lead { color: #94a3b8; font-size: 16px; line-height: 1.7; margin-bottom: 40px; max-width: 480px; }
        .skill-tags { display: flex; flex-wrap: wrap; gap: 16px 24px; font-size: 13px; font-weight: 600; color: #cbd5e1; }
        .skill-tag { padding-bottom: 6px; border-bottom: 1.5px solid #475569; transition: border-color 0.2s, color 0.2s; cursor: pointer; }
        .skill-tag:hover, .skill-tag.active { border-color: var(--teal); color: #ffffff; }

        /* FIGMA MOCKUP */
        .figma-mockup {
            background: #1e1e1e; border: 1px solid #334155; border-radius: 14px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.5); overflow: hidden; aspect-ratio: 16/10;
            display: flex; flex-direction: column; transition: transform 0.4s ease;
        }
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
        .sec-head { text-align: center; max-width: 600px; margin: 0 auto 52px; }
        .sec-head h2 { font-size: 36px; font-weight: 800; margin-bottom: 12px; }
        .sec-head p { color: var(--muted); font-size: 16px; margin: 0; }

        /* SERVICES CARDS */
        .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        @media (max-width: 960px) { .services-grid { grid-template-columns: 1fr; } }
        .service-card {
            background: #ffffff; border: 1.5px solid var(--border); border-radius: 24px;
            padding: 36px 30px; box-shadow: var(--shadow-sm); transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
            position: relative; overflow: hidden; display: flex; flex-direction: column;
        }
        .service-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-md); border-color: rgba(47,184,174,0.3); }
        .service-card .icon-box {
            width: 56px; height: 56px; border-radius: 16px; background: rgba(47,184,174,0.12);
            color: var(--teal); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 24px;
        }
        .service-card h3 { font-size: 22px; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em; }
        .service-card p { color: var(--muted); font-size: 14.5px; line-height: 1.65; margin-bottom: 24px; flex: 1; }
        .service-features { list-style: none; padding: 0; margin: 0 0 32px; font-size: 13.5px; color: var(--muted); display: grid; gap: 10px; }
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
            background: #ffffff; border: 1.5px solid var(--border); border-radius: 24px;
            padding: 40px; box-shadow: var(--shadow-md); transition: all 0.3s ease; display: flex; flex-direction: column;
        }
        .pricing-card.featured { border-color: var(--teal); box-shadow: 0 20px 50px rgba(47,184,174,0.15); position: relative; }
        .pricing-card.featured::before {
            content: "Paling Populer"; position: absolute; top: -14px; right: 28px;
            background: linear-gradient(135deg, var(--teal), var(--teal-dark)); color: #fff;
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
            padding: 4px 14px; border-radius: 999px;
        }
        .pricing-card h3 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
        .pricing-card .price { font-family: 'Space Grotesk', sans-serif; font-size: 34px; font-weight: 800; color: var(--ink); margin: 18px 0; }
        .pricing-card ul { list-style: none; padding: 0; margin: 0 0 32px; font-size: 14px; color: var(--muted); display: grid; gap: 12px; flex: 1; }
        .pricing-card li { display: flex; align-items: flex-start; gap: 10px; }
        .pricing-card li i { color: var(--teal); font-size: 18px; flex: none; margin-top: 2px; }

        /* TESTIMONIALS & CONTACT */
        .reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 48px; }
        @media (max-width: 900px) { .reviews-grid { grid-template-columns: 1fr; } }
        .review-card { background: #ffffff; border: 1px solid var(--border); border-radius: 16px; padding: 28px; box-shadow: var(--shadow-sm); }
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

        /* MODAL */
        .p-modal-overlay {
            position: fixed; inset: 0; z-index: 10000; background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            display: flex; align-items: center; justify-content: center; padding: 24px;
            opacity: 0; pointer-events: none; transition: opacity 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .p-modal-overlay.active { opacity: 1; pointer-events: auto; }
        .p-modal-card {
            background: #ffffff; border-radius: 24px; width: 100%; max-width: 860px; max-height: 90vh;
            overflow-y: auto; box-shadow: 0 32px 64px rgba(0,0,0,0.4);
            transform: scale(0.92) translateY(24px); transition: transform 0.35s cubic-bezier(0.16,1,0.3,1);
            position: relative; color: var(--ink);
        }
        .p-modal-overlay.active .p-modal-card { transform: scale(1) translateY(0); }
        .p-modal-close {
            position: absolute; top: 18px; right: 18px; width: 40px; height: 40px; border-radius: 50%;
            background: rgba(15, 23, 42, 0.6); color: #fff; border: 1px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center; font-size: 22px; cursor: pointer;
            z-index: 10; transition: all 0.2s; backdrop-filter: blur(8px);
        }
        .p-modal-close:hover { background: var(--ink); transform: scale(1.08); }
        .p-modal-media-wrap { background: #0f172a; border-radius: 24px 24px 0 0; overflow: hidden; position: relative; aspect-ratio: 16/10; max-height: 460px; display: flex; align-items: center; justify-content: center; }
        .p-modal-media-wrap img, .p-modal-media-wrap video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .p-modal-gallery-thumbs { display: flex; gap: 10px; padding: 14px 20px; background: #161c2b; overflow-x: auto; }
        .pm-thumb { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: all 0.2s; flex: none; }
        .pm-thumb:hover, .pm-thumb.active { opacity: 1; border-color: var(--teal); transform: scale(1.05); }
        .p-modal-body { padding: 36px; }
        .p-modal-cat { display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--teal); background: rgba(47,184,174,0.1); padding: 4px 14px; border-radius: 999px; margin-bottom: 14px; }
        .p-modal-body h3 { font-size: 28px; font-weight: 800; margin-bottom: 14px; }
        .p-modal-body p { font-size: 15.5px; color: var(--muted); line-height: 1.7; margin-bottom: 28px; }

        /* CHATBOT WIDGET */
        .chat-widget-trigger {
            position: fixed; bottom: 28px; right: 28px; z-index: 9999;
            width: 60px; height: 60px; border-radius: 50%; background: var(--ink); color: #fff;
            border: none; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.3); display: flex;
            align-items: center; justify-content: center; font-size: 26px; cursor: pointer;
            transition: all 0.3s ease;
        }
        .chat-widget-trigger:hover { transform: scale(1.08); background: #1e293b; }
        .chat-window {
            position: fixed; bottom: 100px; right: 28px; z-index: 9999; width: 380px; max-width: calc(100vw - 32px);
            background: #ffffff; border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-lg);
            display: flex; flex-direction: column; overflow: hidden; opacity: 0; pointer-events: none;
            transform: translateY(20px) scale(0.95); transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .chat-window.open { opacity: 1; pointer-events: auto; transform: translateY(0) scale(1); }
        .chat-header { background: var(--ink); color: #fff; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; }
        .chat-messages { height: 320px; overflow-y: auto; padding: 20px; background: #f8fafc; display: flex; flex-direction: column; gap: 12px; }
        .chat-msg { max-width: 85%; padding: 12px 16px; border-radius: 14px; font-size: 13.5px; line-height: 1.55; }
        .chat-msg.bot { background: #ffffff; border: 1px solid var(--border); color: var(--ink); align-self: flex-start; }
        .chat-msg.user { background: var(--teal); color: #ffffff; align-self: flex-end; }
        .chat-input-area { padding: 14px; background: #ffffff; border-top: 1px solid var(--border); display: flex; gap: 8px; }
        .chat-input-area input { flex: 1; border: 1px solid var(--border); border-radius: 10px; padding: 10px 14px; font-size: 13.5px; outline: none; }
        .chat-input-area button { background: var(--ink); color: #fff; border: none; padding: 10px 16px; border-radius: 10px; cursor: pointer; }
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

                <!-- Right Content Visual -->
                <div class="lg:col-span-5 flex justify-center mt-10 lg:mt-0">
                    <div class="feature-card">
                        <div class="text-xs font-bold tracking-widest text-teal-500 mb-3 uppercase">Layanan Unggulan</div>
                        <h3 class="text-2xl font-extrabold mb-3 tracking-tight leading-snug">Web &amp; System Development</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">Solusi digital menyeluruh mulai dari Landing Page, Toko Online, hingga Sistem POS &amp; HR Custom.</p>
                        <div class="grid gap-3">
                            <div class="flex items-center gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                <i class="ph ph-check-circle text-teal-500 text-xl"></i>
                                <span class="text-sm font-semibold">Desain Modern &amp; Fast Loading</span>
                            </div>
                            <div class="flex items-center gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                <i class="ph ph-check-circle text-teal-500 text-xl"></i>
                                <span class="text-sm font-semibold">Integrasi Midtrans &amp; iPaymu</span>
                            </div>
                            <div class="flex items-center gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                <i class="ph ph-check-circle text-teal-500 text-xl"></i>
                                <span class="text-sm font-semibold">Dashboard Admin &amp; Laporan</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Hero Stats -->
            <div class="border-t border-slate-200 mt-14 pt-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="stat-item pr-4">
                        <b>50+</b>
                        <div class="label">Proyek Selesai</div>
                        <div class="sub">Website, Toko Online &amp; Sistem Custom</div>
                    </div>
                    <div class="stat-item md:border-l border-slate-200 md:pl-6 pr-4">
                        <b>99%</b>
                        <div class="label">Kepuasan Klien</div>
                        <div class="sub">Hasil Kualitas Terbaik &amp; Fast Response</div>
                    </div>
                    <div class="stat-item md:border-l border-slate-200 md:pl-6">
                        <b>3+</b>
                        <div class="label">Tahun Pengalaman</div>
                        <div class="sub">Pengembangan Ekosistem Digital</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- METHODOLOGY DARK SECTION -->
    <section class="dark-section" id="tentang">
        <div class="wrap">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
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
                    <h2 class="text-4xl font-extrabold mb-4 leading-tight tracking-tight">Mari Konsultasikan Proyek Digital Anda</h2>
                    <p class="text-slate-500 text-base leading-relaxed mb-10">Tim ahli kami siap membantu memilihkan solusi terbaik sesuai anggaran dan tujuan bisnis Anda.</p>
                    
                    <div class="grid gap-4">
                        <div class="contact-info-card">
                            <div class="contact-icon-box"><i class="ph ph-whatsapp-logo"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">WhatsApp Fast Response</div>
                                <div class="font-extrabold text-base text-slate-900 mt-0.5">+62 812-3456-7890</div>
                            </div>
                        </div>

                        <div class="contact-info-card">
                            <div class="contact-icon-box"><i class="ph ph-envelope"></i></div>
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Email Konsultasi</div>
                                <div class="font-extrabold text-base text-slate-900 mt-0.5">halo@affdigital.my.id</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Kolom Kanan -->
                <div class="lg:col-span-7">
                    <div class="bg-white border-2 border-slate-100 rounded-3xl p-10 shadow-md">
                        <h3 class="text-2xl font-extrabold mb-2">Formulir Pesan &amp; Diskusi</h3>
                        <p class="text-sm text-slate-500 mb-7">Isi formulir di bawah ini dan kami akan membalas pesan Anda dalam 1x24 jam.</p>
                        <form method="POST" action="process_message.php">
                            <div class="grid gap-5">
                                <div>
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Email / Nomor WhatsApp</label>
                                    <input type="text" name="contact" required placeholder="081234567890 atau email@domain.com" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Pesan / Detail Kebutuhan Proyek</label>
                                    <textarea name="message" required placeholder="Jelaskan jenis website atau sistem yang ingin Anda buat..." class="form-input min-h-[120px] resize-y"></textarea>
                                </div>
                                <button type="submit" class="btn-solid-dark w-full py-4 text-base rounded-xl mt-2">Kirim Pesan Konsultasi &rarr;</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<!-- FOOTER -->
<footer class="bg-slate-900 text-slate-400 py-16 text-sm">
    <div class="wrap">
        <div class="flex flex-wrap justify-between gap-8 pb-10 border-b border-white/10">
            <div>
                <div class="flex items-center gap-2.5 mb-3.5">
                    <img src="assets/images/logo.jpg" alt="Aff Digital" class="h-9 w-auto rounded-lg object-cover">
                    <span class="font-['Space_Grotesk'] text-[22px] font-extrabold text-white">aff digital.</span>
                </div>
                <p class="max-w-xs leading-relaxed">Agency Jasa Pembuatan Website, Sistem Custom, dan Foto/Video Promosi Profesional Indonesia.</p>
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
        <div class="text-center pt-7 text-[13px]">&copy; <?php echo date('Y'); ?> Aff Digital. All rights reserved.</div>
    </div>
</footer>

<!-- PORTFOLIO MODAL -->
<div class="p-modal-overlay" id="portfolioModal" aria-hidden="true">
    <div class="p-modal-card">
        <button class="p-modal-close" id="modalCloseBtn" aria-label="Tutup Detail">&times;</button>
        <div class="p-modal-media-wrap" id="modalMediaContainer"></div>
        <div class="p-modal-gallery-thumbs" id="modalGalleryContainer" style="display:none;"></div>
        <div class="p-modal-body">
            <span class="p-modal-cat" id="modalCategory">Kategori</span>
            <h3 id="modalTitle">Judul Proyek</h3>
            <p id="modalDescription">Deskripsi proyek...</p>
            <div class="p-modal-footer">
                <a href="#kontak" onclick="closePortfolioModal()" class="btn-solid-dark">Konsultasikan Proyek Serupa &rarr;</a>
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
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');

    function openPortfolioModal(data) {
        if (!data) return;
        modalCategory.textContent = data.category_label || 'Portofolio';
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

        function setMainMedia(url) {
            if (data.media_type === 'video' && url === data.media_url) {
                modalMediaContainer.innerHTML = `<video src="${url}" controls autoplay loop muted style="width:100%;height:100%;object-fit:contain;background:#0d1117;"></video>`;
            } else {
                modalMediaContainer.innerHTML = `<img src="${url}" alt="${data.title}" style="width:100%;height:100%;object-fit:contain;background:#0d1117;">`;
            }
        }

        setMainMedia(images[0] || data.media_url);

        if (images.length > 1) {
            modalGalleryContainer.style.display = 'flex';
            modalGalleryContainer.innerHTML = '';
            images.forEach((imgUrl, idx) => {
                const thumb = document.createElement('img');
                thumb.src = imgUrl;
                thumb.className = `pm-thumb ${idx === 0 ? 'active' : ''}`;
                thumb.addEventListener('click', () => {
                    document.querySelectorAll('.pm-thumb').forEach(t => t.classList.remove('active'));
                    thumb.classList.add('active');
                    setMainMedia(imgUrl);
                });
                modalGalleryContainer.appendChild(thumb);
            });
        } else {
            modalGalleryContainer.style.display = 'none';
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