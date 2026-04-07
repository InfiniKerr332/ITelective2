<?php
/**
 * WMSU ARL Hub: Upload Material — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$message = '';
$status  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = $_POST['category'];
    $userId      = $_SESSION['user_id'];
    $role        = $_SESSION['user_role'];

    $uploadDir = '../uploads/materials/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['material_files']) && !empty($_FILES['material_files']['name'][0])) {
        $files = $_FILES['material_files'];
        $totalFiles = count($files['name']);
        $totalSize = array_sum($files['size']);
        $maxSize = 25 * 1024 * 1024; // 25MB limit
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp'];
        $uploadedFiles = [];
        $hasError = false;

        if ($totalSize > $maxSize) {
            $message = "Cumulative file size exceeds 25MB limit.";
            $status = "error";
            $hasError = true;
        }

        if (!$hasError) {
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($files['error'][$i] === 0) {
                    $fileName = $files['name'][$i];
                    $fileTmp  = $files['tmp_name'][$i];
                    $fileSize = $files['size'][$i];
                    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (in_array($fileExt, $allowed)) {
                        $newName = uniqid('arl_', true) . '.' . $fileExt;
                        $dest    = $uploadDir . $newName;
                        $dbPath  = 'uploads/materials/' . $newName;

                        if (move_uploaded_file($fileTmp, $dest)) {
                            $uploadedFiles[] = [
                                'name' => $fileName,
                                'path' => $dbPath,
                                'type' => $fileExt,
                                'size' => $fileSize
                            ];
                        } else {
                            $message = "Failed to save one or more files.";
                            $status = "error";
                            $hasError = true;
                            break;
                        }
                    } else {
                        $message = "Invalid file type: $fileExt. Allowed: PDF, DOC, PPT, JPG, PNG, WEBP.";
                        $status = "error";
                        $hasError = true;
                        break;
                    }
                }
            }
        }

        if (!$hasError && !empty($uploadedFiles)) {
            try {
                $materialStatus = ($role === 'admin') ? 'approved' : 'pending';
                $isOfficial     = ($role === 'admin' || $role === 'faculty') ? 1 : 0;
                
                // Store primary file path as the first uploaded file
                $primaryPath = $uploadedFiles[0]['path'];

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO materials (title, description, file_path, category, contributor_id, status, is_official) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$title, $description, $primaryPath, $category, $userId, $materialStatus, $isOfficial]);
                $materialId = $pdo->lastInsertId();

                // Insert all files into material_files table
                $stmtFile = $pdo->prepare("INSERT INTO material_files (material_id, file_path, file_name, file_type, file_size) VALUES (?,?,?,?,?)");
                foreach ($uploadedFiles as $file) {
                    $stmtFile->execute([$materialId, $file['path'], $file['name'], $file['type'], $file['size']]);
                }

                $pdo->commit();

                // Notify Administrators of new pending content
                if ($materialStatus === 'pending') {
                    try {
                        $adminStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
                        $admins = $adminStmt->fetchAll();
                        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'system', ?)");
                        foreach ($admins as $admin) {
                            $notifStmt->execute([
                                $admin['id'],
                                "New Material for Review",
                                "A new material '$title' was uploaded by {$_SESSION['full_name']} and is awaiting moderation.",
                                "admin/admin-moderation.php"
                            ]);
                        }
                    } catch (PDOException $e) { /* Notify failure is non-fatal */ }
                }

                logAudit($pdo, $userId, "UPLOAD", "Uploaded resource: $title with $totalFiles files");
                $message = "Material uploaded successfully with " . count($uploadedFiles) . " file(s)!" . ($materialStatus === 'pending' ? " It is awaiting moderation." : "");
                $status  = "success";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Database error: " . $e->getMessage();
                $status  = "error";
            }
        }
    } else {
        $message = "Please select at least one valid file.";
        $status  = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Material - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#B81C2E',
                    }
                }
            }
        }
    </script>
    <style>
        :root { --sidebar-width: 240px; --header-height: 64px; --wmsu-black: #1A1A2E; --wmsu-red: #B81C2E; --wmsu-red-dark: #8C1222; --bg-base: #F4F4F6; --border-light: rgba(0,0,0,0.05); --text-primary: #1A1A28; --text-secondary: #4A4A5A; --text-muted: #848494; --radius-8: 8px; --shadow-sm: 0 2px 8px rgba(0,0,0,0.05); --shadow-md: 0 12px 24px -8px rgba(0,0,0,0.1); --shadow-red: 0 12px 24px -8px rgba(184,28,46,0.4); }
        body { font-family: 'Inter', sans-serif; background-color: #F4F4F6; }
        h1,h2,h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; vertical-align: middle; }
    </style>
</head>
<body class="text-[#1A1A2E]">

<?php require_once '../includes/dashboard-nav.php'; ?>

<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>

    <main class="ml-[240px] flex-1 bg-[#F4F4F6] flex flex-col">
        <div class="p-8 flex-1">
            <div class="max-w-3xl mx-auto">

                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Upload Material</h1>
                    <p class="text-[#4A4A5A] mt-1">Share your academic work with the WMSU community.</p>
                </div>

                <!-- Alert -->
                <?php if ($message): ?>
                <div class="flex items-start gap-3 p-4 rounded-xl mb-6 <?php echo $status === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0 mt-0.5"><?php echo $status === 'success' ? 'check_circle' : 'error'; ?></span>
                    <span class="text-sm font-medium"><?php echo htmlspecialchars($message); ?></span>
                </div>
                <?php endif; ?>

                <!-- Upload Form Card -->
                <div class="bg-white rounded-xl border border-black/5 p-8">
                    <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return confirm('Are you sure you want to completely upload this material?');">

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wider mb-2">Resource Title <span class="text-[#B81C2E]">*</span></label>
                            <input type="text" name="title" required
                                   placeholder="e.g. Advanced Calculus Handout — BSIT 2A"
                                   class="w-full px-4 py-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] placeholder-[#9CA3AF] focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] transition-colors">
                        </div>

                        <!-- Category + Level row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wider mb-2">Category <span class="text-[#B81C2E]">*</span></label>
                                <select name="category" required
                                        class="w-full px-4 py-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] transition-colors">
                                    <option value="Modules">Study Modules</option>
                                    <option value="Handouts">Class Handouts</option>
                                    <option value="Past Exams">Previous Exams</option>
                                    <option value="Research">Research Projects</option>
                                    <option value="Thesis">Thesis / Capstone</option>
                                    <option value="Reviewer">Reviewer</option>
                                    <option value="Textbook">Textbook</option>
                                    <option value="Lecture Notes">Lecture Notes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wider mb-2">Academic Level</label>
                                <select name="level"
                                        class="w-full px-4 py-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] transition-colors">
                                    <option value="Undergraduate">Undergraduate</option>
                                    <option value="Graduate">Graduate</option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wider mb-2">Brief Description</label>
                            <textarea name="description" rows="3"
                                      placeholder="Provide a short overview of what this material covers..."
                                      class="w-full px-4 py-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] placeholder-[#9CA3AF] focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] transition-colors resize-none"></textarea>
                        </div>

                        <!-- File Upload Zone -->
                        <div>
                            <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wider mb-2">Material Files <span class="text-[#B81C2E]">*</span></label>
                            <div id="dropZone"
                                 onclick="document.getElementById('fileInput').click()"
                                 class="border-2 border-dashed border-[#E2E2E4] rounded-xl p-10 text-center cursor-pointer hover:border-[#B81C2E] hover:bg-[#FFF8F8] transition-colors group">
                                <input type="file" name="material_files[]" id="fileInput" required hidden accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.webp" multiple onchange="updateFileStatus(this)">
                                <span class="material-symbols-outlined text-[48px] text-[#D1D1D9] group-hover:text-[#B81C2E] transition-colors block mb-3" id="uploadIcon">cloud_upload</span>
                                <p id="fileName" class="text-sm font-medium text-[#4A4A5A]">Drop your files here or click to browse</p>
                                <p class="text-xs text-[#9CA3AF] mt-2">Accepted formats: Documents & Images · Max 25MB total</p>
                                <div id="fileList" class="mt-4 hidden text-left space-y-1"></div>
                            </div>
                        </div>

                        <!-- Guidelines Note -->
                        <div class="flex items-start gap-3 p-4 bg-[#F0F4FF] border border-blue-100 rounded-xl">
                            <span class="material-symbols-outlined text-blue-500 text-[20px] flex-shrink-0">info</span>
                            <p class="text-xs text-blue-700 leading-relaxed">
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    Admin uploads are <strong>automatically approved</strong> and published immediately.
                                <?php elseif ($_SESSION['user_role'] === 'faculty'): ?>
                                    Faculty uploads are marked as official contributions and reviewed for final approval.
                                <?php else: ?>
                                    Student uploads require <strong>moderator review</strong> before becoming publicly visible.
                                <?php endif; ?>
                                Please ensure your upload complies with our <a href="<?php echo BASE_URL; ?>core/guidelines.php" class="underline font-semibold">content guidelines</a>.
                            </p>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-[#B81C2E] text-white py-3.5 rounded-lg font-semibold text-sm hover:bg-[#8C1222] transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">publish</span>
                            Publish to ARL Hub
                        </button>
                    </form>
                </div>

            </div>
        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>

<script>
function updateFileStatus(input) {
    const nameEl  = document.getElementById('fileName');
    const iconEl  = document.getElementById('uploadIcon');
    const zone    = document.getElementById('dropZone');
    const listEl  = document.getElementById('fileList');
    
    if (input.files && input.files.length > 0) {
        const count = input.files.length;
        nameEl.innerHTML  = `<strong class="text-[#B81C2E]">${count} file(s) selected</strong>`;
        iconEl.textContent = 'check_circle';
        iconEl.style.color = '#B81C2E';
        zone.style.borderColor = '#B81C2E';
        zone.style.background = '#FFF8F8';
        
        listEl.innerHTML = '';
        listEl.classList.remove('hidden');
        Array.from(input.files).forEach(file => {
            const size = (file.size / (1024 * 1024)).toFixed(2);
            const sizeString = size > 0 ? `${size} MB` : `${(file.size / 1024).toFixed(1)} KB`;
            listEl.innerHTML += `<div class="text-[11px] text-[#4A4A5A] flex justify-between items-center bg-white/50 p-2 rounded-lg border border-black/5">
                <span class="truncate pr-4">${file.name}</span>
                <span class="font-bold shrink-0">${sizeString}</span>
            </div>`;
        });
    } else {
        listEl.classList.add('hidden');
        iconEl.textContent = 'cloud_upload';
        nameEl.textContent = 'Drop your files here or click to browse';
    }
}
</script>
</body>
</html>

