<?php
// index.php — Aff Digital (Editorial Dark Luxury Edition with Direct Payment Gateway Checkout)
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

// Fallback demo packages if DB empty
if (empty($packagesByCategory)) {
    $packagesByCategory = [
        'Website & System' => [
            [
                'id' => 1,
                'name' => 'Paket Basic Website',
                'price' => '1500000',
                'description' => 'Cocok untuk Landing Page Perusahaan, UMKM, & Company Profile Modern.',
                'features' => "1 Halaman Responsive Landing Page\nDesain Custom Premium Dark/Light\nIntegrasi Tombol WhatsApp Direct\nDomain & Hosting 1 Tahun\nFree Maintenance 1 Bulan"
            ],
            [
                'id' => 2,
                'name' => 'Paket Custom POS & HR System',
                'price' => '3500000',
                'description' => 'Pilihan utama bisnis toko/resto yang ingin sistem Kasir POS & Presensi Karyawan terintegrasi.',
                'features' => "Sistem Kasir POS Multi-Company\nPortal Karyawan & Presensi GPS\nKelola Stok, Harga & Menu Makanan\nLaporan Omset Harian/Bulanan\nDatabase System Dedicated"
            ],
            [
                'id' => 3,
                'name' => 'Paket Ultimate Enterprise',
                'price' => '6000000',
                'description' => 'Pengembangan software skala besar dengan kebutuhan arsitektur custom khusus.',
                'features' => "Custom Web & Backend Architecture\nIntegrasi Payment Gateway & WhatsApp API\nFull Source Code & Database Handover\nDokumentasi Sistem Lengkap\nSupport Prioritas 24/7 (6 Bulan)"
            ]
        ]
    ];
}

