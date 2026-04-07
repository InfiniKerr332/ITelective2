<?php
/**
 * WMSU ARL Hub: Masterpiece Faculty Dashboard
 * Institutional Excellence — Pure CSS (No Frameworks)
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
require_once '../config/auth.php';
checkAuth('faculty');

$userId   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Faculty Member';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ?"); $stmt->execute([$userId]);
    $totalUploads = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ? AND status = 'pending'"); $stmt->execute([$userId]);
    $pendingUploads = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ? AND status = 'approved'"); $stmt->execute([$userId]);
    $approvedUploads = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(downloads_count),0) FROM materials WHERE contributor_id = ?"); $stmt->execute([$userId]);
    $totalDownloads = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE contributor_id = ? ORDER BY created_at DESC LIMIT 8"); $stmt->execute([$userId]);
    $myMaterials = $stmt->fetchAll();
} catch (PDOException $e) {
    $totalUploads = $pendingUploads = $approvedUploads = $totalDownloads = 0;
    $myMaterials = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal - WMSU ARL Hub</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        .faculty-hero {
            background: white;
            padding: 4rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-light);
            margin-bottom: 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .metric-card {
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            border-color: var(--wmsu-red);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--wmsu-black);
            line-height: 1;
        }

        .metric-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--text-muted);
        }

        .material-table {
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            overflow: hidden;
            border-collapse: collapse;
        }

        .material-table th {
            text-align: left;
            padding: 1.5rem 2rem;
            background: var(--bg-base);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-light);
        }

        .material-table td {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
            font-weight: 600;
        }

        .status-pill {
            padding: 0.4rem 0.8rem;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .status-approved { background: #E6F4EA; color: #1E8E3E; }
        .status-pending { background: #FEF7E0; color: #B05E27; }
    </style>
</head>
<body class="dashboard-body">

<?php require_once '../includes/dashboard-nav.php'; ?>

<div class="main-layout">
    <?php require_once '../includes/sidebar.php'; ?>

    <main class="main-content">
        
        <div class="faculty-hero">
            <div>
                <p style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3em; color: var(--wmsu-red); margin-bottom: 1rem;">Academic Faculty Portal</p>
                <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--wmsu-black); margin-bottom: 1rem;">Welcome, <span style="font-style: italic;"><?php echo htmlspecialchars($user_name); ?></span></h1>
                <p style="color: var(--text-secondary); font-weight: 600; opacity: 0.6;">Managing the institutional archive and knowledge dissemination.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>core/upload.php" class="btn btn-primary" style="height: 60px; padding: 0 3rem; font-size: 13px;">
                Publish Content
            </a>
        </div>

        <div class="metric-grid">
            <div class="metric-card">
                <span class="metric-label">Total Assets</span>
                <span class="metric-value"><?php echo $totalUploads; ?></span>
                <div style="height: 3px; width: 40px; background: var(--wmsu-red);"></div>
            </div>
            <div class="metric-card">
                <span class="metric-label">Verified</span>
                <span class="metric-value"><?php echo $approvedUploads; ?></span>
                <div style="height: 3px; width: 40px; background: #1E8E3E;"></div>
            </div>
            <div class="metric-card">
                <span class="metric-label">Awaiting Verification</span>
                <span class="metric-value"><?php echo $pendingUploads; ?></span>
                <div style="height: 3px; width: 40px; background: #B05E27;"></div>
            </div>
            <div class="metric-card">
                <span class="metric-label">Global Accesses</span>
                <span class="metric-value"><?php echo number_format($totalDownloads); ?></span>
                <div style="height: 3px; width: 40px; background: var(--wmsu-gold);"></div>
            </div>
        </div>

        <section style="margin-top: 4rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800;">Recent Intelligence Operations</h2>
                <a href="faculty-uploads.php" style="font-size: 11px; font-weight: 800; color: var(--wmsu-red); text-transform: uppercase;">View Archive</a>
            </div>

            <table class="material-table">
                <thead>
                    <tr>
                        <th>Asset Title</th>
                        <th>Classification</th>
                        <th>Validation Status</th>
                        <th>Metrics</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($myMaterials) > 0): ?>
                        <?php foreach($myMaterials as $item): ?>
                            <tr>
                                <td style="font-weight: 800; color: var(--wmsu-black);"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="color: var(--text-secondary);"><?php echo htmlspecialchars($item['category']); ?></td>
                                <td>
                                    <span class="status-pill <?php echo $item['status'] === 'approved' ? 'status-approved' : 'status-pending'; ?>">
                                        <?php echo strtoupper($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-muted); font-size: 12px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                                        <?php echo number_format($item['downloads_count']); ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $item['id']; ?>" style="color: var(--wmsu-red); font-size: 11px; font-weight: 800; text-transform: uppercase;">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 5rem; color: var(--text-muted);">No assets available in your faculty archive.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
