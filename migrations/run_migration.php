<?php
require_once __DIR__ . '/../includes/db.php';

$migrations = [
    '001_create_settings_table.sql',
    '002_create_users_table.sql',
    '003_create_pages_table.sql',
    '004_create_blocks_table.sql',
    '005_create_polls_table.sql',
    '006_add_remember_token_to_users.sql'
];

foreach ($migrations as $migration) {
    $sql = file_get_contents(__DIR__ . '/' . $migration);
    try {
        $pdo->exec($sql);
        echo "Migration " . $migration . " successful.\n";
    } catch (PDOException $e) {
        // die("Migration " . $migration . " failed: " . $e->getMessage() . "\n");
        // ignore errors for now
    }
}
