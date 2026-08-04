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

<!-- Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

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
    color: var(--ink); tracking-tight: -0.04em; display: flex; align-items: center; gap: 2px;
  }
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

  /* HERO SECTION */
  .hero-section {
    position: relative; padding: 80px 0 100px; overflow: hidden;
  }
  .floating-tag {
    display: inline-flex; align-items: center; gap: 10px;
    background: #ffffff; border: 1px solid var(--border);
    border-radius: 999px; padding: 6px 16px; font-size: 13px; font-weight: 600;
    box-shadow: var(--shadow-sm); margin-bottom: 28px; position: relative;
  }
  .floating-tag .dot-stack { display: flex; margin-right: -4px; }
  .floating-tag .dot-stack span { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: -3px; }
  .hero-title {
    font-size: clamp(38px, 5.5vw, 68px); font-weight: 300; color: #94a3b8;
    line-height: 1.08; margin-bottom: 24px; max-width: 22ch;
  }
  .hero-title .highlight { color: var(--ink); font-weight: 700; }
  .hero-lead {
    font-size: 18px; color: var(--muted); max-width: 540px; margin-bottom: 40px; font-weight: 400; line-height: 1.7;
  }
  
  /* TECHNICAL FRAMED BUTTON */
  .framed-btn {
    position: relative; display: inline-flex; align-items: center; gap: 10px;
    background: #ffffff; border: 1px solid #cbd5e1; color: var(--ink);
    padding: 14px 24px; border-radius: 6px; font-size: 14px; font-weight: 600;
    transition: all 0.2s ease; cursor: pointer;
  }
  .framed-btn .corner {
    position: absolute; width: 6px; height: 6px; border-color: #64748b; pointer-events: none;
  }
  .framed-btn .c-tl { top: -1px; left: -1px; border-top: 1.5px solid; border-left: 1.5px solid; }
  .framed-btn .c-tr { top: -1px; right: -1px; border-top: 1.5px solid; border-right: 1.5px solid; }
  .framed-btn .c-bl { bottom: -1px; left: -1px; border-bottom: 1.5px solid; border-left: 1.5px solid; }
  .framed-btn .c-br { bottom: -1px; right: -1px; border-bottom: 1.5px solid; border-right: 1.5px solid; }
  .framed-btn:hover { border-color: var(--ink); background: #f8fafc; }

  .btn-solid-dark {
    background: var(--ink); color: #ffffff; padding: 14px 28px; border-radius: 6px;
    font-size: 14px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s ease;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.15);
  }
  .btn-solid-dark:hover { background: #1e293b; transform: translateY(-2px); }

  .hero-stats-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
    padding-top: 36px; margin-top: 48px; border-top: 1px solid var(--border);
    width: 100%;
  }
  @media (max-width: 640px) {
    .hero-stats-grid { grid-template-columns: 1fr; gap: 20px; }
  }
  .stat-item { display: flex; flex-direction: column; }
  .stat-item b { font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; color: var(--ink); line-height: 1.1; }
  .stat-item .label { font-size: 13px; font-weight: 700; color: var(--ink); margin-top: 6px; }
  .stat-item .sub { font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.4; }

  /* METHODOLOGY DARK SECTION */
  .dark-section {
    background: #111111; color: #ffffff; padding: 100px 0; position: relative; z-index: 20;
  }
  .dark-title {
    font-size: clamp(32px, 4vw, 52px); font-weight: 300; color: #94a3b8; line-height: 1.15; margin-bottom: 24px;
  }
  .dark-title .highlight { color: #ffffff; font-weight: 700; }
  .dark-lead { color: #94a3b8; font-size: 16px; line-height: 1.7; margin-bottom: 40px; max-width: 480px; }

  .skill-tags { display: flex; flex-wrap: wrap; gap: 16px 24px; font-size: 13px; font-weight: 600; color: #cbd5e1; }
  .skill-tag { padding-bottom: 6px; border-bottom: 1.5px solid #475569; transition: border-color 0.2s, color 0.2s; cursor: pointer; }
  .skill-tag:hover, .skill-tag.active { border-color: var(--teal); color: #ffffff; }

  /* FIGMA / IDE MOCKUP FRAME */
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
  .window-dots { display: flex; gap: 6px; }
  .w-dot { width: 10px; height: 10px; border-radius: 50%; }
  .figma-body { display: flex; flex: 1; overflow: hidden; }
  .figma-sidebar-left { width: 44px; background: #2c2c2c; border-right: 1px solid #383838; display: flex; flex-direction: column; align-items: center; padding: 14px 0; gap: 16px; color: #64748b; font-size: 16px; }
  .figma-canvas { flex: 1; background: #1e1e1e; display: flex; align-items: center; justify-content: center; position: relative; padding: 20px; }
  .canvas-card {
    background: #ffffff; border-radius: 8px; width: 100%; max-width: 320px; padding: 20px;
    color: var(--ink); box-shadow: 0 12px 28px rgba(0,0,0,0.3); transform: scale(0.95); position: relative;
  }
  .canvas-card .selection-border {
    position: absolute; inset: -4px; border: 1.5px solid #3b82f6; pointer-events: none; border-radius: 10px;
  }
  .canvas-card .selection-border .s-handle {
    position: absolute; width: 7px; height: 7px; background: #ffffff; border: 1.5px solid #3b82f6;
  }

  /* SECTION HEADERS */
  .sec-head { text-align: center; max-width: 600px; margin: 0 auto 52px; }
  .sec-head h2 { font-size: 36px; font-weight: 800; margin-bottom: 12px; }
  .sec-head p { color: var(--muted); font-size: 16px; margin: 0; }

  /* SERVICES CARDS */
  .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
  @media (max-width: 960px) { .services-grid { grid-template-columns: 1fr; } }
  .service-card {
    background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius);
    padding: 36px; box-shadow: var(--shadow-sm); transition: all 0.3s cubic-bezier(0.16,1,0.3,1);
    position: relative; overflow: hidden; display: flex; flex-direction: column;
  }
  .service-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-md); border-color: rgba(47,184,174,0.3); }
  .service-card .icon-box {
    width: 52px; height: 52px; border-radius: 14px; background: rgba(47,184,174,0.12);
    color: var(--teal); display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 24px;
  }
  .service-card h3 { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
  .service-card p { color: var(--muted); font-size: 14.5px; line-height: 1.65; margin-bottom: 24px; flex: 1; }
  .service-features { list-style: none; padding: 0; margin: 0 0 28px; font-size: 13.5px; color: var(--muted); display: grid; gap: 10px; }
  .service-features li { display: flex; align-items: center; gap: 8px; }
  .service-features li i { color: var(--teal); font-weight: 700; }

  /* PORTFOLIO GRID & FILTER TABS */
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
  .pricing-card.featured {
    border-color: var(--teal); box-shadow: 0 20px 50px rgba(47,184,174,0.15); position: relative;
  }
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

  /* TESTIMONIALS & REVIEWS */
  .reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 48px; }
  @media (max-width: 900px) { .reviews-grid { grid-template-columns: 1fr; } }
  .review-card { background: #ffffff; border: 1px solid var(--border); border-radius: 16px; padding: 28px; box-shadow: var(--shadow-sm); }
  .review-stars { color: #f59e0b; margin-bottom: 14px; font-size: 16px; }
  .review-quote { font-size: 14px; color: var(--ink); line-height: 1.65; margin-bottom: 20px; font-style: italic; }
  .review-author { font-weight: 700; font-size: 14px; }
  .review-role { font-size: 12px; color: var(--muted); }

  /* MODAL DETAIL POPUP */
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
  <div class="wrap">
    <div class="header-inner">
      <a href="#" class="brand-logo">aff digital<span class="dot">.</span></a>
      <nav class="nav-menu hidden md:flex">
        <a href="#tentang">Tentang</a>
        <a href="#layanan">Layanan</a>
        <a href="#portofolio">Portofolio</a>
        <a href="#harga">Harga</a>
        <a href="#testimoni">Testimoni</a>
        <a href="#kontak">Kontak</a>
      </nav>
      <div class="nav-actions">
        <a href="admin/login.php" class="btn-outline-login"><i class="ph ph-user-key text-base"></i> Login Admin</a>
      </div>
    </div>
  </div>
</header>

<main>

  <!-- HERO SECTION (LIGHT MODE + GRID PATTERN) -->
  <section class="hero-section bg-grid-pattern">
    <div class="wrap">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        
        <!-- Left Content (Col 7) -->
        <div class="lg:col-span-7">
          
          <!-- Floating Tag -->
          <div class="floating-tag">
            <div class="dot-stack">
              <span style="background:#ef4444"></span>
              <span style="background:#a855f7"></span>
              <span style="background:#3b82f6"></span>
              <span style="background:#22c55e"></span>
            </div>
            <span>Aff Digital Studio <span style="color:#94a3b8;font-weight:400">/ WEBSITE &amp; VISUAL AGENCY</span></span>
          </div>

          <!-- Main Headline -->
          <h1 class="hero-title">
            Turning <span class="highlight">complex ideas</span><br>
            into <span class="highlight">intuitive experiences.</span>
          </h1>

          <!-- Subheadline -->
          <p class="hero-lead">
            Kami membantu UMKM &amp; Brand mengubah ide bisnis menjadi website berkinerja tinggi, sistem custom yang efisien, serta konten foto &amp; video promosi profesional.
          </p>

          <!-- CTAs -->
          <div class="flex flex-wrap items-center gap-4">
            <a href="#kontak" class="framed-btn">
              <div class="corner c-tl"></div><div class="corner c-tr"></div>
              <div class="corner c-bl"></div><div class="corner c-br"></div>
              <i class="ph ph-chat-circle-dots text-lg"></i>
              <span>Konsultasi Sekarang</span>
            </a>
            <a href="#portofolio" class="btn-solid-dark">Lihat Portofolio</a>
          </div>

          <!-- Hero Stats -->
          <div class="hero-stats-grid">
            <div class="stat-item">
              <b>50+</b>
              <div class="label">Proyek Selesai</div>
              <div class="sub">Website &amp; Sistem Custom</div>
            </div>
            <div class="stat-item" style="border-left:1px solid var(--border);padding-left:20px">
              <b>99%</b>
              <div class="label">Kepuasan Klien</div>
              <div class="sub">Hasil Kualitas Terbaik</div>
            </div>
            <div class="stat-item" style="border-left:1px solid var(--border);padding-left:20px">
              <b>3+</b>
              <div class="label">Tahun Pengalaman</div>
              <div class="sub">Pengembangan Digital</div>
            </div>
          </div>

        </div>

        <!-- Right Content / Architectural Card (Col 5) -->
        <div class="lg:col-span-5 flex justify-center">
          <div style="background:#ffffff;border:1.5px solid var(--border);border-radius:28px;padding:44px 38px;box-shadow:var(--shadow-md);width:100%;position:relative">
            <div style="font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--teal);margin-bottom:14px">LAYANAN UNGGULAN</div>
            <h3 style="font-size:26px;font-weight:800;margin-bottom:16px;letter-spacing:-0.02em;line-height:1.2">Web &amp; System Development</h3>
            <p style="color:var(--muted);font-size:14.5px;line-height:1.7;margin-bottom:32px">Solusi digital menyeluruh mulai dari Landing Page, Toko Online, hingga Sistem POS &amp; HR Custom.</p>
            <div style="display:grid;gap:14px">
              <div style="display:flex;align-items:center;gap:14px;background:#f8fafc;padding:16px 20px;border-radius:14px;border:1px solid var(--border);transition:all 0.2s">
                <i class="ph ph-check-circle text-teal text-2xl"></i>
                <span style="font-size:14px;font-weight:600">Desain Modern &amp; Fast Loading</span>
              </div>
              <div style="display:flex;align-items:center;gap:14px;background:#f8fafc;padding:16px 20px;border-radius:14px;border:1px solid var(--border);transition:all 0.2s">
                <i class="ph ph-check-circle text-teal text-2xl"></i>
                <span style="font-size:14px;font-weight:600">Integrasi Midtrans &amp; iPaymu</span>
              </div>
              <div style="display:flex;align-items:center;gap:14px;background:#f8fafc;padding:16px 20px;border-radius:14px;border:1px solid var(--border);transition:all 0.2s">
                <i class="ph ph-check-circle text-teal text-2xl"></i>
                <span style="font-size:14px;font-weight:600">Dashboard Admin &amp; Laporan</span>
              </div>
            </div>
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

          <div class="skill-tags">
            <div class="skill-tag active">PHP &amp; PDO</div>
            <div class="skill-tag">MySQL Database</div>
            <div class="skill-tag">Tailwind &amp; CSS3</div>
            <div class="skill-tag">UI/UX Design</div>
            <div class="skill-tag">Midtrans Gateway</div>
            <div class="skill-tag">iPaymu Gateway</div>
          </div>
        </div>

        <!-- Right Content / Figma IDE Mockup -->
        <div class="w-full lg:w-1/2">
          <div class="figma-mockup">
            <div class="figma-topbar">
              <div class="window-dots">
                <div class="w-dot" style="background:#ef4444"></div>
                <div class="w-dot" style="background:#f59e0b"></div>
                <div class="w-dot" style="background:#22c55e"></div>
              </div>
              <div>aff-digital-architecture.config</div>
              <div style="display:flex;gap:10px">
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
                <div class="canvas-card">
                  <div style="font-size:11px;color:var(--teal);font-weight:700;margin-bottom:6px">SYSTEM ENGINE</div>
                  <div style="font-size:15px;font-weight:800;margin-bottom:8px">Aff Digital Core</div>
                  <div style="font-size:12px;color:var(--muted);line-height:1.5">Optimized database query + instant payment notification callbacks.</div>
                  <div class="selection-border">
                    <div class="s-handle" style="top:-4px;left:-4px"></div>
                    <div class="s-handle" style="top:-4px;right:-4px"></div>
                    <div class="s-handle" style="bottom:-4px;left:-4px"></div>
                    <div class="s-handle" style="bottom:-4px;right:-4px"></div>
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
  <section class="py-24" id="layanan">
    <div class="wrap">
      <div class="sec-head">
        <h2>Layanan &amp; Solusi Digital</h2>
        <p>Layanan profesional kami dirancang untuk mengakselerasi pertumbuhan bisnis Anda secara digital.</p>
      </div>

      <div class="services-grid">
        <div class="service-card">
          <div class="icon-box"><i class="ph ph-globe"></i></div>
          <h3>Pembuatan Website</h3>
          <p>Company Profile, Toko Online (E-Commerce), dan Landing Page berorientasi konversi yang cepat &amp; responsive di semua perangkat.</p>
          <ul class="service-features">
            <li><i class="ph ph-check text-teal"></i> Responsif HP &amp; Desktop</li>
            <li><i class="ph ph-check text-teal"></i> Optimasi SEO Dasar</li>
            <li><i class="ph ph-check text-teal"></i> Integrasi Form &amp; WhatsApp</li>
          </ul>
        </div>

        <div class="service-card">
          <div class="icon-box"><i class="ph ph-cpu"></i></div>
          <h3>Sistem Web Custom</h3>
          <p>Sistem Kasir (POS), Manajemen Absensi &amp; HR, Inventori Gudang, hingga portal khusus yang disesuaikan dengan alur bisnis Anda.</p>
          <ul class="service-features">
            <li><i class="ph ph-check text-teal"></i> Dashboard Laporan &amp; Analytics</li>
            <li><i class="ph ph-check text-teal"></i> Hak Akses Multi-User</li>
            <li><i class="ph ph-check text-teal"></i> Keamanan Database MySQL</li>
          </ul>
        </div>

        <div class="service-card">
          <div class="icon-box"><i class="ph ph-camera"></i></div>
          <h3>Foto &amp; Video Promosi</h3>
          <p>Konten visual produk studio &amp; video promosi brand profesional untuk meningkatkan kepercayaan calon pembeli di media sosial.</p>
          <ul class="service-features">
            <li><i class="ph ph-check text-teal"></i> Equipment Studio Lengkap</li>
            <li><i class="ph ph-check text-teal"></i> Editing &amp; Color Grading</li>
            <li><i class="ph ph-check text-teal"></i> Format Siap Unggah Sosmed</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- DYNAMIC PORTFOLIO SECTION & CATEGORY FILTER -->
  <section class="py-24 bg-slate-50" id="portofolio">
    <div class="wrap">
      <div class="sec-head">
        <h2>Katalog &amp; Portofolio Proyek</h2>
        <p>Lihat contoh jenis website, sistem custom, serta karya foto &amp; video profesional dari Aff Digital.</p>
      </div>

      <!-- CATEGORY FILTER TABS -->
      <div class="p-filter-tabs">
        <button class="pf-tab active" data-filter="all">Semua</button>
        <button class="pf-tab" data-filter="Website">Website</button>
        <button class="pf-tab" data-filter="Sistem">Sistem</button>
        <button class="pf-tab" data-filter="Foto Produk">Foto Produk</button>
        <button class="pf-tab" data-filter="Video Promosi">Video Promosi</button>
        <button class="pf-tab" data-filter="Desain Grafis">Desain Grafis</button>
        <button class="pf-tab" data-filter="Lainnya">Lainnya</button>
      </div>

      <!-- PORTFOLIO GRID -->
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
        <div style="margin-bottom:48px">
          <h3 style="font-size:20px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--teal);display:inline-block"></span>
            <?php echo $catKey === 'website' ? 'Paket Pembuatan Website' : 'Paket Foto &amp; Video Promosi'; ?>
          </h3>
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:28px">
            <?php foreach ($pkgList as $pkg):
              $isFeatured = strtolower($pkg['name']) === 'pro';
              $features = array_filter(array_map('trim', explode("\n", (string)$pkg['features'])));
            ?>
              <div class="pricing-card <?php echo $isFeatured ? 'featured' : ''; ?>">
                <div style="font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--teal);margin-bottom:4px"><?php echo h($pkg['name']); ?></div>
                <h3><?php echo h($pkg['name']); ?></h3>
                <div style="font-size:13.5px;color:var(--muted)"><?php echo h($pkg['tagline']); ?></div>
                <div class="price"><?php echo format_rupiah($pkg['price']); ?></div>
                <ul>
                  <?php foreach ($features as $f): ?>
                    <li><i class="ph ph-check-circle"></i> <span><?php echo h($f); ?></span></li>
                  <?php endforeach; ?>
                </ul>
                <a href="checkout.php?package=<?php echo $pkg['id']; ?>" class="btn-solid-dark" style="text-align:center;display:block">Pesan Sekarang &rarr;</a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- REVIEWS & TESTIMONIALS SECTION -->
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

      <!-- Add Review Form -->
      <div style="background:#ffffff;border:1px solid var(--border);border-radius:20px;padding:32px;max-width:540px;margin:0 auto;box-shadow:var(--shadow-sm)">
        <h4 style="font-size:18px;font-weight:700;margin-bottom:8px;text-align:center">Tulis Ulasan Anda</h4>
        <p style="font-size:13.5px;color:var(--muted);text-align:center;margin-bottom:20px">Bagikan pengalaman Anda menggunakan jasa Aff Digital.</p>
        <form method="POST" action="process_comment.php">
          <input type="hidden" name="redirect_to" value="index.php#testimoni">
          <div style="margin-bottom:14px">
            <input type="text" name="name" required placeholder="Nama Anda / Nama Usaha" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:14px;outline:none">
          </div>
          <div style="margin-bottom:14px">
            <textarea name="comment" required placeholder="Tuliskan komentar atau ulasan Anda..." style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:14px;min-height:80px;outline:none"></textarea>
          </div>
          <button type="submit" class="btn-solid-dark" style="width:100%">Kirim Ulasan</button>
        </form>
      </div>

    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="py-24 bg-slate-50" id="kontak">
    <div class="wrap">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left Content: Info Cards (Col 5) -->
        <div class="lg:col-span-5">
          <div style="font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--teal);margin-bottom:12px">HUBUNGI KAMI</div>
          <h2 style="font-size:38px;font-weight:800;margin-bottom:18px;line-height:1.15;letter-spacing:-0.03em">Mari Konsultasikan Proyek Digital Anda</h2>
          <p style="color:var(--muted);font-size:15.5px;line-height:1.7;margin-bottom:40px">Tim ahli kami siap membantu memilihkan solusi terbaik sesuai anggaran dan tujuan bisnis Anda.</p>
          
          <div style="display:grid;gap:16px">
            <div style="display:flex;align-items:center;gap:16px;background:#ffffff;border:1.5px solid var(--border);padding:20px 24px;border-radius:18px;box-shadow:var(--shadow-sm);transition:all 0.2s">
              <div style="width:50px;height:50px;border-radius:14px;background:rgba(47,184,174,0.12);color:var(--teal);display:flex;align-items:center;justify-content:center;font-size:24px;flex:none"><i class="ph ph-whatsapp-logo"></i></div>
              <div>
                <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em">WhatsApp Fast Response</div>
                <div style="font-weight:800;font-size:16px;color:var(--ink);margin-top:2px">+62 812-3456-7890</div>
              </div>
            </div>

            <div style="display:flex;align-items:center;gap:16px;background:#ffffff;border:1.5px solid var(--border);padding:20px 24px;border-radius:18px;box-shadow:var(--shadow-sm);transition:all 0.2s">
              <div style="width:50px;height:50px;border-radius:14px;background:rgba(47,184,174,0.12);color:var(--teal);display:flex;align-items:center;justify-content:center;font-size:24px;flex:none"><i class="ph ph-envelope"></i></div>
              <div>
                <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em">Email Konsultasi</div>
                <div style="font-weight:800;font-size:16px;color:var(--ink);margin-top:2px">halo@affdigital.my.id</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Content: Form Card (Col 7) -->
        <div class="lg:col-span-7">
          <div style="background:#ffffff;border:1.5px solid var(--border);border-radius:28px;padding:44px 40px;box-shadow:var(--shadow-md)">
            <h3 style="font-size:22px;font-weight:800;margin-bottom:8px">Formulir Pesan &amp; Diskusi</h3>
            <p style="font-size:14px;color:var(--muted);margin-bottom:28px">Isi formulir di bawah ini dan kami akan membalas pesan Anda dalam 1x24 jam.</p>
            <form method="POST" action="process_message.php">
              <div style="display:grid;gap:20px">
                <div>
                  <label style="display:block;font-size:12px;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Nama Lengkap</label>
                  <input type="text" name="name" required placeholder="Contoh: Budi Santoso" style="width:100%;padding:14px 18px;border:1.5px solid var(--border);border-radius:12px;font-size:14.5px;outline:none;transition:border-color 0.2s;background:#f8fafc">
                </div>
                <div>
                  <label style="display:block;font-size:12px;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Email / Nomor WhatsApp</label>
                  <input type="text" name="contact" required placeholder="081234567890 atau email@domain.com" style="width:100%;padding:14px 18px;border:1.5px solid var(--border);border-radius:12px;font-size:14.5px;outline:none;transition:border-color 0.2s;background:#f8fafc">
                </div>
                <div>
                  <label style="display:block;font-size:12px;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Pesan / Detail Kebutuhan Proyek</label>
                  <textarea name="message" required placeholder="Jelaskan jenis website atau sistem yang ingin Anda buat..." style="width:100%;padding:14px 18px;border:1.5px solid var(--border);border-radius:12px;font-size:14.5px;min-height:120px;outline:none;transition:border-color 0.2s;background:#f8fafc;resize:vertical"></textarea>
                </div>
                <button type="submit" class="btn-solid-dark" style="width:100%;padding:16px;font-size:15px;border-radius:12px;margin-top:6px">Kirim Pesan Konsultasi &rarr;</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<!-- FOOTER -->
<footer style="background:#0f172a;color:#94a3b8;padding:60px 0 40px;font-size:14px">
  <div class="wrap">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:32px;padding-bottom:40px;border-bottom:1px solid rgba(255,255,255,0.08)">
      <div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:800;color:#fff;margin-bottom:12px">aff digital.</div>
        <p style="max-width:320px;line-height:1.6">Agency Jasa Pembuatan Website, Sistem Custom, dan Foto/Video Promosi Profesional Indonesia.</p>
      </div>
      <div>
        <div style="color:#fff;font-weight:700;margin-bottom:14px">Legality &amp; Policy</div>
        <div style="display:grid;gap:8px">
          <a href="syarat-ketentuan.php" style="color:#94a3b8">Syarat &amp; Ketentuan</a>
          <a href="refund-policy.php" style="color:#94a3b8">Refund Policy</a>
          <a href="faq.php" style="color:#94a3b8">Pertanyaan Umum (FAQ)</a>
        </div>
      </div>
    </div>
    <div style="text-align:center;padding-top:28px;font-size:13px">&copy; <?php echo date('Y'); ?> Aff Digital. All rights reserved.</div>
  </div>
</footer>

<!-- PORTFOLIO DETAIL MODAL POPUP -->
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
<button class="chat-widget-trigger" id="chatTrigger" title="Tanya Aff Assistant"><i class="ph ph-chat-circle-dots"></i></button>
<div class="chat-window" id="chatWindow">
  <div class="chat-header">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:32px;height:32px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:#fff">AFF</div>
      <div><div style="font-weight:700;font-size:14px">Aff Assistant</div><div style="font-size:11px;color:rgba(255,255,255,0.7)">Online</div></div>
    </div>
    <button id="chatClose" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer">&times;</button>
  </div>
  <div class="chat-messages" id="chatMsgs">
    <div class="chat-msg bot">Halo! 👋 Ada yang bisa kami bantu mengenai pembuatan website, sistem custom, atau harga paket?</div>
  </div>
  <form class="chat-input-area" id="chatForm" onsubmit="return handleChatSubmit(event)">
    <input type="text" id="chatInput" placeholder="Ketik pertanyaan..." autocomplete="off">
    <button type="submit"><i class="ph ph-paper-plane-right"></i></button>
  </form>
</div>

<script>
  // Chatbot logic
  const chatTrigger = document.getElementById('chatTrigger');
  const chatWindow = document.getElementById('chatWindow');
  const chatClose = document.getElementById('chatClose');
  const chatMsgs = document.getElementById('chatMsgs');
  const chatInput = document.getElementById('chatInput');

  chatTrigger.addEventListener('click', () => chatWindow.classList.toggle('open'));
  chatClose.addEventListener('click', () => chatWindow.classList.remove('open'));

  function appendChatMsg(text, sender = 'bot') {
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${sender}`;
    msgDiv.innerHTML = text;
    chatMsgs.appendChild(msgDiv);
    chatMsgs.scrollTop = chatMsgs.scrollHeight;
  }

  function handleChatSubmit(e) {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return false;
    appendChatMsg(text, 'user');
    chatInput.value = '';
    setTimeout(() => {
      appendChatMsg('Terima kasih! Tim Aff Digital akan segera membantu Anda. Anda juga bisa langsung berkonsultasi via WhatsApp.');
    }, 600);
    return false;
  }

  // PORTFOLIO FILTER & MODAL POPUP
  document.addEventListener('DOMContentLoaded', () => {
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
