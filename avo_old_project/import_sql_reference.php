<?php
/**
 * AVO ERP - Import SQL Reference Files into MySQL Database
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = $argv[1] ?? 'avoservice_avoservicrm';

$con = mysqli_connect($host, $user, $pass);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error() . PHP_EOL);
}

// Ensure database exists
mysqli_query($con, "CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
mysqli_select_db($con, $dbname);

$dir = __DIR__ . DIRECTORY_SEPARATOR . 'sql_reference';
$files = glob($dir . DIRECTORY_SEPARATOR . '*.sql');

echo "========================================================\n";
echo "Importing SQL Files into Database: `{$dbname}`\n";
echo "Found: " . count($files) . " .sql files\n";
echo "========================================================\n";

$success = 0;
$failed = 0;
$errors = [];

mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($con, "SET NAMES utf8mb4");

foreach ($files as $idx => $file) {
    $table_name = basename($file, '.sql');
    $sql = file_get_contents($file);

    if (empty(trim($sql))) {
        continue;
    }

    if (mysqli_multi_query($con, $sql)) {
        // Drain all results from multi_query
        do {
            if ($result = mysqli_store_result($con)) {
                mysqli_free_result($result);
            }
        } while (mysqli_more_results($con) && mysqli_next_result($con));

        if (mysqli_errno($con)) {
            $failed++;
            $err = mysqli_error($con);
            $errors[] = "#" . ($idx + 1) . " `{$table_name}`: " . $err;
            echo "#" . ($idx + 1) . " [ERROR] `{$table_name}`: {$err}\n";
        } else {
            $success++;
            // Check row count in table
            $cnt_res = mysqli_query($con, "SELECT COUNT(*) FROM `{$table_name}`");
            $rows_count = 0;
            if ($cnt_res && $row = mysqli_fetch_row($cnt_res)) {
                $rows_count = $row[0];
            }
            echo "#" . ($idx + 1) . " [OK] `{$table_name}` ({$rows_count} rows)\n";
        }
    } else {
        $failed++;
        $err = mysqli_error($con);
        $errors[] = "#" . ($idx + 1) . " `{$table_name}`: " . $err;
        echo "#" . ($idx + 1) . " [FAIL] `{$table_name}`: {$err}\n";
    }
}

mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 1");

echo "========================================================\n";
echo "Import Summary:\n";
echo "Successfully imported: {$success} tables\n";
echo "Failed: {$failed} tables\n";
if (!empty($errors)) {
    echo "Errors:\n" . implode("\n", $errors) . "\n";
}
echo "========================================================\n";
