<?php
require 'config/db.php';
function printColumns($table, $pdo) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "Columns for $table: ";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . ", ";
        }
        echo "\n";
    } catch(Exception $e) {
        echo "Error on $table: " . $e->getMessage() . "\n";
    }
}
printColumns('notifications', $pdo);
printColumns('materials', $pdo);
printColumns('audit_logs', $pdo);
