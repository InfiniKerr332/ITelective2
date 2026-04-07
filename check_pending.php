<?php
require 'config/db.php';
echo "User 1: " . ($pdo->query("SELECT id FROM users WHERE id=1")->fetch() ? 'Found' : 'Not Found') . "\n";
echo "User 8: " . ($pdo->query("SELECT id FROM users WHERE id=8")->fetch() ? 'Found' : 'Not Found') . "\n";
