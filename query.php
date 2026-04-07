<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, title, contributor_id, status FROM materials WHERE status = 'pending'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
