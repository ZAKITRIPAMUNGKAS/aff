<?php
// index.php — Aff Digital
// Membaca data komentar dari data/comments.json (disimpan sebagai file, bisa dilihat/diedit lewat File Manager cPanel)

require_once __DIR__ . '/db.php';

$packagesByCategory = [];
$portfoliosList = [];
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
$comments = array_reverse($comments); // terbaru di atas

$showOk    = isset($_GET['ok']) && $_GET['ok'] === '1';
$showErr   = isset($_GET['err']) && $_GET['err'] === '1';
$showOkMsg = isset($_GET['okmsg']) && $_GET['okmsg'] === '1';
$showErrMsg = isset($_GET['errmsg']) && $_GET['errmsg'] === '1';

function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
function stars_for($rating) {
    $rating = max(1, min(5, (int) $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aff Digital — Website, Foto & Video</title>
<meta name="description" content="Aff Digital: jasa pembuatan website custom (company profile, toko online, sistem absensi HR, sistem retail/POS) serta foto & video profesional.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --ink: #0f1623;
    --paper: #f0f2f7;
    --card: #ffffff;
    --amber: #e8a33d;
    --teal: #2fb8ae;
    --teal-dark: #1f8a82;
    --muted: #6b7485;
    --line: rgba(15,22,35,0.10);
    --line-soft: rgba(15,22,35,0.06);
    --radius: 16px;
    --maxw: 1160px;
    --shadow-sm: 0 2px 8px rgba(15,22,35,0.07), 0 1px 2px rgba(15,22,35,0.05);
    --shadow-md: 0 8px 28px rgba(15,22,35,0.10), 0 2px 8px rgba(15,22,35,0.06);
    --shadow-lg: 0 20px 56px rgba(15,22,35,0.13), 0 4px 16px rgba(15,22,35,0.08);
    --shadow-teal: 0 8px 32px rgba(47,184,174,0.22);
    --shadow-amber: 0 8px 32px rgba(232,163,61,0.22);
  }
  *{box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
    *{animation-duration:0.001ms !important; animation-iteration-count:1 !important; transition-duration:0.001ms !important;}
  }
  body{
    margin:0;
    background: var(--paper);
    background-image:
      radial-gradient(ellipse 80% 50% at 10% -10%, rgba(47,184,174,0.09) 0%, transparent 60%),
      radial-gradient(ellipse 60% 40% at 90% 100%, rgba(232,163,61,0.07) 0%, transparent 55%);
    background-attachment: fixed;
    color:var(--ink);
    font-family:'Inter', sans-serif; line-height:1.6; -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,h4{ font-family:'Space Grotesk', sans-serif; margin:0; letter-spacing:-0.02em; }
  .mono{font-family:'JetBrains Mono', monospace;}
  a{color:inherit;text-decoration:none;}
  img{max-width:100%; display:block;}
  .wrap{max-width:var(--maxw); margin:0 auto; padding:0 28px;}
  section{padding:104px 0;}
  @media (max-width:720px){ section{padding:72px 0;} }

  .aperture .blade{ transform-origin:110px 110px; transition: transform 1.1s cubic-bezier(.16,1,.3,1); transform: rotate(0deg) scale(1.42); }
  .aperture.open .blade{ transform: rotate(38deg) scale(1); }

  .divider{ display:flex; align-items:center; justify-content:center; gap:14px; margin: 0 0 52px 0; color:var(--muted); }
  .divider svg{width:22px; height:22px; flex:none;}
  .divider .lbl{ font-family:'JetBrains Mono', monospace; font-size:12px; letter-spacing:0.14em; text-transform:uppercase;}
  .divider::before, .divider::after{ content:""; height:1px; width:80px; background:linear-gradient(to right, transparent, var(--line)); }

  header{
    position:sticky; top:0; z-index:50;
    background:rgba(240,242,247,0.80);
    backdrop-filter:blur(20px) saturate(180%);
    -webkit-backdrop-filter:blur(20px) saturate(180%);
    border-bottom:1px solid rgba(255,255,255,0.7);
    box-shadow: 0 1px 0 rgba(15,22,35,0.06), 0 4px 16px rgba(15,22,35,0.04);
  }
  .nav{ display:flex; align-items:center; justify-content:space-between; padding:14px 0; }
  .brand{ display:flex; align-items:center; gap:10px; font-weight:800; font-size:19px; letter-spacing:-0.03em; }
  .brand .mark{width:30px; height:30px;}
  .nav-links{ display:flex; align-items:center; gap:28px; font-size:14.5px; font-weight:500; }
  .nav-links a{ color:var(--ink); opacity:0.65; transition:opacity .2s, color .2s; }
  .nav-links a:hover{ opacity:1; }
  .nav-links a.cta-btn{ color:#fff; opacity:1; }
  .nav-links a.cta-btn:hover{ opacity:1; color:#fff; }
  .cta-btn{
    background: linear-gradient(135deg, var(--ink) 0%, #1e2d4a 100%);
    color:#fff; padding:11px 22px; border-radius:999px;
    font-size:14px; font-weight:700;
    border:none;
    box-shadow: 0 4px 14px rgba(15,22,35,0.25), inset 0 1px 0 rgba(255,255,255,0.1);
    transition: transform .18s ease, box-shadow .18s ease;
    display:inline-block;
  }
  .cta-btn:hover{ transform:translateY(-2px); box-shadow: 0 8px 24px rgba(15,22,35,0.30), inset 0 1px 0 rgba(255,255,255,0.1); }
  .login-btn{
    padding:9px 18px; border-radius:999px; font-size:14px; font-weight:600;
    border:1.5px solid rgba(15,22,35,0.25);
    color:var(--ink) !important; opacity:1 !important;
    display:inline-block;
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(8px);
    transition:transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
  }
  .login-btn:hover{ background:var(--ink); color:#fff !important; transform:translateY(-2px); border-color:var(--ink); box-shadow: 0 6px 20px rgba(15,22,35,0.22); }
  .cta-teal{
    background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
    color:#fff; box-shadow: var(--shadow-teal);
  }
  .cta-teal:hover{ box-shadow: 0 12px 36px rgba(47,184,174,0.35); }
  .cta-ghost{ background:rgba(255,255,255,0.7); color:var(--ink); border:1.5px solid var(--line); backdrop-filter:blur(8px); box-shadow:var(--shadow-sm); }
  .cta-ghost:hover{ background:#fff; box-shadow:var(--shadow-md); }
  .menu-toggle{ display:none; background:none; border:none; cursor:pointer; padding:6px; }
  .menu-toggle svg{ width:26px; height:26px; }
  :focus-visible{ outline:2px solid var(--teal); outline-offset:3px; border-radius:4px; }

  @media (max-width:860px){
    .nav-links{ position:fixed; inset:64px 0 auto 0; flex-direction:column; align-items:flex-start; background:rgba(240,242,247,0.97); backdrop-filter:blur(20px); padding:20px 28px 28px; gap:18px; border-bottom:1px solid var(--line); transform:translateY(-130%); transition:transform .3s ease; }
    .nav-links.show{ transform:translateY(0); }
    .menu-toggle{ display:block; }
    .cta-btn.desktop-only{ display:none; }
  }

  .hero{ padding:100px 0 72px; }
  .hero-grid{ display:grid; grid-template-columns:1.1fr 0.9fr; gap:64px; align-items:center; }
  @media (max-width:900px){ .hero-grid{ grid-template-columns:1fr; } .hero-visual{order:-1;} }
  .eyebrow{
    font-family:'JetBrains Mono', monospace; font-size:12px; letter-spacing:0.15em; text-transform:uppercase;
    color:var(--teal); font-weight:600; margin-bottom:20px; display:inline-flex; align-items:center; gap:8px;
    background:rgba(47,184,174,0.10); border:1px solid rgba(47,184,174,0.25); padding:6px 14px; border-radius:999px;
  }
  .hero h1{ font-size:clamp(36px, 5.2vw, 62px); font-weight:800; line-height:1.04; margin-bottom:24px; letter-spacing:-0.03em; }
  .hero h1 .accent{ color:var(--amber); background:linear-gradient(135deg,var(--amber),#c97f22); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .hero p.lead{ font-size:18px; color:var(--muted); max-width:50ch; margin-bottom:36px; line-height:1.7; }
  .hero-ctas{ display:flex; gap:12px; flex-wrap:wrap; }
  .hero-stats{ display:flex; gap:40px; margin-top:52px; flex-wrap:wrap; padding-top:32px; border-top:1px solid var(--line-soft); }
  .stat b{ font-family:'Space Grotesk',sans-serif; font-size:28px; font-weight:800; display:block; letter-spacing:-0.03em; }
  .stat span{ font-size:12.5px; color:var(--muted); }
  .hero-visual{
    background: radial-gradient(140% 140% at 25% 15%, #1e2d4a 0%, #0f1623 55%, #12172B 100%);
    border-radius:28px; aspect-ratio:1/1; display:flex; align-items:center; justify-content:center;
    position:relative; overflow:hidden;
    box-shadow: var(--shadow-lg), 0 0 0 1px rgba(255,255,255,0.06);
  }
  .hero-visual::after{ content:""; position:absolute; inset:0; background: linear-gradient(120deg, transparent 35%, rgba(232,163,61,0.15) 100%); }
  .hero-visual::before{ content:""; position:absolute; top:-40%; right:-20%; width:70%; height:70%; background:radial-gradient(circle, rgba(47,184,174,0.15) 0%, transparent 65%); pointer-events:none; }

  .vm-grid{ display:grid; grid-template-columns:1fr 1fr; gap:28px; }
  @media (max-width:760px){ .vm-grid{ grid-template-columns:1fr; } }
  .vm-card{ background:var(--card); border:1px solid var(--line-soft); border-radius:var(--radius); padding:40px; box-shadow:var(--shadow-md); transition:transform 0.3s cubic-bezier(0.16,1,0.3,1), box-shadow 0.3s; }
  .vm-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lg); }
  .vm-card h3{ font-size:23px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-weight:700; }
  .vm-card p{ color:var(--muted); margin:0; line-height:1.75; }
  .vm-card ul{ margin:0; padding-left:20px; color:var(--muted); }
  .vm-card li{ margin-bottom:10px; }
  .tag-dot{ width:9px; height:9px; border-radius:50%; background:var(--amber); display:inline-block; box-shadow:0 0 10px var(--amber); }
  .vm-card:nth-child(2) .tag-dot{ background:var(--teal); box-shadow:0 0 10px var(--teal); }

  .section-head{ margin-bottom:52px; max-width:640px; }
  .section-head h2{ font-size:clamp(28px,3.6vw,42px); margin-bottom:14px; font-weight:800; letter-spacing:-0.03em; }
  .section-head p{ color:var(--muted); font-size:16.5px; line-height:1.75; }

  .services{ display:grid; grid-template-columns:1fr 1fr; gap:28px; }
  @media (max-width:760px){ .services{ grid-template-columns:1fr; } }
  .service-card{
    background:var(--card); border-radius:var(--radius); padding:42px;
    border:1px solid var(--line-soft); position:relative; overflow:hidden;
    box-shadow:var(--shadow-md);
    transition:transform 0.35s cubic-bezier(0.16,1,0.3,1), box-shadow 0.35s;
  }
  .service-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,var(--teal),var(--teal-dark)); opacity:0; transition:opacity 0.3s; }
  .service-card.alt::before{ background:linear-gradient(90deg,var(--amber),#c97f22); }
  .service-card:hover::before{ opacity:1; }
  .service-card:hover{ transform:translateY(-8px); box-shadow:var(--shadow-lg); }
  .service-card .num{ font-family:'JetBrains Mono',monospace; color:var(--muted); font-size:12px; letter-spacing:0.12em; opacity:0.6; }
  .service-card h3{ font-size:27px; margin:16px 0 12px; font-weight:800; letter-spacing:-0.02em; }
  .service-card > p{ color:var(--muted); margin-bottom:24px; line-height:1.7; }
  .service-card ul{ list-style:none; margin:0; padding:0; display:grid; gap:12px; }
  .service-card li{ display:flex; align-items:flex-start; gap:10px; font-size:15px; }
  .service-card li svg{ width:17px; height:17px; flex:none; margin-top:3px; color:var(--teal); }
  .service-card.alt li svg{ color:var(--amber); }
  .service-card .icon-badge{ width:56px; height:56px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, var(--teal), var(--teal-dark)); color:#fff; margin-bottom:8px; box-shadow:var(--shadow-teal); }
  .service-card.alt .icon-badge{ background:linear-gradient(135deg, var(--amber), #c97f22); box-shadow:var(--shadow-amber); }
  .icon-badge svg{ width:26px; height:26px; }

  .chip-note{ font-size:13px; color:var(--muted); margin-top:24px; margin-bottom:10px; font-weight:600; }
  .chip-list{ display:flex; flex-wrap:wrap; gap:9px; }
  .chip{ background:rgba(63,169,160,0.12); color:#1f5c57; border:1px solid rgba(63,169,160,0.32); padding:7px 13px; border-radius:999px; font-size:12.5px; font-weight:500; }

  .portfolio-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; }
  @media (max-width:900px){ .portfolio-grid{ grid-template-columns:repeat(2,1fr); } }
  @media (max-width:560px){ .portfolio-grid{ grid-template-columns:1fr; } }
  .p-item{ border-radius:var(--radius); overflow:hidden; border:1px solid var(--line-soft); background:var(--card); cursor:pointer; transition:transform 0.35s cubic-bezier(0.16,1,0.3,1), box-shadow 0.35s; }
  .p-item:hover{ transform:translateY(-6px); box-shadow:var(--shadow-md); }
  .p-item.hidden{ display:none !important; }
  .p-thumb{ aspect-ratio:4/3; display:flex; align-items:center; justify-content:center; position:relative; color:#fff; overflow:hidden; background:#12172B; }
  .p-thumb img{ width:100%; height:100%; object-fit:cover; display:block; transition:transform 0.6s cubic-bezier(0.16,1,0.3,1); }
  .p-item:hover .p-thumb img{ transform:scale(1.08); }
  .p-thumb span.ph-label{ position:absolute; bottom:10px; left:12px; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; background:rgba(18,23,43,0.75); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); color:#fff; padding:4px 10px; border-radius:6px; z-index:2; border:1px solid rgba(255,255,255,0.15); }
  .p-body{ padding:16px 18px 20px; }
  .p-body h4{ font-size:16px; margin-bottom:4px; font-weight:700; }
  .p-body p{ margin:0; font-size:13.5px; color:var(--muted); line-height:1.6; }

  /* CATEGORY FILTER TABS */
  .p-filter-tabs{ display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-bottom:36px; }
  .pf-tab{ background:rgba(255,255,255,0.7); border:1.5px solid var(--line); border-radius:999px; padding:8px 20px; font-size:13.5px; font-weight:600; color:var(--muted); cursor:pointer; transition:all 0.2s ease; backdrop-filter:blur(8px); }
  .pf-tab:hover{ border-color:var(--teal); color:var(--ink); transform:translateY(-2px); }
  .pf-tab.active{ background:linear-gradient(135deg,var(--teal),var(--teal-dark)); color:#fff; border-color:transparent; box-shadow:var(--shadow-teal); }

  /* MODAL DETAIL POPUP */
  .p-modal-overlay{
    position:fixed; inset:0; z-index:10000; background:rgba(15,22,35,0.78);
    backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
    display:flex; align-items:center; justify-content:center; padding:24px;
    opacity:0; pointer-events:none; transition:opacity 0.3s cubic-bezier(0.16,1,0.3,1);
  }
  .p-modal-overlay.active{ opacity:1; pointer-events:auto; }
  .p-modal-card{
    background:#ffffff; border-radius:24px; width:100%; max-width:840px; max-height:90vh;
    overflow-y:auto; box-shadow:0 32px 64px rgba(15,22,35,0.4), 0 0 0 1px rgba(255,255,255,0.2);
    transform:scale(0.92) translateY(24px); transition:transform 0.35s cubic-bezier(0.16,1,0.3,1);
    position:relative; color:var(--ink);
  }
  .p-modal-overlay.active .p-modal-card{ transform:scale(1) translateY(0); }
  .p-modal-close{
    position:absolute; top:18px; right:18px; width:38px; height:38px; border-radius:50%;
    background:rgba(15,22,35,0.6); color:#fff; border:1px solid rgba(255,255,255,0.2);
    display:flex; align-items:center; justify-content:center; font-size:20px; cursor:pointer;
    z-index:10; transition:all 0.2s; backdrop-filter:blur(8px);
  }
  .p-modal-close:hover{ background:var(--ink); transform:scale(1.08); }
  .p-modal-media-wrap{ background:#0f1623; border-radius:24px 24px 0 0; overflow:hidden; position:relative; aspect-ratio:16/10; max-height:460px; display:flex; align-items:center; justify-content:center; }
  .p-modal-media-wrap img, .p-modal-media-wrap video{ width:100%; height:100%; object-fit:cover; display:block; }
  .p-modal-gallery-thumbs{ display:flex; gap:10px; padding:14px 20px; background:#161c2b; overflow-x:auto; }
  .pm-thumb{ width:56px; height:56px; border-radius:8px; object-fit:cover; cursor:pointer; border:2px solid transparent; opacity:0.6; transition:all 0.2s; flex:none; }
  .pm-thumb:hover, .pm-thumb.active{ opacity:1; border-color:var(--teal); transform:scale(1.05); }
  .p-modal-body{ padding:32px; }
  .p-modal-cat{ display:inline-block; font-size:12px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:var(--teal); background:rgba(47,184,174,0.1); padding:4px 12px; border-radius:999px; margin-bottom:12px; }
  .p-modal-body h3{ font-size:26px; font-weight:800; margin-bottom:12px; letter-spacing:-0.02em; }
  .p-modal-body p{ font-size:15.5px; color:var(--muted); line-height:1.7; margin-bottom:24px; }
  .p-modal-footer{ display:flex; gap:12px; align-items:center; }
  .grad-1{ background:linear-gradient(135deg,#2d3561,#12172B); }
  .grad-2{ background:linear-gradient(135deg,#3FA9A0,#1f5c57); }
  .grad-3{ background:linear-gradient(135deg,#E8A33D,#a8621c); }
  .grad-4{ background:linear-gradient(135deg,#4a5490,#232a45); }
  .grad-5{ background:linear-gradient(135deg,#c97f22,#7c4c14); }
  .grad-6{ background:linear-gradient(135deg,#2b7d76,#12172B); }
  .grad-7{ background:linear-gradient(135deg,#6b5ea3,#2b2350); }
  .grad-8{ background:linear-gradient(135deg,#3f7fa9,#1a3550); }
  .grad-9{ background:linear-gradient(135deg,#7c8a52,#33391f); }

  .catalog-note{ background:#fff7e8; border:1px dashed #d8a94a; color:#7a5410; font-size:13.5px; padding:14px 18px; border-radius:10px; margin-bottom:40px; }

  .price-group{ margin-bottom:52px; }
  .price-group:last-child{ margin-bottom:0; }
  .price-group h3{ font-size:20px; margin-bottom:22px; display:flex; align-items:center; gap:10px; }
  .price-group h3 .tag-dot{ width:8px; height:8px; border-radius:50%; background:var(--teal); display:inline-block; }
  .price-group.alt h3 .tag-dot{ background:var(--amber); }
  .price-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media (max-width:900px){ .price-grid{ grid-template-columns:1fr; } }
  .price-card{
    background:var(--card); border:1px solid var(--line-soft); border-radius:20px; padding:36px;
    display:flex; flex-direction:column; position:relative;
    box-shadow:var(--shadow-sm);
    transition:transform 0.35s cubic-bezier(0.16,1,0.3,1), box-shadow 0.35s;
  }
  .price-card:hover{ transform:translateY(-6px); box-shadow:var(--shadow-md); }
  .price-card.featured{
    border:2px solid var(--teal);
    background: linear-gradient(160deg, #fff 0%, rgba(47,184,174,0.04) 100%);
    box-shadow: var(--shadow-teal), var(--shadow-md);
  }
  .price-card.featured:hover{ box-shadow: 0 12px 40px rgba(47,184,174,0.30), var(--shadow-lg); transform:translateY(-10px); }
  .price-card .featured-tag{ position:absolute; top:-14px; left:28px; background:linear-gradient(135deg,var(--teal),var(--teal-dark)); color:#fff; font-size:11px; font-weight:800; padding:5px 14px; border-radius:999px; letter-spacing:0.06em; text-transform:uppercase; box-shadow:0 4px 12px rgba(47,184,174,0.35); }
  .price-card h4{ font-size:20px; margin-bottom:6px; font-weight:800; letter-spacing:-0.02em; }
  .price-card .tagline{ color:var(--muted); font-size:14px; margin-bottom:20px; min-height:36px; }
  .price-card .amount{ font-family:'Space Grotesk',sans-serif; font-size:30px; font-weight:800; margin-bottom:6px; letter-spacing:-0.03em; }
  .price-card .amount .from{ font-size:12px; color:var(--muted); font-weight:500; display:block; margin-bottom:4px; }
  .price-card ul{ list-style:none; margin:20px 0 28px; padding:0; display:grid; gap:11px; flex:1; }
  .price-card li{ display:flex; align-items:flex-start; gap:9px; font-size:14px; color:var(--muted); }
  .price-card li svg{ width:16px; height:16px; flex:none; margin-top:3px; color:var(--teal); }
  .price-card .order-btn{
    background:linear-gradient(135deg, var(--ink) 0%, #1e2d4a 100%);
    color:#fff; text-align:center; padding:13px 18px; border-radius:12px;
    font-weight:700; font-size:14.5px;
    box-shadow: 0 4px 14px rgba(15,22,35,0.22);
    transition:transform .2s ease, box-shadow .2s ease;
  }
  .price-card .order-btn:hover{ transform:translateY(-3px); box-shadow: 0 8px 24px rgba(15,22,35,0.32); }
  .price-card.featured .order-btn{ background:linear-gradient(135deg,var(--teal),var(--teal-dark)); box-shadow:var(--shadow-teal); }
  .price-card.featured .order-btn:hover{ box-shadow: 0 10px 32px rgba(47,184,174,0.40); }
  .price-note{ font-size:13px; color:var(--muted); margin-top:28px; text-align:center; }

  .testi-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:22px; margin-bottom:56px; }
  @media (max-width:760px){ .testi-grid{ grid-template-columns:1fr; } }
  .testi-card{
    background:var(--card); border:1px solid var(--line-soft); border-radius:var(--radius); padding:32px;
    box-shadow:var(--shadow-sm);
    transition:transform 0.3s cubic-bezier(0.16,1,0.3,1), box-shadow 0.3s;
    position:relative; overflow:hidden;
  }
  .testi-card::before{ content:''; position:absolute; top:0; left:0; width:3px; height:100%; background:linear-gradient(to bottom, var(--teal), var(--amber)); border-radius:3px 0 0 3px; }
  .testi-card:hover{ transform:translateY(-5px); box-shadow:var(--shadow-md); }
  .stars{ color:var(--amber); font-size:15px; letter-spacing:2px; margin-bottom:14px; }
  .testi-card p.quote{ font-size:15.5px; color:var(--ink); margin-bottom:20px; line-height:1.7; font-style:italic; }
  .testi-who{ display:flex; align-items:center; gap:12px; }
  .avatar{ width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--teal),var(--amber)); flex:none; box-shadow:0 3px 10px rgba(47,184,174,0.3); }
  .testi-who b{ font-size:14px; display:block; font-weight:700; }
  .testi-who span{ font-size:12.5px; color:var(--muted); }
  .empty-note{ color:var(--muted); font-size:14.5px; }

  .review-form-card{ background:var(--card); border:1px solid var(--line-soft); border-radius:var(--radius); padding:36px; max-width:640px; }
  .review-form-card h3{ font-size:21px; margin-bottom:8px; }
  .review-form-card > p{ color:var(--muted); font-size:14.5px; margin-bottom:24px; }
  .field-light{ display:grid; gap:6px; margin-bottom:16px; }
  .field-light label{ font-size:12.5px; color:var(--muted); font-weight:600; }
  .field-light input, .field-light select, .field-light textarea{ background:#fff; border:1px solid var(--line); border-radius:10px; padding:12px 14px; font-family:'Inter',sans-serif; font-size:14.5px; color:var(--ink); }
  .field-light textarea{ resize:vertical; min-height:88px; }
  .hp-field{ position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden; }
  .star-rating{ display:flex; flex-direction:row-reverse; justify-content:flex-end; gap:4px; margin-bottom:6px; }
  .star-rating input{ position:absolute; opacity:0; width:1px; height:1px; }
  .star-rating label{ font-size:28px; color:#d8dbe3; cursor:pointer; transition:color .15s; line-height:1; }
  .star-rating input:checked ~ label,
  .star-rating label:hover,
  .star-rating label:hover ~ label{ color:var(--amber); }
  .star-rating input:focus-visible + label{ outline:2px solid var(--teal); outline-offset:2px; border-radius:4px; }
  .submit-btn-light{ background:var(--ink); color:#fff; border:none; padding:13px 24px; border-radius:10px; font-weight:700; font-size:14.5px; cursor:pointer; transition:transform .18s ease; }
  .submit-btn-light:hover{ transform:translateY(-2px); }
  .alert-success{ background:#e7f7ee; border:1px solid #34a468; color:#1c6b40; padding:14px 18px; border-radius:10px; margin-bottom:28px; font-size:14px; }
  .alert-error{ background:#fdecec; border:1px solid #d9534f; color:#8a2620; padding:14px 18px; border-radius:10px; margin-bottom:28px; font-size:14px; }

  .contact-wrap{ background:var(--ink); color:#fff; border-radius:24px; padding:56px; display:grid; grid-template-columns:1fr 1fr; gap:48px; align-items:center; }
  @media (max-width:860px){ .contact-wrap{ grid-template-columns:1fr; padding:36px 26px; } }
  .contact-wrap h2{ font-size:clamp(24px,3vw,34px); margin-bottom:16px; }
  .contact-wrap p{ color:rgba(255,255,255,0.7); margin-bottom:28px; }
  .contact-info{ display:grid; gap:16px; margin-bottom:8px; }
  .contact-info .row{ display:flex; align-items:center; gap:12px; font-size:14.5px; }
  .contact-info svg{ width:19px; height:19px; color:var(--amber); flex:none; }
  form{ display:grid; gap:14px; }
  .field{ display:grid; gap:6px; }
  .field label{ font-size:12.5px; color:rgba(255,255,255,0.65); }
  .field input, .field select, .field textarea{ background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.16); border-radius:10px; padding:12px 14px; color:#fff; font-family:'Inter',sans-serif; font-size:14.5px; }
  .field input::placeholder, .field textarea::placeholder{ color:rgba(255,255,255,0.35); }
  .field textarea{ resize:vertical; min-height:88px; }
  .submit-btn{ background:var(--amber); color:#241705; border:none; padding:13px 22px; border-radius:10px; font-weight:700; font-size:14.5px; cursor:pointer; transition:transform .18s ease; }
  .submit-btn:hover{ transform:translateY(-2px); }
  .contact-alert{ background:rgba(52,164,104,0.15); border:1px solid #34a468; color:#bdf0d4; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:13.5px; }
  .contact-alert.err{ background:rgba(217,83,79,0.15); border-color:#d9534f; color:#ffc9c6; }

  /* Interaktivitas & Animasi Premium */
  @keyframes floatHero {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(1deg); }
  }
  @keyframes badgePulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(63, 169, 160, 0.4); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(63, 169, 160, 0); }
  }
  @keyframes floatWa {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-7px); }
  }
  @keyframes waPing {
    0% { transform: scale(1); opacity: 0.75; }
    100% { transform: scale(1.65); opacity: 0; }
  }

  .vm-card, .service-card, .price-card, .p-item, .testi-card, .review-form-card {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s ease;
  }
  .vm-card:hover, .service-card:hover, .price-card:hover, .p-item:hover, .testi-card:hover, .review-form-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(18, 23, 43, 0.12);
    border-color: rgba(63, 169, 160, 0.4);
  }

  .service-card .icon-badge {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease;
  }
  .service-card:hover .icon-badge {
    transform: scale(1.12) rotate(-5deg);
    box-shadow: 0 10px 24px rgba(63, 169, 160, 0.35);
  }

  .p-thumb {
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .p-item:hover .p-thumb {
    transform: scale(1.06);
  }

  .price-card.featured {
    border: 2px solid var(--teal);
    box-shadow: 0 12px 32px rgba(63, 169, 160, 0.15);
  }
  .price-card.featured:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 24px 50px rgba(63, 169, 160, 0.28);
  }
  .price-card .featured-tag {
    animation: badgePulse 3s ease-in-out infinite;
  }

  .cta-btn, .submit-btn, .order-btn, .submit-btn-light {
    position: relative;
    overflow: hidden;
  }
  .cta-btn::after, .submit-btn::after, .order-btn::after, .submit-btn-light::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(60deg, transparent, rgba(255, 255, 255, 0.25), transparent);
    transform: translateX(-100%) rotate(30deg);
    transition: transform 0.6s ease;
  }
  .cta-btn:hover::after, .submit-btn:hover::after, .order-btn:hover::after, .submit-btn-light:hover::after {
    transform: translateX(100%) rotate(30deg);
  }

  .wa-float-btn {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 99;
    background: #25D366;
    color: #fff;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(37, 211, 102, 0.45);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
    animation: floatWa 4s ease-in-out infinite;
  }
  .wa-float-btn:hover {
    transform: scale(1.12) translateY(-4deg);
    box-shadow: 0 16px 36px rgba(37, 211, 102, 0.6);
  }
  .wa-float-btn .ping {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: #25D366;
    animation: waPing 2.2s cubic-bezier(0, 0, 0.2, 1) infinite;
    z-index: -1;
  }
  .wa-float-btn svg { width: 28px; height: 28px; fill: currentColor; }

  .reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .reveal.in {
    opacity: 1;
    transform: translateY(0);
  }

  /* Premium Multi-Column Footer Styles */
  footer {
    background: #0d1120;
    color: #e2e4ed;
    padding: 72px 0 36px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 14px;
  }
  .footer-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1.2fr 1.5fr;
    gap: 48px;
    margin-bottom: 56px;
  }
  @media (max-width: 900px) {
    .footer-grid {
      grid-template-columns: 1fr 1fr;
      gap: 36px;
    }
  }
  @media (max-width: 560px) {
    .footer-grid {
      grid-template-columns: 1fr;
      gap: 32px;
    }
  }
  .footer-col .foot-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 20px;
    color: #fff;
    margin-bottom: 16px;
  }
  .footer-col p.desc {
    color: rgba(255, 255, 255, 0.65);
    line-height: 1.65;
    font-size: 14px;
    margin-bottom: 20px;
  }
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(63, 169, 160, 0.15);
    color: #3FA9A0;
    border: 1px solid rgba(63, 169, 160, 0.3);
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
  }
  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3FA9A0;
    box-shadow: 0 0 10px #3FA9A0;
  }
  .footer-col h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 20px;
    letter-spacing: -0.01em;
  }
  .footer-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 11px;
  }
  .footer-col ul a {
    color: rgba(255, 255, 255, 0.65);
    text-decoration: none;
    transition: color 0.2s ease, transform 0.2s ease;
    display: inline-block;
  }
  .footer-col ul a:hover {
    color: var(--amber);
    transform: translateX(4px);
  }
  .payment-methods {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
  }
  .payment-chip {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.85);
    padding: 5px 11px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
  }
  .footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
  }
  .footer-bottom .copy {
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
  }
  .footer-bottom .sub-links {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
  }
  .footer-bottom .sub-links a {
    color: rgba(255, 255, 255, 0.5);
    transition: color 0.2s;
  }
  .footer-bottom .sub-links a:hover {
    color: #fff;
  }
  .back-to-top {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .back-to-top:hover {
    background: rgba(255, 255, 255, 0.18);
    transform: translateY(-2px);
  }

  /* Chatbot Floating Widget Styles */
  .chatbot-float-btn {
    position: fixed;
    bottom: 96px;
    right: 28px;
    z-index: 98;
    background: #12172B;
    color: #fff;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(18, 23, 43, 0.35);
    border: 2px solid var(--teal);
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
  }
  .chatbot-float-btn:hover {
    transform: scale(1.1) translateY(-3px);
    box-shadow: 0 14px 32px rgba(63, 169, 160, 0.4);
  }
  .chatbot-float-btn .badge-dot {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: var(--amber);
    border: 2px solid #12172B;
    border-radius: 50%;
  }

  .chatbot-window {
    position: fixed;
    bottom: 96px;
    right: 28px;
    width: 370px;
    max-width: calc(100vw - 40px);
    height: 520px;
    max-height: calc(100vh - 120px);
    background: #FFFFFF;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(18, 23, 43, 0.25);
    border: 1px solid var(--line);
    z-index: 100;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    transform: translateY(20px) scale(0.95);
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .chatbot-window.open {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0) scale(1);
  }

  .chat-header {
    background: #12172B;
    color: #fff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .chat-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .chat-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--amber));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    flex: none;
  }
  .chat-header-info h5 {
    margin: 0;
    font-size: 15px;
    font-family: 'Space Grotesk', sans-serif;
  }
  .chat-header-info span {
    font-size: 12px;
    color: #3FA9A0;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .chat-header-info span::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #3FA9A0;
  }
  .chat-close-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    font-size: 20px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
  }
  .chat-close-btn:hover {
    color: #fff;
  }

  .chat-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #F8F9FC;
  }
  .chat-msg {
    max-width: 84%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
  }
  .chat-msg.bot {
    background: #FFFFFF;
    color: var(--ink);
    border: 1px solid var(--line-soft);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(18,23,43,0.04);
  }
  .chat-msg.user {
    background: var(--ink);
    color: #fff;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
  }

  .chat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
  }
  .chat-chip {
    background: rgba(63, 169, 160, 0.12);
    color: #1f5c57;
    border: 1px solid rgba(63, 169, 160, 0.25);
    padding: 5px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
  }
  .chat-chip:hover {
    background: rgba(63, 169, 160, 0.25);
    transform: translateY(-1px);
  }

  .chat-typing {
    display: none;
    align-self: flex-start;
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    padding: 4px 16px;
    background: #F8F9FC;
  }
  .chat-typing.show { display: block; }

  .chat-input-area {
    padding: 12px 16px;
    background: #FFFFFF;
    border-top: 1px solid var(--line-soft);
    display: flex;
    gap: 8px;
  }
  .chat-input-area input {
    flex: 1;
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 10px 16px;
    font-size: 13.5px;
    font-family: 'Inter', sans-serif;
    outline: none;
  }
  .chat-input-area input:focus {
    border-color: var(--teal);
  }
  .chat-send-btn {
    background: var(--teal);
    color: #fff;
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex: none;
    transition: background 0.2s, transform 0.15s;
  }
  .chat-send-btn:hover {
    background: #359189;
    transform: scale(1.05);
  }
  .chat-send-btn svg { width: 18px; height: 18px; fill: currentColor; }
</style>
</head>
<body>

<header>
  <div class="wrap nav">
    <a href="#" class="brand">
      <svg class="mark" viewBox="0 0 40 40" fill="none">
        <circle cx="20" cy="20" r="19" stroke="#12172B" stroke-width="2"/>
        <path d="M20 6 L20 20 L30 26" stroke="#E8A33D" stroke-width="2.4" stroke-linecap="round"/>
      </svg>
      Aff Digital
    </a>
    <button class="menu-toggle" id="menuToggle" aria-label="Buka menu" aria-expanded="false">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <nav class="nav-links" id="navLinks">
      <a href="#layanan">Layanan</a>
      <a href="#harga">Harga</a>
      <a href="#portofolio">Portofolio</a>
      <a href="#testimoni">Testimoni</a>
      <a href="#kontak">Kontak</a>
      <a href="admin/login.php" class="login-btn">🔐 Login</a>
      <a href="#kontak" class="cta-btn">Konsultasi Gratis</a>
    </nav>
  </div>
</header>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow">Website Custom · Foto & Video</span>
      <h1>Fokus yang tepat, untuk bisnis yang <span class="accent">terlihat</span>.</h1>
      <p class="lead">Aff Digital membangun website sesuai kebutuhan Anda — dari company profile, toko online, sampai sistem absensi HR dan retail — serta menghasilkan foto &amp; video yang bercerita.</p>
      <div class="hero-ctas">
        <a href="#kontak" class="cta-btn">Mulai Proyek</a>
        <a href="#portofolio" class="cta-btn cta-ghost">Lihat Portofolio</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><b>50+</b><span>Proyek diselesaikan</span></div>
        <div class="stat"><b>2</b><span>Layanan inti</span></div>
        <div class="stat"><b>&lt; 7 hari</b><span>Rata-rata pengerjaan</span></div>
      </div>
    </div>
    <div class="hero-visual">
      <svg class="aperture" id="apertureSvg" viewBox="0 0 220 220" width="72%" height="72%">
        <g><path class="blade" fill="#E8A33D" opacity="0.95" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <g transform="rotate(60 110 110)"><path class="blade" fill="#3FA9A0" opacity="0.95" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <g transform="rotate(120 110 110)"><path class="blade" fill="#F4F1EA" opacity="0.9" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <g transform="rotate(180 110 110)"><path class="blade" fill="#E8A33D" opacity="0.95" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <g transform="rotate(240 110 110)"><path class="blade" fill="#3FA9A0" opacity="0.95" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <g transform="rotate(300 110 110)"><path class="blade" fill="#F4F1EA" opacity="0.9" d="M110 20 L150 60 L110 110 L70 60 Z"/></g>
        <circle cx="110" cy="110" r="14" fill="#12172B"/>
      </svg>
    </div>
  </div>
</section>

<section id="visimisi">
  <div class="wrap">
    <div class="divider"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 3 L12 12 L18 15"/></svg><span class="lbl">Arah Kami</span></div>
    <div class="vm-grid">
      <div class="vm-card reveal">
        <h3><span class="tag-dot"></span> Visi</h3>
        <p>Menjadi mitra digital terpercaya bagi UMKM dan brand Indonesia dalam membangun kehadiran online yang kuat dan autentik — di layar maupun di layar lebar.</p>
      </div>
      <div class="vm-card reveal">
        <h3><span class="tag-dot"></span> Misi</h3>
        <ul>
          <li>Menghadirkan website — sederhana maupun sistem custom — yang cepat, rapi, dan mudah dikelola.</li>
          <li>Menangkap momen dan produk klien melalui foto &amp; video berkualitas tinggi.</li>
          <li>Mengutamakan komunikasi terbuka dan proses kerja yang transparan di setiap proyek.</li>
          <li>Terus mengasah kemampuan mengikuti perkembangan tren digital.</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section id="layanan">
  <div class="wrap">
    <div class="section-head">
      <h2>Layanan kami</h2>
      <p>Dua kemampuan inti yang saling melengkapi — website yang membangun kepercayaan, dan visual yang membuat orang berhenti scroll.</p>
    </div>
    <div class="services">
      <div class="service-card reveal">
        <div class="icon-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><circle cx="6.5" cy="6.5" r="0.6" fill="#fff"/><circle cx="9" cy="6.5" r="0.6" fill="#fff"/></svg>
        </div>
        <span class="num">01</span>
        <h3>Pembuatan Website</h3>
        <p>Dari halaman sederhana sampai sistem kerja harian — dibuat sesuai alur bisnis Anda, bukan template pasaran.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Website company profile & landing page</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Toko online / katalog produk</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Sistem custom sesuai kebutuhan (lihat contoh di bawah)</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Optimasi kecepatan & tampilan mobile</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Pendampingan setelah website live</li>
        </ul>
        <div class="chip-note">Contoh sistem custom yang bisa dibuat:</div>
        <div class="chip-list">
          <span class="chip">Absensi & HR</span>
          <span class="chip">Kasir / Retail (POS)</span>
          <span class="chip">Manajemen Stok & Inventori</span>
          <span class="chip">Manajemen Gudang (Warehouse)</span>
          <span class="chip">Reservasi / Booking</span>
          <span class="chip">Dashboard Admin & Laporan</span>
          <span class="chip">Membership Pelanggan</span>
          <span class="chip">Sistem Akademik/Sekolah</span>
        </div>
      </div>
      <div class="service-card alt reveal">
        <div class="icon-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
        </div>
        <span class="num">02</span>
        <h3>Foto & Video</h3>
        <p>Dokumentasi produk, event, hingga video promosi yang siap dipakai untuk katalog, marketplace, dan media sosial.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Foto produk untuk katalog & marketplace</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Video promosi & company profile</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Dokumentasi foto & video event</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Konten siap unggah untuk media sosial</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Editing & color grading profesional</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($packagesByCategory)): ?>
<section id="harga">
  <div class="wrap">
    <div class="section-head">
      <h2>Paket & harga</h2>
      <p>Pilih paket sesuai kebutuhan, lalu bayar langsung dan aman lewat Midtrans (kartu, e-wallet, VA, QRIS). Harga dapat menyesuaikan setelah konsultasi untuk kebutuhan custom.</p>
    </div>

    <?php
    $categoryLabels = [
        'website'    => ['title' => 'Pembuatan Website', 'class' => ''],
        'foto_video' => ['title' => 'Foto & Video', 'class' => 'alt'],
    ];
    foreach ($categoryLabels as $catKey => $catInfo):
        if (empty($packagesByCategory[$catKey])) continue;
    ?>
      <div class="price-group <?php echo $catInfo['class']; ?>">
        <h3><span class="tag-dot"></span> <?php echo h($catInfo['title']); ?></h3>
        <div class="price-grid">
          <?php foreach ($packagesByCategory[$catKey] as $i => $pkg): ?>
            <div class="price-card <?php echo $i === 1 ? 'featured' : ''; ?> reveal">
              <?php if ($i === 1): ?><span class="featured-tag">Populer</span><?php endif; ?>
              <h4><?php echo h($pkg['name']); ?></h4>
              <p class="tagline"><?php echo h($pkg['tagline']); ?></p>
              <div class="amount">
                <span class="from">Mulai dari</span>
                <?php echo h(format_rupiah($pkg['price'])); ?>
              </div>
              <ul>
                <?php foreach (array_filter(array_map('trim', explode("\n", (string) $pkg['features']))) as $feat): ?>
                  <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> <?php echo h($feat); ?></li>
                <?php endforeach; ?>
              </ul>
              <a class="order-btn" href="checkout.php?package=<?php echo (int) $pkg['id']; ?>">Pesan Paket Ini</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <p class="price-note">Butuh kebutuhan di luar paket di atas? <a href="#kontak">Hubungi kami</a> untuk penawaran khusus.</p>
  </div>
</section>
<?php endif; ?>

<section id="portofolio">
  <div class="wrap">
    <div class="section-head" style="text-align:center;margin:0 auto 36px;">
      <h2>Katalog & portofolio</h2>
      <p>Contoh jenis website, sistem custom, serta hasil karya foto & video profesional dari Aff Digital.</p>
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
      <div class="p-item reveal" data-category="<?php echo h($catLabel); ?>" data-portfolio="<?php echo h($dataJson); ?>">
        <div class="p-thumb">
          <?php if (($p['media_type'] ?? 'image') === 'video'): ?>
            <video src="<?php echo h($p['media_url']); ?>" autoplay loop muted playsinline style="width:100%;height:100%;object-fit:cover;display:block;"></video>
          <?php else: ?>
            <img src="<?php echo h($p['media_url']); ?>" alt="<?php echo h($p['title']); ?>" loading="lazy">
          <?php endif; ?>
          <span class="ph-label"><?php echo h($catLabel); ?></span>
        </div>
        <div class="p-body">
          <h4><?php echo h($p['title']); ?></h4>
          <p><?php echo h($p['description']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PORTFOLIO DETAIL MODAL POPUP -->
<div class="p-modal-overlay" id="portfolioModal" aria-hidden="true">
  <div class="p-modal-card">
    <button class="p-modal-close" id="modalCloseBtn" aria-label="Tutup Detail">&times;</button>
    <div class="p-modal-media-wrap" id="modalMediaContainer">
      <!-- Dynamic Media (Image/Video) inserted here via JS -->
    </div>
    <div class="p-modal-gallery-thumbs" id="modalGalleryContainer" style="display:none;">
      <!-- Dynamic Thumbnail Strip inserted here via JS -->
    </div>
    <div class="p-modal-body">
      <span class="p-modal-cat" id="modalCategory">Kategori</span>
      <h3 id="modalTitle">Judul Proyek</h3>
      <p id="modalDescription">Deskripsi proyek...</p>
      <div class="p-modal-footer">
        <a href="#kontak" onclick="closePortfolioModal()" class="cta-btn cta-teal">Konsultasikan Proyek Serupa &rarr;</a>
      </div>
    </div>
  </div>
</div>

<section id="testimoni">
  <div class="wrap">
    <div class="section-head">
      <h2>Kata pelanggan</h2>
      <p>Pengalaman nyata dari pelanggan yang telah mempercayakan proyek website dan konten visual mereka bersama Aff Digital.</p>
    </div>

    <?php if ($showOk): ?>
      <div class="alert-success">Terima kasih! Ulasan Anda berhasil dikirim dan langsung tampil di bawah.</div>
    <?php endif; ?>
    <?php if ($showErr): ?>
      <div class="alert-error">Nama dan pesan ulasan tidak boleh kosong. Silakan coba lagi.</div>
    <?php endif; ?>

    <div class="testi-grid">
      <?php if (empty($comments)): ?>
        <p class="empty-note">Belum ada ulasan. Jadilah yang pertama menulis komentar di bawah ini.</p>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
          <div class="testi-card reveal">
            <div class="stars"><?php echo stars_for($c['rating'] ?? 5); ?></div>
            <p class="quote">"<?php echo h($c['message'] ?? ''); ?>"</p>
            <div class="testi-who">
              <div class="avatar"></div>
              <div><b><?php echo h($c['name'] ?? 'Pelanggan'); ?></b><span><?php echo h($c['layanan'] ?? ''); ?></span></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="review-form-card reveal" id="tulis-ulasan">
      <h3>Tulis ulasan Anda</h3>
      <p>Sudah pernah pakai jasa Aff Digital? Bagikan pengalaman Anda di sini.</p>
      <form action="process_comment.php" method="POST">
        <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off">
        <div class="field-light">
          <label for="rname">Nama</label>
          <input id="rname" name="name" type="text" placeholder="Nama Anda" required maxlength="60">
        </div>
        <div class="field-light">
          <label for="rlayanan">Layanan yang digunakan</label>
          <select id="rlayanan" name="layanan">
            <option>Pembuatan Website</option>
            <option>Foto & Video</option>
            <option>Keduanya</option>
          </select>
        </div>
        <div class="field-light">
          <label>Rating</label>
          <div class="star-rating">
            <input type="radio" id="s5" name="rating" value="5"><label for="s5">★</label>
            <input type="radio" id="s4" name="rating" value="4"><label for="s4">★</label>
            <input type="radio" id="s3" name="rating" value="3"><label for="s3">★</label>
            <input type="radio" id="s2" name="rating" value="2"><label for="s2">★</label>
            <input type="radio" id="s1" name="rating" value="1"><label for="s1">★</label>
          </div>
        </div>
        <div class="field-light">
          <label for="rmessage">Ulasan</label>
          <textarea id="rmessage" name="message" placeholder="Ceritakan pengalaman Anda..." required maxlength="500"></textarea>
        </div>
        <button class="submit-btn-light" type="submit">Kirim Ulasan</button>
      </form>
    </div>
  </div>
</section>

<section id="kontak">
  <div class="wrap">
    <div class="contact-wrap">
      <div>
        <span class="eyebrow" style="color:#E8A33D;">Mulai Proyek</span>
        <h2>Punya kebutuhan website atau konten visual?</h2>
        <p>Ceritakan kebutuhan Anda, kami bantu wujudkan dalam waktu singkat.</p>
        <div class="contact-info">
          <div class="row"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg> 0812-0000-0000 (ganti dengan nomor Anda)</div>
          <div class="row"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/></svg> hello@affdigital.id (ganti dengan email Anda)</div>
          <div class="row"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Kota Anda, Indonesia</div>
        </div>
      </div>
      <div>
        <?php if ($showOkMsg): ?>
          <div class="contact-alert">Terima kasih, pesan Anda sudah kami terima dan tersimpan.</div>
        <?php endif; ?>
        <?php if ($showErrMsg): ?>
          <div class="contact-alert err">Nama dan kontak wajib diisi. Silakan coba lagi.</div>
        <?php endif; ?>
        <form action="process_message.php" method="POST">
          <input class="hp-field" type="text" name="company" tabindex="-1" autocomplete="off">
          <div class="field"><label for="nama">Nama</label><input id="nama" name="nama" type="text" placeholder="Nama lengkap" required></div>
          <div class="field"><label for="kontakinput">Email / No. WhatsApp</label><input id="kontakinput" name="kontak" type="text" placeholder="08xx atau email" required></div>
          <div class="field"><label for="layananinput">Layanan yang diminati</label>
            <select id="layananinput" name="layanan">
              <option>Pembuatan Website</option>
              <option>Foto & Video</option>
              <option>Keduanya</option>
            </select>
          </div>
          <div class="field"><label for="pesan">Pesan</label><textarea id="pesan" name="pesan" placeholder="Ceritakan kebutuhan Anda..."></textarea></div>
          <button class="submit-btn" type="submit">Kirim Pesan</button>
        </form>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="foot-brand">
          <svg class="mark" viewBox="0 0 40 40" fill="none" width="28" height="28">
            <circle cx="20" cy="20" r="19" stroke="#3FA9A0" stroke-width="2"/>
            <path d="M20 6 L20 20 L30 26" stroke="#E8A33D" stroke-width="2.4" stroke-linecap="round"/>
          </svg>
          Aff Digital
        </div>
        <p class="desc">Mitra solusi digital terpercaya untuk pengembangan website custom, sistem aplikasi bisnis (HR, POS, Inventori), serta produksi konten foto & video profesional.</p>
        <div class="status-badge">
          <span class="status-dot"></span>
          Accepting New Projects 2026
        </div>
      </div>

      <div class="footer-col">
        <h4>Layanan Inti</h4>
        <ul>
          <li><a href="#layanan">Company Profile & Landing Page</a></li>
          <li><a href="#layanan">Toko Online (E-Commerce)</a></li>
          <li><a href="#layanan">Sistem Custom (POS, HR, Inventori)</a></li>
          <li><a href="#layanan">Foto Produk Studio</a></li>
          <li><a href="#layanan">Video Promosi & Sosmed</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Navigasi</h4>
        <ul>
          <li><a href="#">Beranda</a></li>
          <li><a href="#visimisi">Arah Kami</a></li>
          <li><a href="#harga">Paket & Harga</a></li>
          <li><a href="#portofolio">Katalog & Portofolio</a></li>
          <li><a href="#testimoni">Ulasan Pelanggan</a></li>
          <li><a href="#kontak">Konsultasi Gratis</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Pembayaran Aman</h4>
        <p class="desc" style="font-size:13px; margin-bottom:12px;">Transaksi terenkripsi aman didukung oleh Payment Gateway resmi Indonesia:</p>
        <div class="payment-methods">
          <span class="payment-chip">Midtrans</span>
          <span class="payment-chip">iPaymu</span>
          <span class="payment-chip">QRIS</span>
          <span class="payment-chip">BCA / Mandiri / BRI</span>
          <span class="payment-chip">Gopay / ShopeePay</span>
          <span class="payment-chip">Alfamart / Indomaret</span>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <span class="copy">&copy; 2026 Aff Digital. Semua hak dilindungi.</span>
      <div class="sub-links">
        <a href="syarat-ketentuan.php">Syarat &amp; Ketentuan</a>
        <a href="refund-policy.php">Kebijakan Pengembalian Dana</a>
        <a href="faq.php">FAQ</a>
      </div>
      <button class="back-to-top" onclick="window.scrollTo({top:0, behavior:'smooth'})">
        &uarr; Kembali ke Atas
      </button>
    </div>
  </div>
</footer>

<script>
  window.addEventListener('load', () => {
    const ap = document.getElementById('apertureSvg');
    requestAnimationFrame(() => setTimeout(() => ap.classList.add('open'), 150));
  });

  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');
  menuToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('show');
    menuToggle.setAttribute('aria-expanded', isOpen);
  });
  navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => navLinks.classList.remove('show')));

  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: 0.15 });
  revealEls.forEach(el => io.observe(el));
</script>

<!-- Floating Chatbot Widget -->
<button class="chatbot-float-btn" id="chatTrigger" aria-label="Buka Chat AI Assistant" title="Konsultasi AI Assistant">
  <span class="badge-dot"></span>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
  </svg>
</button>

<div class="chatbot-window" id="chatWindow">
  <div class="chat-header">
    <div class="chat-header-info">
      <div class="chat-avatar">AFF</div>
      <div>
        <h5>Aff Assistant</h5>
        <span>Online &amp; Ready</span>
      </div>
    </div>
    <button class="chat-close-btn" id="chatClose" title="Tutup Chat">&times;</button>
  </div>
  <div class="chat-messages" id="chatMsgs">
    <div class="chat-msg bot">
      Halo! 👋 Selamat datang di <b>Aff Digital</b>.<br>
      Saya asisten virtual yang siap membantu Anda seputar harga paket website, foto &amp; video, sistem custom, atau cara pemesanan.
    </div>
    <div class="chat-chips">
      <button type="button" class="chat-chip" onclick="sendQuickMsg('Berapa harga pembuatan website?')">💡 Harga Website</button>
      <button type="button" class="chat-chip" onclick="sendQuickMsg('Apa saja paket foto & video?')">📷 Foto &amp; Video</button>
      <button type="button" class="chat-chip" onclick="sendQuickMsg('Bagaimana cara pesan sistem custom?')">🛠️ Sistem Custom</button>
      <button type="button" class="chat-chip" onclick="sendQuickMsg('Apa saja metode pembayaran?')">💳 Metode Bayar</button>
    </div>
  </div>
  <div class="chat-typing" id="chatTyping">Aff Assistant sedang mengetik...</div>
  <form class="chat-input-area" id="chatForm" onsubmit="return handleChatSubmit(event)">
    <input type="text" id="chatInput" placeholder="Ketik pertanyaan Anda..." autocomplete="off">
    <button type="submit" class="chat-send-btn" title="Kirim Pesan">
      <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
    </button>
  </form>
</div>

<script>
  const chatTrigger = document.getElementById('chatTrigger');
  const chatWindow = document.getElementById('chatWindow');
  const chatClose = document.getElementById('chatClose');
  const chatMsgs = document.getElementById('chatMsgs');
  const chatInput = document.getElementById('chatInput');
  const chatTyping = document.getElementById('chatTyping');

  chatTrigger.addEventListener('click', () => chatWindow.classList.toggle('open'));
  chatClose.addEventListener('click', () => chatWindow.classList.remove('open'));

  function appendChatMsg(text, sender = 'bot') {
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${sender}`;
    msgDiv.innerHTML = text;
    chatMsgs.appendChild(msgDiv);
    chatMsgs.scrollTop = chatMsgs.scrollHeight;
  }

  function sendQuickMsg(text) {
    appendChatMsg(text, 'user');
    processBotReply(text);
  }

  function handleChatSubmit(e) {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (!text) return false;
    appendChatMsg(text, 'user');
    chatInput.value = '';
    processBotReply(text);
    return false;
  }

  function processBotReply(userQuery) {
    chatTyping.classList.add('show');
    const q = userQuery.toLowerCase();
    let reply = "";

    if (q.includes('harga') || q.includes('biaya') || q.includes('paket') || q.includes('website')) {
      reply = "Paket website di Aff Digital mulai dari <b>Rp 1.500.000</b> (Basic Landing Page), <b>Rp 3.500.000</b> (Pro E-Commerce), hingga sistem custom mulai Rp 7.500.000. Anda bisa melihat daftar lengkapnya di section <a href='#harga' onclick='chatWindow.classList.remove(\"open\")' style='color:#3FA9A0;font-weight:600;'>Paket & Harga</a>!";
    } else if (q.includes('foto') || q.includes('video') || q.includes('promosi') || q.includes('katalog')) {
      reply = "Layanan Foto & Video kami meliputi:<br>• Foto Produk Katalog (mulai Rp 750rb)<br>• Foto & Video Promosi Sosmed (mulai Rp 2jt)<br>• Dokumentasi Event Full Day (mulai Rp 3,5jt).";
    } else if (q.includes('custom') || q.includes('pos') || q.includes('absensi') || q.includes('gudang') || q.includes('sistem')) {
      reply = "Kami melayani pembuatan sistem custom sesuai alur bisnis Anda! Seperti Sistem Kasir/POS, Absensi HR & Penggajian, Inventori Gudang, hingga Portal Sekolah. Silakan konsultasi via <a href='#kontak' onclick='chatWindow.classList.remove(\"open\")' style='color:#3FA9A0;font-weight:600;'>Form Kontak</a> atau WhatsApp.";
    } else if (q.includes('bayar') || q.includes('pembayaran') || q.includes('gateway') || q.includes('midtrans') || q.includes('ipaymu')) {
      reply = "Pembayaran di Aff Digital sangat aman dan praktis melalui <b>Midtrans</b> & <b>iPaymu</b> (Transfer Bank/VA, QRIS, Kartu Kredit, GoPay, ShopeePay, Alfamart/Indomaret).";
    } else if (q.includes('kontak') || q.includes('wa') || q.includes('whatsapp') || q.includes('hubungi') || q.includes('tanya')) {
      reply = "Anda dapat langsung berkonsultasi dengan tim kami via WhatsApp (tombol hijau di kanan bawah) atau kirim pesan melalui <a href='#kontak' onclick='chatWindow.classList.remove(\"open\")' style='color:#3FA9A0;font-weight:600;'>Form Kontak</a>!";
    } else {
      reply = "Terima kasih atas pertanyaannya! Untuk kebutuhan lebih spesifik atau penawaran khusus, Anda dapat langsung berkonsultasi gratis dengan tim Aff Digital melalui form kontak di bawah atau via WhatsApp.";
    }

    setTimeout(() => {
      chatTyping.classList.remove('show');
      appendChatMsg(reply, 'bot');
    }, 650);
  }

  // --- PORTFOLIO CATEGORY FILTERING & DETAIL MODAL POPUP ---
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Filter Tabs
    const filterTabs = document.querySelectorAll('.pf-tab');
    const portfolioItems = document.querySelectorAll('.p-item');

    filterTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const filter = tab.getAttribute('data-filter');
        portfolioItems.forEach(item => {
          const itemCat = item.getAttribute('data-category');
          if (filter === 'all' || itemCat === filter) {
            item.classList.remove('hidden');
          } else {
            item.classList.add('hidden');
          }
        });
      });
    });

    // 2. Modal Lightbox Detail Popup
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
      modalDescription.textContent = data.description || 'Tidak ada deskripsi.';

      let images = [];
      if (data.images_json) {
        try {
          const parsed = JSON.parse(data.images_json);
          if (Array.isArray(parsed) && parsed.length > 0) {
            images = parsed;
          }
        } catch (e) {}
      }
      if (images.length === 0 && data.media_url) {
        images = [data.media_url];
      }

      // Render main media
      function setMainMedia(url, isVid = false) {
        if (isVid || (data.media_type === 'video' && url === data.media_url)) {
          modalMediaContainer.innerHTML = `<video src="${url}" controls autoplay loop muted style="width:100%;height:100%;object-fit:contain;background:#0d1117;"></video>`;
        } else {
          modalMediaContainer.innerHTML = `<img src="${url}" alt="${data.title}" style="width:100%;height:100%;object-fit:contain;background:#0d1117;">`;
        }
      }

      setMainMedia(images[0] || data.media_url);

      // Render thumbnail strip if multiple images
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
        modalGalleryContainer.innerHTML = '';
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

    portfolioItems.forEach(item => {
      item.addEventListener('click', () => {
        const rawJson = item.getAttribute('data-portfolio');
        if (rawJson) {
          try {
            const data = JSON.parse(rawJson);
            openPortfolioModal(data);
          } catch(e) {}
        }
      });
    });

    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closePortfolioModal);
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) closePortfolioModal();
      });
    }
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        closePortfolioModal();
      }
    });
  });
</script>

</body>
</html>
