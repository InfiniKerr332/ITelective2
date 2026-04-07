<?php
/**
 * WMSU ARL Hub: Masterpiece About Us (Institutional v3)
 * Pure CSS (No Frameworks)
 * Standardized Fonts: Inter (Heading & Body)
 * Typography: h1 (64px), h2 (12px), body (16px)
 * Spacing: Base Unit (4), Border Radius (8px)
 */
require_once '../config/auth.php';
$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    require_once '../includes/header.php';
} else {
    $page_title = "About ARL Hub | WMSU ARL Hub";
}
?>

<body class="dashboard-body">

<?php if ($isLoggedIn): ?>
    <?php require_once '../includes/dashboard-nav.php'; ?>
    <div class="main-layout">
        <?php require_once '../includes/sidebar.php'; ?>
        <main class="main-content">
<?php endif; ?>

<div class="about-hero" style="background: white; border-bottom: 1px solid var(--border-light); padding: <?php echo $isLoggedIn ? '80px' : '120px'; ?> 24px 80px; border-radius: <?php echo $isLoggedIn ? 'var(--radius-lg)' : '0'; ?>;">
    <div style="max-width: 1280px; margin: 0 auto; text-align: center;">
        <p style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4em; color: var(--wmsu-red); margin-bottom: 32px;">Our Goal</p>
        <h1 style="font-family: 'Inter', sans-serif; font-size: var(--font-h1); font-weight: 900; color: var(--wmsu-black); letter-spacing: -3px; line-height: 0.9; margin-bottom: 40px;">About ARL Hub</h1>
        <p style="max-width: 720px; margin: 0 auto; font-size: 20px; font-weight: 500; color: var(--text-secondary); line-height: 1.6;">A centralized platform for resources and research for the Western Mindanao State University community.</p>
    </div>
</div>

<main style="max-width: 1280px; margin: 80px auto; padding: 0 24px;">
    
    <!-- ── Visionary Intelligence ── -->
    <div class="grid grid-2" style="margin-bottom: 80px;">
        <div class="card" style="padding: 64px;">
            <p style="font-size: var(--font-h2); font-weight: 800; text-transform: uppercase; letter-spacing: 0.3em; color: var(--wmsu-red); margin-bottom: 24px;">Our Mission</p>
            <h2 style="font-size: 32px; font-weight: 800; color: var(--wmsu-black); text-transform: none; letter-spacing: -1px; margin-bottom: 32px;">Empowering Students.</h2>
            <p style="font-size: 16px; line-height: 1.8; color: var(--text-secondary); font-weight: 500;">
                The ARL Hub is designed to make it easy for students and faculty to share knowledge. By centralizing academic materials, we make sure that WMSU resources are easy to find and use for everyone in the university.
            </p>
        </div>
        <div style="display: grid; gap: 24px;">
            <div class="card" style="padding: 40px;">
                <div style="width: 48px; height: 48px; background: var(--wmsu-red-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <span class="material-symbols-outlined" style="color: var(--wmsu-red); font-size: 24px;">verified_user</span>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--wmsu-black); margin-bottom: 12px;">Quality Controlled</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">Every resource published within the library undergoes review by faculty to ensure academic integrity.</p>
            </div>
            <div class="card" style="padding: 40px;">
                <div style="width: 48px; height: 48px; background: var(--wmsu-red-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <span class="material-symbols-outlined" style="color: var(--wmsu-red); font-size: 24px;">hub</span>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; color: var(--wmsu-black); margin-bottom: 12px;">Unified Access</h3>
                <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">Designed as a centralized hub connecting local resources, research data, and learning materials into one portal.</p>
            </div>
        </div>
    </div>

    <!-- ── Institutional Geometry ── -->
    <div style="background: var(--wmsu-black); border-radius: var(--radius-8); padding: 80px; text-align: center; position: relative; overflow: hidden; margin-bottom: 80px;">
        <div style="position: relative; z-index: 2;">
            <h2 style="color: white; font-size: 48px; font-weight: 900; letter-spacing: -2px; margin-bottom: 24px; text-transform: none;">Join Our Community.</h2>
            <p style="color: rgba(255, 255, 255, 0.6); max-width: 600px; margin: 0 auto 40px; font-size: 18px;">Start your journey with the hub that makes finding resources simple.</p>
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-primary" style="padding: 16px 40px; text-decoration: none;">Sign up</a>
                <a href="<?php echo BASE_URL; ?>core/browse.php" class="btn btn-login" style="padding: 16px 40px; text-decoration: none;">Browse Now</a>
            </div>
        </div>
        <div style="position: absolute; right: -100px; top: -50px; width: 400px; height: 400px; background: radial-gradient(circle, var(--wmsu-red) 0%, transparent 70%); opacity: 0.2;"></div>
    </div>

</main>

<?php if ($isLoggedIn): ?>
        </main>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
</body>
</html>
