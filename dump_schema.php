<?php
require 'config/db.php';
$schema = [];
foreach(['notifications', 'materials', 'users', 'audit_logs'] as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $cols[] = $row['Field']; }
        $schema[$table] = $cols;
    } catch(Exception $e) {
        $schema[$table] = "Error: " . $e->getMessage();
    }
}
file_put_contents('schema_dump.json', json_encode($schema, JSON_PRETTY_PRINT));
