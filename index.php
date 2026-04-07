<?php
/**
 * WMSU ARL Hub: Masterpiece Landing Page
 * Institutional Excellence — Immersive UI with Advanced Animations
 */
require_once 'includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM materials WHERE status = 'approved' ORDER BY downloads_count DESC LIMIT 3");
    $featured = $stmt->fetchAll();
    $totalMaterials = $pdo->query("SELECT COUNT(*) FROM materials WHERE status='approved'")->fetchColumn();
    $totalUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalDownloads = $pdo->query("SELECT SUM(downloads_count) FROM materials")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $featured = []; 
    $totalMaterials = '5,000+'; $totalUsers = '1,200+'; $totalDownloads = '15,000+';
}
?>

<style>
    /* ── Base Aesthetics & Variables ── */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.5);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        --red-glow: rgba(184, 28, 46, 0.4);
    }
    
    body {
        margin: 0;
        background-color: #fafafa;
        overflow-x: hidden;
    }

    /* ── Animated Background ── */
    .hero-wrapper {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #fdfdfd 0%, #f4f4f5 100%);
        padding: 160px 32px 120px 32px;
        text-align: center;
        border-bottom: 1px solid var(--border-light);
    }

    .bg-grid {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: linear-gradient(to right, rgba(0,0,0,0.03) 1px, transparent 1px),
                          linear-gradient(to bottom, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        z-index: 0;
        mask-image: radial-gradient(circle at center, black, transparent 80%);
        -webkit-mask-image: radial-gradient(circle at center, black, transparent 80%);
    }

    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        z-index: 0;
        animation: floatOrb 10s ease-in-out infinite alternate;
    }

    .orb-1 {
        width: 400px; height: 400px;
        background: var(--red-glow);
        top: -100px; left: -100px;
    }

    .orb-2 {
        width: 300px; height: 300px;
        background: rgba(204, 167, 47, 0.3); /* Gold */
        bottom: -50px; right: 10%;
        animation-delay: -5s;
    }

    @keyframes floatOrb {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(30px, 50px) scale(1.1); }
    }

    /* ── Hero Content ── */
    .hero-content {
        position: relative;
        z-index: 10;
        max-width: 900px;
        margin: 0 auto;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(184, 28, 46, 0.1);
        color: var(--wmsu-red);
        border: 1px solid rgba(184, 28, 46, 0.2);
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 32px;
        backdrop-filter: blur(4px);
        transform: translateY(20px);
        opacity: 0;
        animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.2s;
    }

    .hero-h1 {
        font-size: 72px;
        color: var(--wmsu-black);
        margin-bottom: 24px;
        font-weight: 900;
        letter-spacing: -0.04em;
        line-height: 1.1;
        transform: translateY(20px);
        opacity: 0;
        animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.4s;
    }
    
    .hero-h1 span.highlight {
        background: linear-gradient(135deg, var(--wmsu-red) 0%, #8a1523 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
    }

    .hero-p {
        font-size: 18px;
        color: var(--text-secondary);
        max-width: 640px;
        margin: 0 auto 48px auto;
        line-height: 1.7;
        transform: translateY(20px);
        opacity: 0;
        animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.6s;
    }

    .btn-group {
        display: flex;
        justify-content: center;
        gap: 16px;
        transform: translateY(20px);
        opacity: 0;
        animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.8s;
    }

    .btn-primary {
        background: var(--wmsu-red);
        color: white;
        box-shadow: 0 4px 14px 0 rgba(184, 28, 46, 0.39);
        transition: all 0.3s ease;
        padding: 14px 36px;
        border-radius: var(--radius-8);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(184, 28, 46, 0.23);
        background: #9a1827;
        color: white;
    }

    .btn-outline {
        background: white;
        color: var(--text-primary);
        border: 1px solid var(--border-light);
        transition: all 0.3s ease;
        padding: 14px 36px;
        border-radius: var(--radius-8);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .btn-outline:hover {
        border-color: var(--wmsu-red);
        color: var(--wmsu-red);
        transform: translateY(-2px);
    }

    @keyframes fadeUp {
        100% { transform: translateY(0); opacity: 1; }
    }

    /* ── Trust Marquee ── */
    .trust-strip {
        background: white;
        padding: 32px 0;
        border-bottom: 1px solid var(--border-light);
        overflow: hidden;
        display: flex;
        align-items: center;
    }

    .trust-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin: 0 32px;
        flex-shrink: 0;
    }

    .marquee-container {
        display: flex;
        flex: 1;
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    }

    .marquee-content {
        display: flex;
        align-items: center;
        gap: 48px;
        animation: scroll 30s linear infinite;
        padding-right: 48px;
    }

    .trust-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-primary);
        opacity: 0.7;
        white-space: nowrap;
        transition: opacity 0.3s;
    }
    .trust-badge:hover { opacity: 1; color: var(--wmsu-red); }

    @keyframes scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }

    /* ── Bento Features ── */
    .bento-section {
        padding: 120px 32px;
        background: #fdfdfd;
        max-width: 1280px;
        margin: 0 auto;
    }

    .section-title {
        text-align: center;
        font-size: 40px;
        font-weight: 900;
        color: var(--wmsu-black);
        margin-bottom: 64px;
        letter-spacing: -0.02em;
    }

    .bento-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: auto;
        gap: 24px;
    }

    .bento-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 40px;
        box-shadow: var(--glass-shadow);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: transform 0.1s;
        transform-style: preserve-3d;
        perspective: 1000px;
        position: relative;
        overflow: hidden;
    }
    
    .bento-card.large {
        grid-column: span 2;
    }

    .card-glare {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(105deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0) 40%);
        pointer-events: none;
        z-index: 2;
        transition: opacity 0.3s ease;
        opacity: 0;
    }

    .bento-icon {
        width: 56px; height: 56px;
        background: var(--wmsu-red-light);
        color: var(--wmsu-red);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        transform: translateZ(30px);
    }
    .bento-icon .material-symbols-outlined { font-size: 28px; }

    .bento-title {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 12px;
        color: var(--wmsu-black);
        transform: translateZ(20px);
    }

    .bento-desc {
        font-size: 15px;
        color: var(--text-secondary);
        line-height: 1.6;
        transform: translateZ(10px);
    }

    /* ── Live Stats Segment ── */
    .stats-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px dashed var(--border-light);
        transform: translateZ(10px);
    }
    .stat-block { text-align: left; }
    .stat-num { font-size: 32px; font-weight: 900; color: var(--wmsu-red); display: block; line-height: 1; margin-bottom: 4px; }
    .stat-lbl { font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }

    /* ── Scroll Reveal Class ── */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    @media (max-width: 900px) {
        .hero-h1 { font-size: 48px; }
        .bento-grid { grid-template-columns: 1fr; }
        .bento-card.large { grid-column: span 1; }
        .hero-wrapper { padding: 120px 20px 80px 20px; }
    }
