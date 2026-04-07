<?php
/**
 * WMSU ARL Hub: Dashboard Footer (Stitch Design System)
 * Multi-column footer on dark #1A1A2E background
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
?>
<!-- Footer pushed down with mt-auto and spaced with mt-24 for scrolling -->
<footer class="mt-auto mt-24 pt-16 pb-8 px-8 text-white bg-[#1A1A2E] border-t border-white/5">
    <div class="max-w-[1280px] mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-12">
            <!-- Brand Block -->
            <div class="lg:col-span-5 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white flex items-center justify-center rounded-lg shadow-lg overflow-hidden">
                        <img src="<?php echo BASE_URL; ?>images/logo.png" alt="WMSU Logo" class="w-full h-full object-cover">
                    </div>
                    <span class="text-xl font-bold text-white font-heading tracking-tight uppercase">ARL HUB</span>
                </div>
                <p class="text-white/40 text-[14px] max-w-sm leading-relaxed">
                    A centralized academic resource library for WMSU students and faculty — Zamboanga City.
                </p>
                <div class="flex gap-4 items-center">
                    <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                        <span class="material-symbols-outlined text-white/50 text-[18px]">school</span>
                    </div>
                    <span class="text-[11px] font-medium text-white/25 uppercase tracking-[0.2em]">Official WMSU Portal</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <h4 class="text-[14px] font-bold text-white font-heading tracking-wide">Quick Links</h4>
                <ul class="flex flex-col gap-2.5">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>core/browse.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Browse Materials</a></li>
                    <li><a href="<?php echo BASE_URL; ?>core/upload.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Upload a Material</a></li>
                    <li><a href="<?php echo BASE_URL; ?>core/search-results.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Search by Subject</a></li>
                </ul>
            </div>

            <!-- User Access -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <h4 class="text-[14px] font-bold text-white font-heading tracking-wide">User Access</h4>
                <ul class="flex flex-col gap-2.5">
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Student Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Faculty Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/register.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Register an Account</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/forgot-password.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Forgot Password</a></li>
                </ul>
            </div>

            <!-- Information -->
            <div class="lg:col-span-3 flex flex-col gap-4">
                <h4 class="text-[14px] font-bold text-white font-heading tracking-wide">Information</h4>
                <ul class="flex flex-col gap-2.5">
                    <li><a href="<?php echo BASE_URL; ?>core/about.php" class="text-[13px] text-white/45 hover:text-white transition-colors">About This System</a></li>
                    <li><a href="#" class="text-[13px] text-white/45 hover:text-white transition-colors">Terms of Use</a></li>
                    <li><a href="#" class="text-[13px] text-white/45 hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="<?php echo BASE_URL; ?>core/guidelines.php" class="text-[13px] text-white/45 hover:text-white transition-colors">Content Guidelines</a></li>
                    <li><a href="#" class="text-[13px] text-white/45 hover:text-white transition-colors">Contact Administrator</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Strip -->
        <div class="pt-6 border-t border-white/5">
            <p class="text-center text-[12px] text-white/25 font-medium">
                © <?php echo date('Y'); ?> Academic Resource Library and Study Materials Hub — Western Mindanao State University, Zamboanga City. All rights reserved.
            </p>
        </div>
    </div>
</footer>
