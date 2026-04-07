<?php
/**
 * WMSU ARL Hub: Faculty & Research Directory
 * Interactive filter, functional buttons, proper HTML structure
 */
require_once '../config/auth.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty & Research - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        .faculty-hero {
            background: white;
            border-bottom: 1px solid var(--border-light);
            padding: <?php echo $isLoggedIn ? '80px' : '120px'; ?> 24px 80px;
            border-radius: <?php echo $isLoggedIn ? 'var(--radius-lg)' : '0'; ?>;
        }
        .filter-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        .filter-chip {
            display: inline-flex;
            padding: 10px 24px;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: 40px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-chip:hover { border-color: var(--wmsu-red); color: var(--wmsu-red); }
        .filter-chip.active { background: var(--wmsu-red); color: white; border-color: var(--wmsu-red); box-shadow: var(--shadow-red); }
        
        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 32px;
        }
        .faculty-card {
            background: white;
            border-radius: var(--radius-8);
            border: 1px solid var(--border-light);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .faculty-card:hover {
            box-shadow: var(--shadow-lg);
            border-color: rgba(184, 28, 46, 0.2);
            transform: translateY(-6px);
        }
        .faculty-card-photo {
            height: 240px;
            background: var(--bg-base);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .faculty-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
        }
        .verified-badge {
            position: absolute;
            bottom: 16px;
            right: 16px;
            background: var(--wmsu-red);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .faculty-info { padding: 32px; }
        .faculty-rank {
            font-size: 12px;
            font-weight: 800;
            color: var(--wmsu-red);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .faculty-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--wmsu-black);
            margin-bottom: 8px;
        }
        .faculty-dept {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 24px;
        }
        .faculty-actions {
            display: flex;
            gap: 8px;
        }
        .btn-view-research {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-8);
            font-size: 12px;
            font-weight: 700;
            color: var(--wmsu-black);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-view-research:hover {
            border-color: var(--wmsu-red);
            color: var(--wmsu-red);
            background: rgba(184, 28, 46, 0.04);
        }
        .btn-email {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 12px;
            background: white;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-8);
            color: var(--wmsu-black);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-email:hover {
            border-color: var(--wmsu-red);
            color: var(--wmsu-red);
            background: rgba(184, 28, 46, 0.04);
        }
        .hidden { display: none !important; }
    </style>
</head>
<body class="dashboard-body">

<?php if ($isLoggedIn): ?>
    <?php require_once '../includes/dashboard-nav.php'; ?>
    <div class="main-layout">
        <?php require_once '../includes/sidebar.php'; ?>
        <main class="main-content">
<?php else: ?>
    <?php require_once '../includes/header.php'; ?>
<?php endif; ?>

<div class="faculty-hero">
    <div style="max-width: 1280px; margin: 0 auto; text-align: center;">
        <p style="font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4em; color: var(--wmsu-red); margin-bottom: 32px;">Faculty Members</p>
        <h1 style="font-family: 'Inter', sans-serif; font-size: 2.5rem; font-weight: 900; color: var(--wmsu-black); letter-spacing: -1px; line-height: 0.9; margin-bottom: 40px;">Research & Faculty</h1>
        <p style="max-width: 720px; margin: 0 auto; font-size: 18px; font-weight: 500; color: var(--text-secondary); line-height: 1.6;">Explore the directory of faculty members and their research contributions to the ARL Hub.</p>
    </div>
</div>

<main style="max-width: 1280px; margin: 80px auto; padding: 0 24px;">
    
    <!-- ── Faculty Filter ── -->
    <div class="filter-strip" style="margin-bottom: 64px;" id="filterStrip">
        <div class="filter-chip active" data-dept="all" onclick="filterFaculty('all', this)">All Departments</div>
        <div class="filter-chip" data-dept="Computer Science" onclick="filterFaculty('Computer Science', this)">Computer Science</div>
        <div class="filter-chip" data-dept="Electrical Engineering" onclick="filterFaculty('Electrical Engineering', this)">Electrical Engineering</div>
        <div class="filter-chip" data-dept="Physics" onclick="filterFaculty('Physics', this)">Physics</div>
        <div class="filter-chip" data-dept="Environmental Science" onclick="filterFaculty('Environmental Science', this)">Environmental Science</div>
        <div class="filter-chip" data-dept="Information Technology" onclick="filterFaculty('Information Technology', this)">Information Technology</div>
        <div class="filter-chip" data-dept="Mathematics" onclick="filterFaculty('Mathematics', this)">Mathematics</div>
    </div>

    <!-- ── Faculty Grid ── -->
    <div class="faculty-grid" id="facultyGrid">
        
        <?php
        $faculty_sim = [
            ['name' => 'Dr. Maria Elena Santos', 'rank' => 'Professor VI', 'dept' => 'Computer Science', 'email' => 'mesantos@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria'],
            ['name' => 'Engr. Robert Chen', 'rank' => 'Associate Professor II', 'dept' => 'Electrical Engineering', 'email' => 'rchen@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Robert'],
            ['name' => 'Dr. Julian Arnaiz', 'rank' => 'Assistant Professor IV', 'dept' => 'Physics', 'email' => 'jarnaiz@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Julian'],
            ['name' => 'Prof. Sarah Villafuerte', 'rank' => 'Professor I', 'dept' => 'Environmental Science', 'email' => 'svillafuerte@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sarah'],
            ['name' => 'Dr. Antonio Luna', 'rank' => 'Professor IV', 'dept' => 'Information Technology', 'email' => 'aluna@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Antonio'],
            ['name' => 'Prof. Clara Reyes', 'rank' => 'Associate Professor I', 'dept' => 'Mathematics', 'email' => 'creyes@wmsu.edu.ph', 'img' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=Clara'],
        ];

        foreach($faculty_sim as $f): ?>
            <div class="faculty-card" data-department="<?php echo htmlspecialchars($f['dept']); ?>">
                <div class="faculty-card-photo">
                    <img src="<?php echo $f['img']; ?>" class="faculty-avatar" alt="<?php echo htmlspecialchars($f['name']); ?>">
                    <div class="verified-badge">Verified</div>
                </div>
                <div class="faculty-info">
                    <p class="faculty-rank"><?php echo $f['rank']; ?></p>
                    <h3 class="faculty-name"><?php echo $f['name']; ?></h3>
                    <p class="faculty-dept"><?php echo $f['dept']; ?> Department</p>
                    <div class="faculty-actions">
                        <a href="<?php echo BASE_URL; ?>core/browse.php?q=<?php echo urlencode($f['name']); ?>" class="btn-view-research">
                            <span class="material-symbols-outlined" style="font-size: 16px;">science</span>
                            View Research
                        </a>
                        <a href="mailto:<?php echo $f['email']; ?>" class="btn-email" title="Send Email to <?php echo $f['name']; ?>">
                            <span class="material-symbols-outlined" style="font-size: 18px;">mail</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

</main>

<?php if ($isLoggedIn): ?>
        </main>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<script>
function filterFaculty(dept, chipEl) {
    // Update active chip
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    chipEl.classList.add('active');
    
    // Filter cards
    const cards = document.querySelectorAll('.faculty-card');
    cards.forEach(card => {
        if (dept === 'all' || card.dataset.department === dept) {
            card.classList.remove('hidden');
            card.style.animation = 'fadeIn 0.4s ease forwards';
        } else {
            card.classList.add('hidden');
        }
    });
}
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</body>
</html>
