<?php
/**
 * WMSU ARL Hub: Submission Guidelines
 */
require_once '../includes/header.php';
?>

<main class="page-container">
    <header class="page-header">
        <h1>Submission Guidelines</h1>
        <p>Ensure your academic contributions reach our high community standards.</p>
    </header>

    <div class="guidelines-card">
        <div class="section">
            <div class="badge">01</div>
            <div class="text">
                <h3>Originality & Academic Integrity</h3>
                <p>All submitted materials must be the original work of the contributor or have appropriate permissions for distribution. Plagiarism will result in immediate removal of content and potential account suspension.</p>
            </div>
        </div>

        <div class="section">
            <div class="badge">02</div>
            <div class="text">
                <h3>Vetting and Verification</h3>
                <p>Resources flagged as "Faculty Verified" are subject to review by departmental heads. Ensure your handouts and modules are consistent with the latest WMSU curriculum standards.</p>
            </div>
        </div>

        <div class="section">
            <div class="badge">03</div>
            <div class="text">
                <h3>Supported Formats</h3>
                <p>To ensure accessibility for all students, we recommend PDF format for text documents. Presentation slides should be converted to PDF before submission to maintain visual consistency.</p>
            </div>
        </div>

        <div class="section">
            <div class="badge">04</div>
            <div class="text">
                <h3>Privacy and Consent</h3>
                <p>Do not include sensitive personal data (e.g., student IDs, private contact information) within the document contents. Always redact names if the documents contain student records.</p>
            </div>
        </div>
    </div>
</main>

<style>
    .page-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 100px 5% 150px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .page-header h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 48px;
        font-weight: 800;
        color: var(--rich-black);
        margin-bottom: 16px;
        letter-spacing: -1.5px;
    }

    .page-header p {
        font-size: 18px;
        color: var(--text-secondary);
        line-height: 1.6;
        max-width: 500px;
        margin: 0 auto;
    }

    .guidelines-card {
        background: var(--white);
        padding: 56px;
        border-radius: 32px;
        border: 1px solid #F3F4F6;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        gap: 48px;
    }

    .section {
        display: flex;
        gap: 32px;
        align-items: flex-start;
    }

    .badge {
        width: 56px;
        height: 56px;
        background: #FEF2F2;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-weight: 800;
        font-size: 20px;
        flex-shrink: 0;
    }

    .text h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: var(--rich-black);
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .text p {
        font-size: 16px;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    @media (max-width: 640px) {
        .page-header h1 { font-size: 36px; }
        .section { flex-direction: column; gap: 16px; }
        .guidelines-card { padding: 32px; }
    }
</style>

<?php include '../includes/footer.php'; ?>
