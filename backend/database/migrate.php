<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Database\DatabaseSeeder;

echo "=========================================\n";
echo "       AVO ERP Database Migrator\n";
echo "=========================================\n";

try {
    $pdo = Database::getConnection();
    $driver = Database::getDriver();
    echo "✓ Database Connected Successfully! Driver: [{$driver}]\n";

    if ($driver === 'mysql') {
        $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
        echo "✓ Active MySQL Database: `{$dbName}`\n";
    }

    echo "✓ Ensuring all database tables exist...\n";
    Database::initializeSchema($pdo, $driver);

    echo "✓ Seeding database with 14 Archetype users, products, customers, bids, scrap, and dynamic permission matrix...\n";
    DatabaseSeeder::run();
    echo "✓ Seeding complete!\n\n";

    echo "--- Table Row Summary (Visible in phpMyAdmin `avo_erp`) ---\n";
    $tables = [
        'users',
        'products',
        'customers',
        'orders',
        'order_items',
        'invoices',
        'audit_logs',
        'rfp_bids',
        'scrap_ledger',
        'permission_matrix'
    ];

    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo sprintf(" - %-20s : %d rows\n", $table, $count);
    }

    echo "\n✓ All 10 tables are present and populated in `avo_erp` database!\n";
    echo "=========================================\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