</style>

<main>
    <!-- Beautiful Hero -->
    <section class="hero-wrapper">
        <div class="bg-grid"></div>
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>
        
        <div class="hero-content">
            <div class="badge">
                <span class="material-symbols-outlined" style="font-size: 16px;">verified</span>
                Official Academic Repository
            </div>
            <h1 class="hero-h1">The Future of <span class="highlight">Academic</span> Exchange</h1>
            <p class="hero-p">Experience an ultra-modern, faculty-verified database for all your course materials. Discover notes, research papers, and lectures instantly.</p>
            
            <div class="btn-group">
                <a href="<?php echo BASE_URL; ?>core/browse.php" class="btn-primary">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    Start Exploring
                </a>
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn-outline">
                    <span class="material-symbols-outlined">person_add</span>
                    Create Account
                </a>
            </div>
        </div>
    </section>

    <!-- Trust Marquee -->
    <section class="trust-strip">
        <div class="trust-label">Trusted Across Colleges</div>
        <div class="marquee-container">
            <div class="marquee-content" id="marquee-content">
                <!-- Content generated via JS for duplication -->
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bento-section">
        <h2 class="section-title reveal">Built for Institutional Excellence</h2>
        
        <div class="bento-grid">
            <!-- Large Card with Stats -->
            <div class="bento-card large tilt-card reveal">
                <div class="card-glare"></div>
                <div class="bento-icon"><span class="material-symbols-outlined">query_stats</span></div>
                <h3 class="bento-title">A Growing Ecosystem</h3>
                <p class="bento-desc">Join thousands of students and educators utilizing a shared knowledge base to elevate the standard of learning.</p>
                <div class="stats-container">
                    <div class="stat-block">
                        <span class="stat-num counter" data-target="<?php echo (int)str_replace(['+',' '], '', $totalMaterials); ?>">0</span>
                        <span class="stat-lbl">Resources</span>
                    </div>
                    <div class="stat-block">
                        <span class="stat-num counter" data-target="<?php echo (int)str_replace(['+',' '], '', $totalUsers); ?>">0</span>
                        <span class="stat-lbl">Active Users</span>
                    </div>
                    <div class="stat-block">
                        <span class="stat-num counter" data-target="<?php echo (int)str_replace(['+',' '], '', $totalDownloads); ?>">0</span>
                        <span class="stat-lbl">Downloads</span>
                    </div>
                </div>
            </div>

            <!-- Small Card 1 -->
            <div class="bento-card tilt-card reveal" style="transition-delay: 0.1s;">
                <div class="card-glare"></div>
                <div style="position: absolute; top: 20px; right: 20px; background: rgba(184, 28, 46, 0.1); color: var(--wmsu-red); font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 100px; text-transform: uppercase; transform: translateZ(20px);">Top Tier Security</div>
                <div class="bento-icon"><span class="material-symbols-outlined">shield_locked</span></div>
                <h3 class="bento-title">Secure & Verified</h3>
                <p class="bento-desc">Authenticated solely through official @wmsu.edu.ph institutional emails. Content is rigorously filtered and verified.</p>
            </div>

            <!-- Small Card 2 -->
            <div class="bento-card tilt-card reveal" style="transition-delay: 0.2s;">
                <div class="card-glare"></div>
                <div class="bento-icon"><span class="material-symbols-outlined">view_cozy</span></div>
                <h3 class="bento-title">Smart Categorization</h3>
                <p class="bento-desc">Locate exactly what you need with an intuitive taxonomy system branching across departments and specific courses.</p>
            </div>
            
            <!-- Large Focus Card -->
            <div class="bento-card large tilt-card reveal" style="transition-delay: 0.3s; background: var(--wmsu-black); color: white; border-color: rgba(255,255,255,0.1);">
                <div class="card-glare" style="background: linear-gradient(105deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 40%);"></div>
                <div class="bento-icon" style="background: rgba(255,255,255,0.1); color: white;"><span class="material-symbols-outlined">rocket_launch</span></div>
                <h3 class="bento-title" style="color: white;">Lightning Fast Previews</h3>
                <p class="bento-desc" style="color: rgba(255,255,255,0.7);">Our system generates high-fidelity previews of documents instantly without the need to download large files first, saving critical study time.</p>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        
        // ── Number Counter Animation ──
        const counters = document.querySelectorAll('.counter');
        const speed = 200; 

        const startCounters = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    const updateCount = () => {
                        const count = +counter.innerText.replace(/,/g, '');
                        const inc = target / speed;
                        if (count < target) {
                            counter.innerText = Math.ceil(count + inc).toLocaleString();
                            setTimeout(updateCount, 15);
                        } else {
                            counter.innerText = target.toLocaleString() + '+';
                        }
                    };
                    updateCount();
                    observer.unobserve(counter);
                }
            });
        };
        const counterObserver = new IntersectionObserver(startCounters, { threshold: 0.5 });
        counters.forEach(c => counterObserver.observe(c));

        // ── Scroll Reveal Animation ──
        const reveals = document.querySelectorAll('.reveal');
        const revealConfig = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, revealConfig);
        reveals.forEach(r => revealObserver.observe(r));

        // ── 3D Tilt Effect on Bento Cards ──
        const cards = document.querySelectorAll('.tilt-card');
        cards.forEach(card => {
            const glare = card.querySelector('.card-glare');
            
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const tiltX = ((y - centerY) / centerY) * -5; // max tilt deg
                const tiltY = ((x - centerX) / centerX) * 5;
                
                card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.02, 1.02, 1.02)`;
                if(glare) {
                    glare.style.opacity = '1';
                    glare.style.transform = `translate(${x*0.1}px, ${y*0.1}px)`;
                }
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
                if(glare) glare.style.opacity = '0';
            });
        });

        // ── Populate Trust Marquee ──
        const marqueeContent = document.getElementById('marquee-content');
        const trusts = [
            { icon: 'account_balance', text: 'College of Engineering' },
            { icon: 'biotech', text: 'College of Science' },
            { icon: 'laptop_mac', text: 'Institute of Computer Studies' },
            { icon: 'gavel', text: 'College of Law' },
            { icon: 'analytics', text: 'College of Accountancy' },
            { icon: 'local_hospital', text: 'College of Nursing' },
        ];
        
        // Populate normal and duplicate for infinite continuous scroll
        let htmlStr = '';
        trusts.forEach(t => {
            htmlStr += `<div class="trust-badge"><span class="material-symbols-outlined">${t.icon}</span>${t.text}</div>`;
        });
        marqueeContent.innerHTML = htmlStr + htmlStr + htmlStr; // Repeat to overflow securely
    });
</script>

<?php require_once 'includes/footer.php'; ?>