// Load Comments / Testimonials
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
<html lang="id" class="scroll-smooth h-full bg-[#120c0c]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AFF DIGITAL — Studio Software, Website &amp; Sistem Custom Modern</title>
    <meta name="description" content="AFF Digital membantu bisnis &amp; brand mengubah ide menjadi website berkinerja tinggi, sistem custom (POS Kasir, Portal Karyawan HR), serta media promosi digital profesional.">

    <!-- Favicons -->
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.jpg">

    <!-- Google Fonts & Phosphor Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;1,400&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        bebas: ['"Bebas Neue"', 'cursive'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                        grotesk: ['"Space Grotesk"', 'sans-serif']
                    },
                    colors: {
                        aff: {
                            bg: '#120c0c',
                            card: '#1c1313',
                            cardBorder: '#2d1b1b',
                            cream: '#eee6d8',
                            muted: '#a69090',
                            red: '#8b1818',
                            redBright: '#e63946',
                            redGlow: 'rgba(139, 24, 24, 0.45)'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        ::selection { background: #8b1818; color: #eee6d8; }
        .bg-grid-dark {
            background-image: 
                linear-gradient(to right, rgba(238, 230, 216, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(238, 230, 216, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
    </style>
</head>
<body class="bg-[#120c0c] text-[#eee6d8] font-sans antialiased selection:bg-[#8b1818] selection:text-white bg-grid-dark min-h-full flex flex-col justify-between">

    <!-- TOP HEADER / NAVBAR -->
    <header class="sticky top-0 z-50 bg-[#120c0c]/90 backdrop-blur-md border-b border-[#2d1b1b]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <img src="assets/images/logo.png" alt="AFF Digital" class="h-10 w-auto group-hover:scale-105 transition-transform">
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-mono tracking-widest text-[#a69090] uppercase">
                <a href="#about" class="hover:text-[#eee6d8] transition-colors">Tentang Kami</a>
                <a href="#services" class="hover:text-[#eee6d8] transition-colors">Layanan</a>
                <a href="#portfolio" class="hover:text-[#eee6d8] transition-colors">Portofolio</a>
                <a href="#pricing" class="hover:text-[#eee6d8] transition-colors">Harga Paket</a>
                <a href="#reviews" class="hover:text-[#eee6d8] transition-colors">Testimoni</a>
                <a href="#faq" class="hover:text-[#eee6d8] transition-colors">FAQ</a>
            </nav>

            <!-- CTA Buttons (Consultation & Admin Login) -->
            <div class="flex items-center gap-3">
                <a href="admin/login.php" class="hidden sm:inline-flex py-2 px-4 rounded-full bg-[#1c1313] hover:bg-[#2d1b1b] text-[#a69090] hover:text-[#eee6d8] font-mono text-xs border border-[#2d1b1b] transition-all">
                    Login Admin
                </a>
                <a href="https://wa.me/6289612339608?text=Halo%20AFF%20Digital,%20saya%20ingin%20konsultasi%20pembuatan%20website/sistem" target="_blank" class="py-2.5 px-6 rounded-full bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-grotesk font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_25px_rgba(139,24,24,0.5)] hover:scale-105 flex items-center gap-2">
                    <i class="ph-bold ph-whatsapp-logo text-base"></i>
                    <span>Konsultasi</span>
                </a>
            </div>

        </div>
    </header>

    <!-- HERO SECTION (EDITORIAL DARK LUXURY MATCHING MOCKUP) -->
    <section id="about" class="relative py-16 sm:py-24 overflow-hidden border-b border-[#2d1b1b]">
        
        <!-- Background Glowing Red Sun Backdrop -->
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-[#8b1818]/25 blur-[120px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-12">
            
            <!-- Top Tagline Bar -->
            <div class="flex items-center justify-between text-xs font-mono text-[#a69090] border-b border-[#2d1b1b] pb-4">
                <span class="tracking-widest uppercase">CREATIVE SOFTWARE &amp; DIGITAL PORTFOLIO</span>
                <span class="text-[#e63946] flex items-center gap-1.5 font-bold">
                    <span class="w-2 h-2 rounded-full bg-[#e63946] animate-ping"></span>
                    BEROPERASI AKTIF &amp; SIAP MENERIMA PROYEK BARU
                </span>
            </div>

            <!-- GIANT EDITORIAL HEADLINE -->
            <div class="text-center space-y-2">
                <h1 class="font-bebas text-7xl sm:text-9xl md:text-[13rem] leading-none tracking-tight text-[#eee6d8] drop-shadow-2xl">
                    PORTFOLIO
                </h1>
                <p class="font-grotesk text-sm sm:text-base tracking-[0.3em] uppercase text-[#a69090] font-bold">
                    AFF DIGITAL • UI / UX / WEB / CUSTOM POS &amp; HR SYSTEMS
                </p>
            </div>

            <!-- 3-COLUMN HERO CONTENT (MATCHING MOCKUP LAYOUT) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center pt-6">
                
                <!-- Left Quote Card (3 cols) -->
                <div class="lg:col-span-3 space-y-4 p-6 bg-[#1c1313]/80 border border-[#2d1b1b] rounded-3xl backdrop-blur-md shadow-2xl">
                    <h2 class="font-bebas text-2xl text-[#eee6d8] tracking-wide">UI / UX / WEB / POS</h2>
                    <p class="text-xs text-[#a69090] font-mono leading-relaxed uppercase">
                        USER EXPERIENCE<br>USER INTERFACE<br>POS KASIR &amp; PORTAL HR
                    </p>
                    <blockquote class="text-xs font-serif italic text-[#eee6d8] pt-3 border-t border-[#2d1b1b]">
                        "Agensi digital profesional yang membantu UMKM &amp; Brand bertransformasi lewat website, sistem custom, dan konten visual."
                    </blockquote>
                </div>

                <!-- Center Portrait Image with Crimson Sun Glow (6 cols) -->
                <div class="lg:col-span-6 flex flex-col items-center justify-center relative my-4 lg:my-0">
                    <!-- Crimson Red Circular Backdrop -->
                    <div class="w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-gradient-to-tr from-[#8b1818] via-[#a81d1d] to-[#4a0d0d] absolute -z-10 shadow-[0_0_80px_rgba(139,24,24,0.7)] animate-pulse"></div>
                    
                    <img src="assets/images/orang.png" alt="Achmad Farhan Fahrezi" class="w-64 h-64 sm:w-80 sm:h-80 object-cover rounded-full border-4 border-[#eee6d8]/20 shadow-2xl z-10 hover:scale-105 transition-transform duration-500">
                    
                    <div class="mt-6 text-center z-10 space-y-1">
                        <h3 class="font-bebas text-3xl text-[#eee6d8] tracking-widest">Achmad Farhan Fahrezi</h3>
                        <span class="text-xs font-mono text-[#e63946] uppercase tracking-wider block">Digital Specialist</span>
                    </div>
                </div>

                <!-- Right Agency Profile & Stats Card (3 cols) -->
                <div class="lg:col-span-3 space-y-5 p-6 bg-[#1c1313]/80 border border-[#2d1b1b] rounded-3xl backdrop-blur-md shadow-2xl">
                    <div>
                        <h3 class="font-bebas text-2xl text-[#eee6d8]">ABOUT AFF DIGITAL</h3>
                        <p class="text-xs text-[#a69090] mt-1 font-sans leading-relaxed">
                            Kami adalah software house &amp; studio kreatif yang berfokus membangun website berkinerja tinggi, sistem custom (POS Kasir, Portal HR), dan media promosi bisnis.
                        </p>
                    </div>

                    <!-- Statistics Grid -->
                    <div class="grid grid-cols-3 gap-2 pt-3 border-t border-[#2d1b1b] text-center font-mono">
                        <div class="p-2 bg-[#120c0c] rounded-2xl border border-[#2d1b1b]">
                            <strong class="font-bebas text-2xl text-[#e63946] block">4+</strong>
                            <span class="text-[9px] text-[#a69090] uppercase block">YEARS EXP</span>
                        </div>
                        <div class="p-2 bg-[#120c0c] rounded-2xl border border-[#2d1b1b]">
                            <strong class="font-bebas text-2xl text-[#eee6d8] block">100+</strong>
                            <span class="text-[9px] text-[#a69090] uppercase block">PROJECTS</span>
                        </div>
                        <div class="p-2 bg-[#120c0c] rounded-2xl border border-[#2d1b1b]">
                            <strong class="font-bebas text-2xl text-[#e63946] block">99%</strong>
                            <span class="text-[9px] text-[#a69090] uppercase block">HAPPY CLIENTS</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION 1: WHAT WE DO (SERVICES GRID) -->
    <section id="services" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="flex items-center justify-between border-b border-[#2d1b1b] pb-4">
                <h2 class="font-bebas text-4xl text-[#eee6d8] tracking-wider flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-[#8b1818]"></span>
                    WHAT WE DO / LAYANAN KAMI
                </h2>
                <span class="text-xs font-mono text-[#a69090]">EXPERT DIGITAL SOLUTIONS</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5">
                
                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl space-y-4 hover:translate-y-[-4px] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-2xl group-hover:bg-[#8b1818] group-hover:text-white transition-all">
                        <i class="ph-bold ph-layout"></i>
                    </div>
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8]">UI / UX &amp; WEB DESIGN</h3>
                    <p class="text-xs text-[#a69090] leading-relaxed">Desain antarmuka website modern, responsif, dan konversi tinggi untuk brand Anda.</p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl space-y-4 hover:translate-y-[-4px] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-2xl group-hover:bg-[#8b1818] group-hover:text-white transition-all">
                        <i class="ph-bold ph-storefront"></i>
                    </div>
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8]">POS KASIR MULTI-TENANT</h3>
                    <p class="text-xs text-[#a69090] leading-relaxed">Sistem kasir toko/resto multi-unit bisnis, transaksi cepat, cetak struk &amp; laporan omset.</p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl space-y-4 hover:translate-y-[-4px] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-2xl group-hover:bg-[#8b1818] group-hover:text-white transition-all">
                        <i class="ph-bold ph-users-three"></i>
                    </div>
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8]">PORTAL KARYAWAN HR</h3>
                    <p class="text-xs text-[#a69090] leading-relaxed">Sistem presensi GPS &amp; kamera, pengajuan cuti, penggajian (payroll), &amp; slip gaji digital.</p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl space-y-4 hover:translate-y-[-4px] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-2xl group-hover:bg-[#8b1818] group-hover:text-white transition-all">
                        <i class="ph-bold ph-code"></i>
                    </div>
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8]">CUSTOM SYSTEM &amp; API</h3>
                    <p class="text-xs text-[#a69090] leading-relaxed">Pengembangan aplikasi custom skala perusahaan sesuai kebutuhan alur kerja bisnis.</p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl space-y-4 hover:translate-y-[-4px] transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-2xl group-hover:bg-[#8b1818] group-hover:text-white transition-all">
                        <i class="ph-bold ph-video-camera"></i>
                    </div>
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8]">FOTO &amp; VIDEO PROMOSI</h3>
                    <p class="text-xs text-[#a69090] leading-relaxed">Konten visual profesional untuk katalog produk, medsos, &amp; kampanye promosi digital.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 2: PORTOFOLIO DINAMIS DARI DATABASE -->
    <section id="portfolio" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#2d1b1b] pb-5">
                <div>
                    <span class="text-[10px] font-mono text-[#e63946] uppercase font-bold tracking-widest block mb-1">HASIL KARYA NYATA</span>
                    <h2 class="font-bebas text-5xl text-[#eee6d8] tracking-wider">PORTOFOLIO &amp; FEATURED WORK</h2>
                    <p class="text-xs text-[#a69090] mt-1 font-mono">Website, Sistem Custom, Foto &amp; Video Promosi yang sudah dikerjakan AFF Digital</p>
                </div>
                <a href="#contact" class="shrink-0 px-5 py-2.5 rounded-full bg-[#1c1313] hover:bg-[#2d1b1b] border border-[#2d1b1b] hover:border-[#8b1818] text-[#a69090] hover:text-[#eee6d8] text-xs font-mono transition-all">
                    Butuh Projek Serupa? →
                </a>
            </div>

            <!-- CATEGORY FILTER TABS -->
            <?php
            $allCategories = ['Semua'];
            foreach ($portfoliosList as $pf) {
                $cat = $pf['category_label'] ?? '';
                if ($cat && !in_array($cat, $allCategories)) $allCategories[] = $cat;
            }
            ?>
            <div class="flex flex-wrap gap-2" id="portfolioFilterBar">
                <?php foreach ($allCategories as $cat): ?>
                    <button onclick="filterPortfolio('<?= h($cat) ?>')"
                        class="portfolio-filter-btn px-4 py-1.5 rounded-full text-xs font-mono font-bold border transition-all
                        <?= $cat === 'Semua' ? 'bg-[#8b1818] border-[#8b1818] text-white' : 'bg-[#1c1313] border-[#2d1b1b] text-[#a69090] hover:border-[#8b1818] hover:text-[#eee6d8]' ?>"
                        data-filter="<?= h($cat) ?>">
                        <?= h($cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- PORTFOLIO GRID -->
            <?php if (!empty($portfoliosList)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="portfolioGrid">
                    <?php foreach ($portfoliosList as $pf):
                        $title    = $pf['title'] ?? 'Portofolio';
                        $catLabel = $pf['category_label'] ?? 'Lainnya';
                        $desc     = $pf['description'] ?? '';
                        $imgUrl   = $pf['media_url'] ?? '';
                        $projLink = $pf['project_link'] ?? '';

                        // Fallback images by category
                        if (!$imgUrl) {
                            $catImgMap = [
                                'Website'       => 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&auto=format&fit=crop&q=80',
                                'Sistem'        => 'https://images.unsplash.com/photo-1556742049-0a67daf64f42?w=600&auto=format&fit=crop&q=80',
                                'Foto Produk'   => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80',
                                'Video Promosi' => 'https://images.unsplash.com/photo-1492619375914-88005aa9e8fb?w=600&auto=format&fit=crop&q=80',
                                'Desain Grafis' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?w=600&auto=format&fit=crop&q=80',
                            ];
                            $imgUrl = $catImgMap[$catLabel] ?? 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&auto=format&fit=crop&q=80';
                        }

                        // Category color badge
                        $catColors = [
                            'Website'       => 'bg-blue-900/50 text-blue-300 border-blue-700/40',
                            'Sistem'        => 'bg-purple-900/50 text-purple-300 border-purple-700/40',
                            'Foto Produk'   => 'bg-amber-900/50 text-amber-300 border-amber-700/40',
                            'Video Promosi' => 'bg-rose-900/50 text-rose-300 border-rose-700/40',
                            'Desain Grafis' => 'bg-emerald-900/50 text-emerald-300 border-emerald-700/40',
                        ];
                        $badgeClass = $catColors[$catLabel] ?? 'bg-[#2d1b1b] text-[#a69090] border-[#2d1b1b]';
                    ?>
                    <div class="portfolio-card group bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl overflow-hidden transition-all hover:translate-y-[-4px] hover:shadow-[0_16px_40px_rgba(139,24,24,0.2)] duration-300"
                         data-category="<?= h($catLabel) ?>">

                        <!-- Image -->
                        <div class="relative aspect-video overflow-hidden bg-[#120c0c]">
                            <img src="<?= h($imgUrl) ?>" alt="<?= h($title) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 onerror="this.src='https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&auto=format&fit=crop&q=80'">

                            <!-- Category Badge Overlay -->
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[9px] font-mono font-black uppercase border <?= $badgeClass ?>">
                                <?= h($catLabel) ?>
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="p-5 space-y-3">
                            <h3 class="font-grotesk font-extrabold text-sm text-[#eee6d8] leading-snug group-hover:text-white transition-colors">
                                <?= h($title) ?>
                            </h3>

                            <?php if ($desc): ?>
                                <p class="text-xs text-[#a69090] leading-relaxed line-clamp-2"><?= h($desc) ?></p>
                            <?php endif; ?>

                            <?php if ($projLink): ?>
                                <a href="<?= h($projLink) ?>" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-xs font-mono text-[#e63946] hover:underline font-bold mt-1">
                                    <i class="ph-bold ph-arrow-up-right text-sm"></i>
                                    <span>Lihat Projek →</span>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Fallback jika DB kosong: tampilkan 2 featured work statis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                    <div class="group bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl overflow-hidden transition-all hover:translate-y-[-4px] duration-300">
                        <div class="relative aspect-video overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1556742049-0a67daf64f42?w=600&auto=format&fit=crop&q=80" alt="POS Kasir" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[9px] font-mono font-black uppercase bg-purple-900/60 text-purple-300 border border-purple-700/40">Sistem</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[9px] font-mono font-black uppercase bg-[#8b1818] text-white">LIVE DEMO</span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="font-grotesk font-extrabold text-sm text-[#eee6d8]">POS KASIR POINT OF SALE</h3>
                            <p class="text-xs text-[#a69090] line-clamp-2">Sistem kasir multi-unit bisnis, transaksi cepat, cetak struk &amp; laporan omset.</p>
                            <a href="https://affdigital.my.id/pos-kasir" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono text-[#e63946] hover:underline font-bold">
                                <i class="ph-bold ph-arrow-up-right text-sm"></i> Buka Demo →
                            </a>
                        </div>
                    </div>

                    <div class="group bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-3xl overflow-hidden transition-all hover:translate-y-[-4px] duration-300">
                        <div class="relative aspect-video overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&auto=format&fit=crop&q=80" alt="Portal Karyawan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[9px] font-mono font-black uppercase bg-purple-900/60 text-purple-300 border border-purple-700/40">Sistem</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[9px] font-mono font-black uppercase bg-[#8b1818] text-white">LIVE DEMO</span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="font-grotesk font-extrabold text-sm text-[#eee6d8]">PORTAL KARYAWAN HR DIGITAL</h3>
                            <p class="text-xs text-[#a69090] line-clamp-2">Presensi GPS, slip gaji digital, pengajuan cuti &amp; approval admin.</p>
                            <a href="https://affdigital.my.id/portal-karyawan" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-mono text-[#e63946] hover:underline font-bold">
                                <i class="ph-bold ph-arrow-up-right text-sm"></i> Buka Demo →
                            </a>
                        </div>
                    </div>


                </div>
            <?php endif; ?>

            <!-- MY PROCESS - dipindah ke bawah grid portofolio, horizontal -->
            <div id="process" class="mt-6 border-t border-[#2d1b1b] pt-8">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bebas text-3xl text-[#eee6d8] tracking-wider">MY PROCESS / ALUR KERJA</h3>
                    <span class="text-xs font-mono text-[#a69090]">5 TAHAPAN PENGEMBANGAN PROJEK</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <?php foreach ([
                        ['01','DISCOVER','Memahami tujuan bisnis, audiens, & kebutuhan spesifikasi.'],
                        ['02','DEFINE','Riset, wireframing, & merancang arsitektur sistem.'],
                        ['03','DESIGN','Merancang antarmuka visual modern & ramah pengguna.'],
                        ['04','DEVELOP','Pengodean sistem berkinerja tinggi, aman, & terintegrasi.'],
                        ['05','DELIVER','Pengujian ketat, penyempurnaan, & peluncuran projek.'],
                    ] as [$num, $step, $desc]): ?>
                    <div class="p-4 bg-[#1c1313] border border-[#2d1b1b] rounded-2xl hover:border-[#8b1818] transition-colors space-y-1.5">
                        <span class="font-bebas text-3xl text-[#e63946] block leading-none"><?= $num ?></span>
                        <h4 class="font-grotesk font-extrabold text-xs text-[#eee6d8]"><?= $step ?></h4>
                        <p class="text-[10px] text-[#a69090] leading-relaxed"><?= $desc ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </section>

    <script>
    function filterPortfolio(cat) {
        document.querySelectorAll('.portfolio-filter-btn').forEach(btn => {
            const isActive = btn.dataset.filter === cat;
            btn.classList.toggle('bg-[#8b1818]', isActive);
            btn.classList.toggle('border-[#8b1818]', isActive);
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('bg-[#1c1313]', !isActive);
            btn.classList.toggle('border-[#2d1b1b]', !isActive);
            btn.classList.toggle('text-[#a69090]', !isActive);
        });
        document.querySelectorAll('.portfolio-card').forEach(card => {
            const show = cat === 'Semua' || card.dataset.category === cat;
            card.style.display = show ? '' : 'none';
        });
    }
    </script>



    <!-- SECTION 3: PACKAGES & PRICING WITH DYNAMIC PAYMENT GATEWAY CHECKOUT MODAL -->
    <section id="pricing" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="px-3 py-1 rounded-full bg-[#8b1818]/20 text-[#e63946] text-xs font-mono font-bold border border-[#8b1818]/40">
                    TRANSPARENT PRICING &amp; PAYMENT GATEWAY INTEGRATED
                </span>
                <h2 class="font-bebas text-5xl text-[#eee6d8] tracking-wider">PAKET HARGA &amp; LAYANAN AFF DIGITAL</h2>
                <p class="text-xs text-[#a69090] font-sans">Pembayaran otomatis &amp; aman via <strong>Midtrans Snap</strong> dan <strong>iPaymu</strong> (Transfer Bank, QRIS, E-Wallet, Kartu Kredit).</p>
            </div>

            <!-- PACKAGES GRID (DATABASE / FALLBACK) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php 
                $all_packages_flat = [];
                foreach ($packagesByCategory as $catName => $pkgList) {
                    foreach ($pkgList as $p) {
                        $all_packages_flat[] = $p;
                    }
                }

                foreach ($all_packages_flat as $idx => $pkg):
                    $isFeatured = ($idx === 1);
                    $pkg_id = $pkg['id'] ?? ($idx + 1);
                    $pkg_name = $pkg['name'] ?? 'Paket Layanan';
                    $pkg_price_raw = $pkg['price'] ?? 1500000;
                    $pkg_price_formatted = is_numeric($pkg_price_raw) ? 'Rp ' . number_format($pkg_price_raw, 0, ',', '.') : $pkg_price_raw;
                    $pkg_desc = $pkg['description'] ?? 'Layanan profesional buatan AFF Digital';
                    
                    $features_list = [];
                    if (isset($pkg['features'])) {
                        if (is_array($pkg['features'])) {
                            $features_list = $pkg['features'];
                        } else {
                            $features_list = array_filter(explode("\n", $pkg['features']));
                        }
                    }
                ?>
                    <div class="p-8 bg-[#1c1313] border <?= $isFeatured ? 'border-[#e63946] shadow-[0_0_35px_rgba(139,24,24,0.4)] scale-105' : 'border-[#2d1b1b]' ?> rounded-3xl space-y-6 flex flex-col justify-between relative">
                        <?php if ($isFeatured): ?>
                            <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-[#e63946] text-white text-[10px] font-mono font-black uppercase tracking-wider shadow-md">
                                PALING POPULER
                            </span>
                        <?php endif; ?>

                        <div class="space-y-4">
                            <div>
                                <h3 class="font-bebas text-2xl text-[#eee6d8]"><?= h($pkg_name) ?></h3>
                                <p class="text-xs text-[#a69090] mt-1 leading-relaxed"><?= h($pkg_desc) ?></p>
                            </div>

                            <div class="py-3 border-y border-[#2d1b1b]">
                                <span class="text-[10px] text-[#a69090] font-mono uppercase block">Investasi Mulai</span>
                                <strong class="font-bebas text-4xl text-[#eee6d8] text-white block mt-0.5"><?= h($pkg_price_formatted) ?></strong>
                            </div>

                            <ul class="space-y-2.5 text-xs text-[#a69090]">
                                <?php foreach ($features_list as $feat): ?>
                                    <li class="flex items-center gap-2">
                                        <i class="ph-bold ph-check-circle text-[#e63946] text-base shrink-0"></i>
                                        <span><?= h(trim($feat)) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Direct Checkout & Payment Gateway Trigger Button -->
                        <div class="space-y-2 pt-2">
                            <a href="checkout.php?package=<?= $pkg_id ?>" class="w-full py-3.5 px-4 rounded-2xl bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-grotesk font-bold text-xs uppercase tracking-wider transition-all text-center block shadow-[0_0_20px_rgba(139,24,24,0.4)] hover:scale-[1.02] flex items-center justify-center gap-2">
                                <i class="ph-bold ph-credit-card text-base"></i>
                                <span>Pesan &amp; Bayar Langsung &rarr;</span>
                            </a>
                            
                            <button type="button" onclick="openDirectPaymentModal('<?= h($pkg_id) ?>', '<?= h(addslashes($pkg_name)) ?>', '<?= h(addslashes($pkg_price_formatted)) ?>')" class="w-full py-2 px-3 rounded-xl bg-[#120c0c] hover:bg-[#2d1b1b] text-[#a69090] text-[11px] font-mono border border-[#2d1b1b] transition-all">
                                Pilih Payment Gateway (Midtrans / iPaymu)
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- SECTION 4: TESTIMONIALS & REVIEWS -->
    <section id="reviews" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="flex items-center justify-between border-b border-[#2d1b1b] pb-4">
                <div>
                    <h2 class="font-bebas text-4xl text-[#eee6d8] tracking-wider">TESTIMONI &amp; ULASAN CLIENT</h2>
                    <p class="text-xs text-[#a69090] font-mono">APA KATA MEREKA TENTANG LAYANAN AFF DIGITAL</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-4">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                    </div>
                    <blockquote class="text-xs text-[#eee6d8] font-sans leading-relaxed italic">
                        "Sistem Kasir POS buatan AFF Digital sangat membantu operasional cafe kami. Transaksi cepat, laporan omset otomatis, dan tampilan sangat modern!"
                    </blockquote>
                    <div class="pt-3 border-t border-[#2d1b1b] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#8b1818] text-white font-bold flex items-center justify-center text-xs">B</div>
                        <div>
                            <strong class="text-xs font-bold text-[#eee6d8] block">Budi Santoso</strong>
                            <span class="text-[10px] text-[#a69090]">Owner AFF Coffee &amp; Bakery</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-4">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                    </div>
                    <blockquote class="text-xs text-[#eee6d8] font-sans leading-relaxed italic">
                        "Portal Karyawan &amp; Absensi GPS bekerja sempurna untuk tim lapangan kami. Tidak ada manipulasi titik lokasi lagi. Sangat direkomendasikan!"
                    </blockquote>
                    <div class="pt-3 border-t border-[#2d1b1b] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#8b1818] text-white font-bold flex items-center justify-center text-xs">S</div>
                        <div>
                            <strong class="text-xs font-bold text-[#eee6d8] block">Siti Rahmawati</strong>
                            <span class="text-[10px] text-[#a69090]">Admin HR Perusahaan</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-4">
                    <div class="flex items-center gap-1 text-amber-400">
                        <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                    </div>
                    <blockquote class="text-xs text-[#eee6d8] font-sans leading-relaxed italic">
                        "Pengerjaan tepat waktu, purna jual ramah, dan komunikasi sangat cepat via WhatsApp. Sukses selalu untuk AFF Digital!"
                    </blockquote>
                    <div class="pt-3 border-t border-[#2d1b1b] flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#8b1818] text-white font-bold flex items-center justify-center text-xs">E</div>
                        <div>
                            <strong class="text-xs font-bold text-[#eee6d8] block">Hj. Endang</strong>
                            <span class="text-[10px] text-[#a69090]">Owner Sinar Jaya Mart</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- SECTION 5: FAQ SECTION -->
    <section id="faq" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <div class="text-center space-y-2">
                <span class="px-3 py-1 rounded-full bg-[#8b1818]/20 text-[#e63946] text-xs font-mono font-bold border border-[#8b1818]/40">
                    PUSAT BANTUAN &amp; FAQ
                </span>
                <h2 class="font-bebas text-5xl text-[#eee6d8] tracking-wider">PERTANYAAN UMUM (FAQ)</h2>
                <p class="text-xs text-[#a69090]">Jawaban atas pertanyaan yang paling sering diajukan seputar pengerjaan proyek, sistem, &amp; pembayaran.</p>
            </div>

            <div class="space-y-4">
                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-2">
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8] flex items-center gap-2">
                        <span class="text-[#e63946]">Q.</span> Berapa lama proses pengerjaan pembuatan website / sistem?
                    </h3>
                    <p class="text-xs text-[#a69090] leading-relaxed pl-6">
                        Rata-rata pengerjaan paket Basic &amp; Pro adalah 3 hingga 7 hari kerja setelah seluruh materi (konten/gambar) lengkap. Untuk sistem custom (POS/HR/Gudang), durasi menyesuaikan kompleksitas alur bisnis.
                    </p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-2">
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8] flex items-center gap-2">
                        <span class="text-[#e63946]">Q.</span> Metode pembayaran apa saja yang didukung?
                    </h3>
                    <p class="text-xs text-[#a69090] leading-relaxed pl-6">
                        Pembayaran diproses otomatis &amp; aman via Payment Gateway <strong>Midtrans</strong> dan <strong>iPaymu</strong>. Mendukung Transfer Bank (BCA, Mandiri, BRI, BNI), QRIS, E-Wallet (GoPay, ShopeePay), Kartu Kredit/Debit, dan gerai peritel.
                    </p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-2">
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8] flex items-center gap-2">
                        <span class="text-[#e63946]">Q.</span> Apakah saya mendapatkan garansi setelah website / sistem selesai?
                    </h3>
                    <p class="text-xs text-[#a69090] leading-relaxed pl-6">
                        Ya! Setiap pemesanan website mendapatkan pendampingan &amp; garansi pemeliharaan (30 hingga 60 hari) untuk perbaikan kendala teknis atau perbaikan bug secara gratis.
                    </p>
                </div>

                <div class="p-6 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl space-y-2">
                    <h3 class="font-grotesk font-bold text-base text-[#eee6d8] flex items-center gap-2">
                        <span class="text-[#e63946]">Q.</span> Bagaimana jika saya butuh sistem custom khusus untuk bisnis saya?
                    </h3>
                    <p class="text-xs text-[#a69090] leading-relaxed pl-6">
                        Anda dapat berkonsultasi gratis dengan tim kami via form kontak atau WhatsApp. Kami dapat membangun sistem Kasir/POS, Absensi HR, Manajemen Stok Gudang, Portal Sekolah, atau sistem internal khusus sesuai kebutuhan Anda.
                    </p>
                </div>
            </div>

            <div class="text-center pt-2">
                <a href="faq.php" class="inline-flex items-center gap-2 text-xs font-mono text-[#e63946] hover:underline font-bold">
                    <span>Lihat Halaman FAQ Lengkap &rarr;</span>
                </a>
            </div>

        </div>
    </section>

    <!-- SECTION 6: FORMULIR PESAN & DISKUSI -->
    <section id="contact" class="py-20 border-b border-[#2d1b1b] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                
                <!-- LEFT CONTACT INFO BADGES -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="space-y-2">
                        <span class="px-3 py-1 rounded-full bg-[#8b1818]/20 text-[#e63946] text-xs font-mono font-bold border border-[#8b1818]/40">
                            HUBUNGI KAMI
                        </span>
                        <h2 class="font-bebas text-4xl text-[#eee6d8] tracking-wider">MARI KONSULTASIKAN PROYEK DIGITAL ANDA</h2>
                        <p class="text-xs text-[#a69090] font-sans leading-relaxed">
                            Tim ahli kami siap membantu memilihkan solusi terbaik sesuai anggaran dan tujuan bisnis Anda.
                        </p>
                    </div>

                    <div class="space-y-3 font-mono text-xs">
                        
                        <!-- WhatsApp Card -->
                        <a href="https://wa.me/6289612339608" target="_blank" class="p-4 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-2xl flex items-center gap-4 transition-all group">
                            <div class="w-12 h-12 rounded-xl bg-emerald-950/60 text-emerald-400 border border-emerald-800 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-whatsapp-logo"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#a69090] font-bold uppercase block">WHATSAPP RESPON CEPAT</span>
                                <strong class="text-sm font-extrabold text-[#eee6d8]">+62 896-1233-9608</strong>
                            </div>
                        </a>

                        <!-- Email Konsultasi -->
                        <a href="mailto:owner@affdigital.my.id" class="p-4 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-2xl flex items-center gap-4 transition-all group">
                            <div class="w-12 h-12 rounded-xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-envelope"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#a69090] font-bold uppercase block">EMAIL KONSULTASI</span>
                                <strong class="text-xs font-extrabold text-[#eee6d8] truncate block">owner@affdigital.my.id</strong>
                            </div>
                        </a>

                        <!-- Email Alternatif -->
                        <a href="mailto:afarhanfahrezi@gmail.com" class="p-4 bg-[#1c1313] border border-[#2d1b1b] hover:border-[#8b1818] rounded-2xl flex items-center gap-4 transition-all group">
                            <div class="w-12 h-12 rounded-xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-envelope-simple"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#a69090] font-bold uppercase block">EMAIL ALTERNATIF</span>
                                <strong class="text-xs font-extrabold text-[#eee6d8] truncate block">afarhanfahrezi@gmail.com</strong>
                            </div>
                        </a>

                        <!-- Alamat Kantor -->
                        <div class="p-4 bg-[#1c1313] border border-[#2d1b1b] rounded-2xl flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-[#8b1818]/20 text-[#e63946] border border-[#8b1818]/40 flex items-center justify-center text-xl shrink-0">
                                <i class="ph-bold ph-map-pin"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-[#a69090] font-bold uppercase block">ALAMAT KANTOR</span>
                                <strong class="text-xs font-bold text-[#eee6d8] leading-relaxed block">
                                    Jl. Kalianyar Sidomukti 18, Kota Surabaya, Jawa Timur 60161, Indonesia
                                </strong>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT FORMULIR PESAN & DISKUSI -->
                <div class="lg:col-span-7 bg-[#1c1313] border border-[#2d1b1b] rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
                    <div>
                        <h3 class="font-bebas text-3xl text-[#eee6d8]">FORMULIR PESAN &amp; DISKUSI</h3>
                        <p class="text-xs text-[#a69090] mt-1 font-sans">
                            Isi formulir di bawah dan pesan Anda langsung dikirim ke WhatsApp kami. <strong class="text-[#e63946]">Respon cepat!</strong>
                        </p>
                    </div>

                    <form action="process_message.php" method="POST" class="space-y-4 font-mono text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Email / Nomor WhatsApp Anda</label>
                            <input type="text" name="contact" required placeholder="081234567890 atau email@domain.com" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Jenis Layanan yang Dibutuhkan</label>
                            <select name="service" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] focus:outline-none focus:border-[#8b1818]">
                                <option value="Website / Landing Page">Website / Landing Page</option>
                                <option value="Sistem Kasir POS Multi-Company">Sistem Kasir POS Multi-Company</option>
                                <option value="Portal Karyawan & Presensi GPS">Portal Karyawan &amp; Presensi GPS</option>
                                <option value="Custom Software & System">Custom Software &amp; System</option>
                                <option value="Foto & Video Promosi">Foto &amp; Video Promosi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Pesan / Detail Kebutuhan</label>
                            <textarea name="message" rows="4" required placeholder="Jelaskan kebutuhan, target, atau anggaran proyek Anda..." class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] placeholder-[#594848] focus:outline-none focus:border-[#8b1818]"></textarea>
                        </div>

                        <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-grotesk font-bold text-xs uppercase tracking-wider transition-all shadow-[0_0_25px_rgba(139,24,24,0.5)] flex items-center justify-center gap-2 hover:scale-[1.01]">
                            <i class="ph-bold ph-whatsapp-logo text-lg"></i>
                            <span>Kirim ke WhatsApp Sekarang &rarr;</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </section>

    <!-- FULL FOOTER -->
    <footer class="bg-[#0b0707] text-[#a69090] pt-16 pb-8 border-t border-[#2d1b1b] text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- BRAND INFO -->
                <div class="space-y-4">
                    <a href="index.php" class="inline-block group">
                        <img src="assets/images/logo.png" alt="AFF Digital" class="h-10 w-auto group-hover:scale-105 transition-transform">
                    </a>
                    <p class="text-xs text-[#a69090] leading-relaxed">
                        Agensi digital profesional yang membantu UMKM &amp; Brand bertransformasi lewat website, sistem custom, dan konten visual.
                    </p>
                    <div class="flex items-center gap-3 text-lg">
                        <a href="https://wa.me/6289612339608" target="_blank" class="w-9 h-9 rounded-xl bg-[#1c1313] border border-[#2d1b1b] flex items-center justify-center text-[#eee6d8] hover:border-[#8b1818] transition-colors">
                            <i class="ph-bold ph-whatsapp-logo"></i>
                        </a>
                        <a href="mailto:owner@affdigital.my.id" class="w-9 h-9 rounded-xl bg-[#1c1313] border border-[#2d1b1b] flex items-center justify-center text-[#eee6d8] hover:border-[#8b1818] transition-colors">
                            <i class="ph-bold ph-envelope"></i>
                        </a>
                    </div>
                </div>

                <!-- NAVIGASI -->
                <div class="space-y-3 font-mono">
                    <h4 class="font-grotesk font-extrabold text-xs text-[#eee6d8] uppercase tracking-wider text-[#e63946]">NAVIGASI</h4>
                    <ul class="space-y-2">
                        <li><a href="#about" class="hover:text-[#eee6d8] transition-colors">Tentang Kami</a></li>
                        <li><a href="#services" class="hover:text-[#eee6d8] transition-colors">Layanan</a></li>
                        <li><a href="#portfolio" class="hover:text-[#eee6d8] transition-colors">Portofolio</a></li>
                        <li><a href="#pricing" class="hover:text-[#eee6d8] transition-colors">Harga Paket</a></li>
                        <li><a href="#reviews" class="hover:text-[#eee6d8] transition-colors">Testimoni</a></li>
                    </ul>
                </div>

                <!-- LEGALITAS & KEBIJAKAN -->
                <div class="space-y-3 font-mono">
                    <h4 class="font-grotesk font-extrabold text-xs text-[#eee6d8] uppercase tracking-wider text-[#e63946]">LEGALITAS &amp; KEBIJAKAN</h4>
                    <ul class="space-y-2">
                        <li><a href="syarat-ketentuan.php" class="hover:text-[#eee6d8] transition-colors">Syarat &amp; Ketentuan</a></li>
                        <li><a href="refund-policy.php" class="hover:text-[#eee6d8] transition-colors">Kebijakan Pengembalian Dana</a></li>
                        <li><a href="faq.php" class="hover:text-[#eee6d8] transition-colors">Pertanyaan Umum (FAQ)</a></li>
                        <li><a href="admin/login.php" class="hover:text-[#eee6d8] transition-colors">Login Admin</a></li>
                    </ul>
                </div>

                <!-- HUBUNGI KAMI -->
                <div class="space-y-3 font-mono">
                    <h4 class="font-grotesk font-extrabold text-xs text-[#eee6d8] uppercase tracking-wider text-[#e63946]">HUBUNGI KAMI</h4>
                    <ul class="space-y-2 text-xs">
                        <li class="flex items-center gap-2">
                            <i class="ph-bold ph-whatsapp-logo text-[#e63946]"></i>
                            <span>+62 896-1233-9608</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="ph-bold ph-envelope text-[#e63946]"></i>
                            <span>owner@affdigital.my.id</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="ph-bold ph-envelope-simple text-[#e63946]"></i>
                            <span>afarhanfahrezi@gmail.com</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="ph-bold ph-globe text-[#e63946]"></i>
                            <span>affdigital.my.id</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="ph-bold ph-map-pin text-[#e63946] shrink-0 mt-0.5"></i>
                            <span>Jl. Kalianyar Sidomukti 18, Kota Surabaya, Jawa Timur 60161, Indonesia</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- BOTTOM COPYRIGHT BAR -->
            <div class="pt-8 border-t border-[#2d1b1b] flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs">
                <p>&copy; <?= date('Y') ?> Aff Digital. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-2 text-emerald-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Beroperasi aktif &amp; siap menerima proyek baru</span>
                </div>
            </div>

        </div>
    </footer>

    <!-- INTERACTIVE DIRECT PAYMENT CHECKOUT MODAL (MIDTRANS / IPAYMU / WHATSAPP) -->
    <div id="paymentModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="bg-[#1c1313] border border-[#2d1b1b] rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 my-8 text-[#eee6d8] font-sans">
            
            <div class="flex items-center justify-between border-b border-[#2d1b1b] pb-4">
                <div>
                    <span class="text-[10px] font-mono text-[#e63946] uppercase font-bold">PEMBAYARAN OTOMATIS &amp; AMAN</span>
                    <h3 class="font-bebas text-2xl text-[#eee6d8] mt-0.5" id="modalPackageTitle">Form Pemesanan &amp; Payment Gateway</h3>
                </div>
                <button type="button" onclick="closePaymentModal()" class="text-[#a69090] hover:text-[#eee6d8] p-1"><i class="ph-bold ph-x text-2xl"></i></button>
            </div>

            <form action="checkout.php" method="POST" class="space-y-4 font-mono text-xs">
                <input type="hidden" name="package" id="modalPackageId" value="1">
                
                <div class="p-4 bg-[#120c0c] rounded-2xl border border-[#2d1b1b] flex justify-between items-center">
                    <div>
                        <span class="text-[10px] text-[#a69090] block">Paket Dipilih:</span>
                        <strong class="text-sm font-extrabold text-[#eee6d8]" id="modalPackageName">Paket Basic Website</strong>
                    </div>
                    <strong class="font-bebas text-2xl text-[#e63946]" id="modalPackagePrice">Rp 1.500.000</strong>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] focus:outline-none focus:border-[#8b1818]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Email</label>
                        <input type="email" name="email" required placeholder="budi@domain.com" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] focus:outline-none focus:border-[#8b1818]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">No. WhatsApp / HP</label>
                        <input type="text" name="phone" required placeholder="081234567890" class="w-full px-4 py-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] focus:outline-none focus:border-[#8b1818]">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Pilih Payment Gateway</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 bg-[#120c0c] border border-[#8b1818] rounded-2xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gateway" value="midtrans" checked class="accent-[#e63946]">
                            <div>
                                <strong class="text-xs text-[#eee6d8] block">Midtrans Snap</strong>
                                <span class="text-[9px] text-[#a69090]">BCA, Mandiri, QRIS</span>
                            </div>
                        </label>
                        <label class="p-3 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="gateway" value="ipaymu" class="accent-[#e63946]">
                            <div>
                                <strong class="text-xs text-[#eee6d8] block">iPaymu Gateway</strong>
                                <span class="text-[9px] text-[#a69090]">VA, E-Wallet, Retail</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#a69090] uppercase mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Detail khusus atau pertanyaan untuk projek Anda..." class="w-full px-4 py-2.5 bg-[#120c0c] border border-[#2d1b1b] rounded-2xl text-[#eee6d8] focus:outline-none focus:border-[#8b1818]"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-[#2d1b1b]">
                    <button type="button" onclick="closePaymentModal()" class="px-5 py-2.5 rounded-2xl bg-[#120c0c] text-[#a69090] text-xs font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-[#8b1818] hover:bg-[#a81d1d] text-[#eee6d8] font-grotesk font-bold text-xs uppercase tracking-wider shadow-md">
                        Lanjut Ke Pembayaran &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDirectPaymentModal(pkgId, pkgName, pkgPrice) {
            document.getElementById('modalPackageId').value = pkgId;
            document.getElementById('modalPackageName').innerText = pkgName;
            document.getElementById('modalPackagePrice').innerText = pkgPrice;
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
    </script>
</body>
</html>