<?php
/**
 * WMSU ARL Hub: Global Institutional Footer v2
 * Standardized Fonts: Inter (Heading & Body)
 * Typography: h1 (64px), h2 (12px), body (16px)
 * Spacing: Base Unit (4), Border Radius (8px)
 */
?>
<style>
    .institutional-footer {
        background: var(--wmsu-black);
        color: white;
        padding: 80px 32px 40px 32px; /* calc(var(--base-unit) * [20, 10]) */
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 64px;
        max-width: 1280px;
        margin: 0 auto;
    }

    .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .footer-brand-top {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .footer-logo {
        width: 40px;
        height: 40px;
        background: var(--wmsu-red);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-8);
    }

    .footer-brand-text {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .footer-section h4 {
        font-size: var(--font-h2); /* 12px */
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        color: rgba(255, 255, 255, 0.4);
        margin-bottom: 24px;
    }

    .footer-links {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .footer-link {
        font-size: 14px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.5);
    }

    .footer-link:hover {
        color: white;
    }

    .footer-bottom {
        margin-top: 80px;
        padding-top: 32px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        text-align: center;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.3);
        font-weight: 600;
    }

    @media (max-width: 1024px) {
        .footer-grid { grid-template-columns: 1fr 1fr; gap: 48px; }
    }

    @media (max-width: 640px) {
        .footer-grid { grid-template-columns: 1fr; }
    }
</style>

<footer class="institutional-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-brand-top">
                <div class="footer-logo">
                    <img src="<?php echo BASE_URL; ?>images/logo.png" alt="WMSU logo" style="width: 100%; height: auto; border-radius: inherit;">
                </div>
                <span class="footer-brand-text">WMSU ARL Hub</span>
            </div>
            <p style="color: rgba(255, 255, 255, 0.5); font-size: 15px; line-height: 1.6; max-width: 320px;">
                A centralized academic resource library for WMSU students and faculty — Zamboanga City.
            </p>
        </div>

        <div class="footer-section">
            <h4>Quick Links</h4>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>" class="footer-link">Home</a>
                <a href="<?php echo BASE_URL; ?>core/browse.php" class="footer-link">Browse Materials</a>
                <a href="<?php echo BASE_URL; ?>core/upload.php" class="footer-link">Upload a Material</a>
                <a href="#" class="footer-link">Search by Subject</a>
            </div>
        </div>

        <div class="footer-section">
            <h4>User Access</h4>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>auth/login.php" class="footer-link">Student Login</a>
                <a href="<?php echo BASE_URL; ?>auth/login.php" class="footer-link">Faculty Login</a>
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="footer-link">Sign up for Account</a>
                <a href="<?php echo BASE_URL; ?>auth/forgot-password.php" class="footer-link">Forgot Password</a>
            </div>
        </div>

        <div class="footer-section">
            <h4>Information</h4>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>core/about.php" class="footer-link">About This System</a>
                <a href="<?php echo BASE_URL; ?>core/terms.php" class="footer-link">Terms of Use</a>
                <a href="<?php echo BASE_URL; ?>core/terms.php" class="footer-link">Privacy Policy</a>
                <a href="#" class="footer-link">Contact Administrator</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        © 2026 Academic Resource Library and Study Materials Hub — Western Mindanao State University, Zamboanga City. All rights reserved.
    </div>
</footer>

<!-- ── Responsive Handling ── -->
<script>
    // Handle body layout based on sidebar existence
    window.addEventListener('scroll', () => {
        const header = document.querySelector('.institutional-header');
        if (header) {
            header.style.background = window.scrollY > 20 ? 'var(--wmsu-black)' : 'var(--wmsu-black)';
        }
    });
</script>
</body>
</html>
