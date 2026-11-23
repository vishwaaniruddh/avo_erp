<?php
/**
 * AVO ERP - Automated Table Schema & Sample Data Exporter
 * 
 * Purpose:
 *   Dumps each table in the database into its own SQL file (table_name.sql)
 *   containing the complete CREATE TABLE schema and up to the latest 100 records.
 *   Output directory: ./sql_reference/
 * 
 * Usage:
 *   1. Upload this file to your server (e.g. inside the project root).
 *   2. Run via Browser: http://your-server-ip-or-domain/path/to/export_sql_reference.php
 *      OR run via CLI:  php export_sql_reference.php
 */

@ini_set('display_errors', 1);
error_reporting(E_ALL);
@set_time_limit(0);
@ini_set('memory_limit', '1024M');
date_default_timezone_set('Asia/Kolkata');

$is_cli = (php_sapi_name() === 'cli');

// -------------------------------------------------------------------------
// 1. Database Connection Discovery
// -------------------------------------------------------------------------
$db_connections = [];
$config_loaded = false;

// Disable mysqli throwing exceptions so legacy configs don't crash execution on connection failure
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Attempt to load existing config files
$candidate_configs = ['config.php', 'config1.php', 'configtest.php', 'config_old.php'];
foreach ($candidate_configs as $cfg) {
    $cfg_path = __DIR__ . DIRECTORY_SEPARATOR . $cfg;
    if (file_exists($cfg_path)) {
        set_error_handler(function() { return true; });
        try {
            @include($cfg_path);
            $config_loaded = true;
        } catch (\Throwable $e) {
            // Ignore connection errors from legacy files
        }
        restore_error_handler();
        break;
    }
}

// Detect $con1 (Service DB in AVO)
if (isset($con1) && ($con1 instanceof mysqli)) {
    if (@mysqli_query($con1, "SELECT 1")) {
        $db_name = '';
        $res = mysqli_query($con1, "SELECT DATABASE() as db");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $db_name = $row['db'];
        }
        $db_connections['con1'] = [
            'label' => 'Service DB ($con1: ' . ($db_name ?: 'connected') . ')',
            'conn'  => $con1,
            'name'  => $db_name ?: 'service_db'
        ];
    }
}

// Detect $con2 (CRM DB in AVO)
if (isset($con2) && ($con2 instanceof mysqli)) {
    if (@mysqli_query($con2, "SELECT 1")) {
        $db_name = '';
        $res = mysqli_query($con2, "SELECT DATABASE() as db");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $db_name = $row['db'];
        }
        $db_connections['con2'] = [
            'label' => 'CRM DB ($con2: ' . ($db_name ?: 'connected') . ')',
            'conn'  => $con2,
            'name'  => $db_name ?: 'crm_db'
        ];
    }
}

// Check if manual connection params passed via POST or GET
$manual_host = $_REQUEST['db_host'] ?? ($host ?? 'localhost');
$manual_user = $_REQUEST['db_user'] ?? ($user ?? 'root');
$manual_pass = $_REQUEST['db_pass'] ?? ($pass ?? '');
$manual_name = $_REQUEST['db_name'] ?? ($dbname ?? '');

// CLI Argument Parsing
if ($is_cli) {
    if (in_array('--help', $argv) || in_array('-h', $argv)) {
        echo "========================================================\n";
        echo "AVO ERP - Table Schema & Sample Data Exporter (CLI Mode)\n";
        echo "========================================================\n";
        echo "Usage: php export_sql_reference.php [options]\n";
        echo "  --db=DBNAME       Database name (e.g. avoservi_service)\n";
        echo "  --host=HOST       MySQL Host (default: localhost)\n";
        echo "  --user=USER       MySQL User (default from config or root)\n";
        echo "  --pass=PASS       MySQL Password\n";
        echo "  --limit=N         Max sample records per table (default: 100)\n";
        echo "  --target=TARGET   Target connection: con1, con2, all, or manual\n";
        exit(0);
    }

    foreach ($argv as $arg) {
        if (strpos($arg, '--host=') === 0)   $manual_host = trim(substr($arg, 7), "\"'");
        if (strpos($arg, '--user=') === 0)   $manual_user = trim(substr($arg, 7), "\"'");
        if (strpos($arg, '--pass=') === 0)   $manual_pass = trim(substr($arg, 7), "\"'");
        if (strpos($arg, '--db=') === 0)     $manual_name = trim(substr($arg, 5), "\"'");
        if (strpos($arg, '--limit=') === 0)  $record_limit = (int)substr($arg, 8);
        if (strpos($arg, '--target=') === 0) $selected_target = trim(substr($arg, 9), "\"'");
    }
}

if (!empty($manual_name)) {
    $link = @mysqli_connect($manual_host, $manual_user, $manual_pass, $manual_name);
    if ($link && !mysqli_connect_errno()) {
        $db_connections['manual'] = [
            'label' => "Manual DB ($manual_name)",
            'conn'  => $link,
            'name'  => $manual_name
        ];
    }
}

// Selected target connection
if (!isset($selected_target)) {
    $selected_target = $_REQUEST['target'] ?? (!empty($manual_name) ? 'manual' : (isset($db_connections['con1']) ? 'con1' : (isset($db_connections['con2']) ? 'con2' : 'manual')));
}
if (!isset($record_limit)) {
    $record_limit = isset($_REQUEST['limit']) ? max(1, min(1000, (int)$_REQUEST['limit'])) : 100;
}
$action = $_REQUEST['action'] ?? ($is_cli ? 'export' : '');

// Output directory
$base_out_dir = __DIR__ . DIRECTORY_SEPARATOR . 'sql_reference';

// Helper function to send buffered output in real-time to browser
function send_output($msg, $type = 'info') {
    global $is_cli;
    $time = date('H:i:s');
    if ($is_cli) {
        $prefix = "[$time] ";
        echo $prefix . strip_tags($msg) . PHP_EOL;
    } else {
        $color = '#333';
        if ($type === 'success') $color = '#059669';
        if ($type === 'warning') $color = '#d97706';
        if ($type === 'error')   $color = '#dc2626';
        if ($type === 'header')  $color = '#111827; font-weight: bold; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-top: 10px;';
        
        echo "<div style='font-family: monospace; font-size: 12px; line-height: 1.6; color: {$color}; margin: 2px 0;'>";
        echo "<span style='color: #9ca3af;'>[{$time}]</span> {$msg}";
        echo "</div>";
        @ob_flush();
        @flush();
    }
}

// -------------------------------------------------------------------------
// 2. Export Engine Function
// -------------------------------------------------------------------------
function export_database_tables(mysqli $conn, $db_identifier_name, $out_dir, $limit = 100) {
    if (!file_exists($out_dir)) {
        if (!@mkdir($out_dir, 0777, true)) {
            send_output("FAILED to create directory: {$out_dir}", 'error');
            return false;
        }
    }

    send_output("Starting table export for database: <strong>{$db_identifier_name}</strong> (Sample limit: {$limit} records per table)", 'header');
    send_output("Destination directory: <code>{$out_dir}</code>", 'info');

    // Fetch all tables
    $tables = [];
    $res = mysqli_query($conn, "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if (!$res) {
        $res = mysqli_query($conn, "SHOW TABLES");
    }

    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $tables[] = $row[0];
        }
    }

    $total_tables = count($tables);
    if ($total_tables === 0) {
        send_output("No base tables found in this database.", 'warning');
        return false;
    }

    send_output("Found <strong>{$total_tables} tables</strong> to export.", 'info');

    $manifest = [
        'database'     => $db_identifier_name,
        'exported_at'  => date('Y-m-d H:i:s'),
        'sample_limit' => $limit,
        'total_tables' => $total_tables,
        'tables'       => []
    ];

    $success_count = 0;

    foreach ($tables as $idx => $table) {
        $seq = $idx + 1;
        $sql_file = $out_dir . DIRECTORY_SEPARATOR . $table . '.sql';

        // 1. Get CREATE TABLE
        $create_res = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
        if (!$create_res) {
            send_output("#{$seq}/{$total_tables} [ERROR] Failed to get schema for `{$table}`: " . mysqli_error($conn), 'error');
            continue;
        }
        $create_row = mysqli_fetch_row($create_res);
        $create_table_sql = $create_row[1] ?? '';

        // 2. Determine Primary Key / Auto Increment / Date column for latest records
        $primary_key = null;
        $pk_res = mysqli_query($conn, "SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        if ($pk_res && $pk_row = mysqli_fetch_assoc($pk_res)) {
            $primary_key = $pk_row['Column_name'];
        }

        // If no primary key, check for auto_increment column
        if (!$primary_key) {
            $col_res = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` WHERE Extra LIKE '%auto_increment%'");
            if ($col_res && $c_row = mysqli_fetch_assoc($col_res)) {
                $primary_key = $c_row['Field'];
            }
        }

        // If still none, check for common timestamp or id columns
        if (!$primary_key) {
            $col_res = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}`");
            if ($col_res) {
                $candidates = ['id', 'srno', 'sr_no', 'sno', 'entry_date', 'created_at', 'cdate', 'date'];
                $all_cols = [];
                while ($c_row = mysqli_fetch_assoc($col_res)) {
                    $all_cols[] = $c_row['Field'];
                }
                foreach ($candidates as $cand) {
                    if (in_array($cand, $all_cols, true)) {
                        $primary_key = $cand;
                        break;
                    }
                }
            }
        }

        // Count total rows in table
        $total_rows_in_db = 0;
        $cnt_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `{$table}`");
        if ($cnt_res && $cnt_row = mysqli_fetch_assoc($cnt_res)) {
            $total_rows_in_db = (int)$cnt_row['cnt'];
        }

        // 3. Query sample data
        if ($primary_key) {
            $data_query = "SELECT * FROM `{$table}` ORDER BY `{$primary_key}` DESC LIMIT {$limit}";
        } else {
            $data_query = "SELECT * FROM `{$table}` LIMIT {$limit}";
        }

        $data_res = mysqli_query($conn, $data_query);
        $rows_data = [];
        $columns_list = [];

        if ($data_res) {
            // Get column names
            $fields = mysqli_fetch_fields($data_res);
            foreach ($fields as $field) {
                $columns_list[] = $field->name;
            }

            while ($r = mysqli_fetch_assoc($data_res)) {
                $rows_data[] = $r;
            }
        }

        // Reverse so that SQL file inserts records in chronological order
        if ($primary_key && !empty($rows_data)) {
            $rows_data = array_reverse($rows_data);
        }

        $sample_count = count($rows_data);

        // 4. Build SQL file content
        $sql_buffer = "-- ========================================================\n";
        $sql_buffer .= "-- AVO ERP Table Export: `{$table}`\n";
        $sql_buffer .= "-- Database: {$db_identifier_name}\n";
        $sql_buffer .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql_buffer .= "-- Total Table Rows in DB: {$total_rows_in_db}\n";
        $sql_buffer .= "-- Sample Records Exported: {$sample_count} (Max Limit: {$limit})\n";
        $sql_buffer .= "-- Sort Column: " . ($primary_key ? "`{$primary_key}` DESC" : "Default Natural Order") . "\n";
        $sql_buffer .= "-- ========================================================\n\n";

        $sql_buffer .= "SET NAMES utf8mb4;\n";
        $sql_buffer .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $sql_buffer .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
        $sql_buffer .= $create_table_sql . ";\n\n";

        if ($sample_count > 0 && !empty($columns_list)) {
            $sql_buffer .= "-- --------------------------------------------------------\n";
            $sql_buffer .= "-- Dumping latest {$sample_count} records for table `{$table}`\n";
            $sql_buffer .= "-- --------------------------------------------------------\n\n";

            $escaped_cols = array_map(function($col) {
                return '`' . str_replace('`', '``', $col) . '`';
            }, $columns_list);
            $cols_header = implode(', ', $escaped_cols);

            $batch_size = 50;
            $chunks = array_chunk($rows_data, $batch_size);

            foreach ($chunks as $chunk) {
                $value_lines = [];
                foreach ($chunk as $row) {
                    $val_items = [];
                    foreach ($columns_list as $col) {
                        $val = $row[$col];
                        if ($val === null) {
                            $val_items[] = 'NULL';
                        } elseif (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                            // Keep numeric unless leading zero like phone numbers or pin codes
                            $val_items[] = $val;
                        } else {
                            $escaped = mysqli_real_escape_string($conn, (string)$val);
                            $val_items[] = "'" . $escaped . "'";
                        }
                    }
                    $value_lines[] = "(" . implode(', ', $val_items) . ")";
                }

                $sql_buffer .= "INSERT INTO `{$table}` ({$cols_header}) VALUES\n";
                $sql_buffer .= implode(",\n", $value_lines) . ";\n\n";
            }
        } else {
            $sql_buffer .= "-- (Table has no records or no sample data selected)\n\n";
        }

        $sql_buffer .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        // Save to file
        file_put_contents($sql_file, $sql_buffer);
        $filesize_kb = round(filesize($sql_file) / 1024, 2);

        $manifest['tables'][] = [
            'table_name'    => $table,
            'file_name'     => $table . '.sql',
            'file_size_kb'  => $filesize_kb,
            'db_total_rows' => $total_rows_in_db,
            'sample_rows'   => $sample_count,
            'sort_column'   => $primary_key
        ];

        $success_count++;
        send_output("#{$seq}/{$total_tables} Table <strong>`{$table}`</strong> &rarr; <code>{$table}.sql</code> ({$sample_count} rows of {$total_rows_in_db} total, {$filesize_kb} KB)", 'success');
    }

    // Write manifest JSON and README.txt
    file_put_contents($out_dir . DIRECTORY_SEPARATOR . '_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

    $readme = "AVO ERP - SQL Reference Export\n";
    $readme .= "==============================\n";
    $readme .= "Database: {$db_identifier_name}\n";
    $readme .= "Exported At: " . date('Y-m-d H:i:s') . "\n";
    $readme .= "Total Tables Exported: {$success_count} / {$total_tables}\n";
    $readme .= "Max Sample Limit: {$limit} rows per table\n\n";
    $readme .= str_pad("#", 5) . str_pad("Table Name", 35) . str_pad("DB Total", 12) . str_pad("Sample Rows", 14) . "File Size\n";
    $readme .= str_repeat("-", 75) . "\n";

    foreach ($manifest['tables'] as $i => $tbl) {
        $readme .= str_pad(($i + 1), 5) .
                   str_pad($tbl['table_name'], 35) .
                   str_pad($tbl['db_total_rows'], 12) .
                   str_pad($tbl['sample_rows'], 14) .
                   $tbl['file_size_kb'] . " KB\n";
    }
    file_put_contents($out_dir . DIRECTORY_SEPARATOR . 'README.txt', $readme);

    // Create ZIP archive if ZipArchive extension is available
    $zip_created = false;
    $zip_file = $out_dir . '.zip';
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = glob($out_dir . DIRECTORY_SEPARATOR . '*.sql');
            foreach ($files as $f) {
                $zip->addFile($f, basename($out_dir) . '/' . basename($f));
            }
            if (file_exists($out_dir . DIRECTORY_SEPARATOR . '_manifest.json')) {
                $zip->addFile($out_dir . DIRECTORY_SEPARATOR . '_manifest.json', basename($out_dir) . '/_manifest.json');
            }
            if (file_exists($out_dir . DIRECTORY_SEPARATOR . 'README.txt')) {
                $zip->addFile($out_dir . DIRECTORY_SEPARATOR . 'README.txt', basename($out_dir) . '/README.txt');
            }
            $zip->close();
            $zip_created = true;
        }
    }

    send_output("----------------------------------------------------------------", 'header');
    send_output("EXPORT COMPLETE! Successfully exported <strong>{$success_count} tables</strong> to <code>{$out_dir}</code>", 'success');
    if ($zip_created && file_exists($zip_file)) {
        $zip_size_mb = round(filesize($zip_file) / (1024 * 1024), 2);
        send_output("All tables packaged into ZIP: <strong>" . basename($zip_file) . "</strong> ({$zip_size_mb} MB)", 'success');
    }

    return true;
}

// -------------------------------------------------------------------------
// 3. Execution Router
// -------------------------------------------------------------------------
if ($action === 'export') {
    if (!$is_cli) {
        // Output web page header before flushing output
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Exporting SQL Reference - AVO ERP</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 24px; }
                .container { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 8px; border: 1px solid #334155; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
                h1 { font-size: 18px; margin-top: 0; color: #38bdf8; display: flex; align-items: center; gap: 8px; }
                .console { background: #090d16; border: 1px solid #334155; border-radius: 6px; padding: 16px; max-height: 550px; overflow-y: auto; }
                .btn { display: inline-block; padding: 8px 16px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 500; margin-top: 15px; }
                .btn:hover { background: #1d4ed8; }
            </style>
        </head>
        <body>
        <div class="container">
            <h1>AVO ERP &mdash; Table SQL Exporter</h1>
            <div class="console">
        <?php
    }

    // Determine target connections to export
    $targets_to_run = [];
    if ($selected_target === 'all') {
        foreach ($db_connections as $k => $c) {
            $targets_to_run[$k] = $c;
        }
    } elseif (isset($db_connections[$selected_target])) {
        $targets_to_run[$selected_target] = $db_connections[$selected_target];
    } else {
        // Fallback: try default mysqli connection if available
        $fallback_conn = @mysqli_connect($manual_host, $manual_user, $manual_pass, $manual_name);
        if ($fallback_conn && !mysqli_connect_errno()) {
            $targets_to_run['manual'] = [
                'label' => "Manual DB ({$manual_name})",
                'conn'  => $fallback_conn,
                'name'  => $manual_name
            ];
        } else {
            $err = mysqli_connect_error();
            send_output("ERROR: Unable to establish database connection to '{$manual_name}' at '{$manual_host}'. MySQL Error: {$err}", 'error');
        }
    }

    foreach ($targets_to_run as $key => $target_info) {
        $conn = $target_info['conn'];
        $db_name = $target_info['name'];

        // If multiple targets, store inside sql_reference/{db_name} or if single target, inside sql_reference/
        if (count($targets_to_run) > 1) {
            $dest_dir = $base_out_dir . DIRECTORY_SEPARATOR . $db_name;
        } else {
            $dest_dir = $base_out_dir;
        }

        export_database_tables($conn, $db_name, $dest_dir, $record_limit);
    }

    if (!$is_cli) {
        ?>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 12px; align-items: center;">
                <a href="export_sql_reference.php" class="btn" style="background: #475569;">&larr; Back to Settings</a>
                <?php if (file_exists($base_out_dir . '.zip')): ?>
                    <a href="sql_reference.zip" download class="btn" style="background: #059669;">&darr; Download sql_reference.zip</a>
                <?php endif; ?>
            </div>
        </div>
        <script>
            // Auto scroll console to bottom
            const c = document.querySelector('.console');
            if (c) c.scrollTop = c.scrollHeight;
        </script>
        </body>
        </html>
        <?php
    }
    exit;
}

// -------------------------------------------------------------------------
// 4. Interactive Browser UI (When accessed without action=export)
// -------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVO ERP - Export Table SQL Reference</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            width: 100%;
            max-width: 680px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            background: #090d16;
            color: #ffffff;
            padding: 24px;
            border-bottom: 1px solid #1e293b;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .content {
            padding: 24px;
        }
        .section {
            margin-bottom: 20px;
        }
        .label {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .radio-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .radio-card:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .radio-card input {
            margin-top: 2px;
            cursor: pointer;
        }
        .radio-card-body h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        .radio-card-body p {
            margin: 2px 0 0 0;
            font-size: 11px;
            color: #64748b;
            font-family: monospace;
        }
        .input-group {
            margin-bottom: 14px;
        }
        .input-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .input-control:focus {
            border-color: #0f172a;
        }
        .btn-submit {
            width: 100%;
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: #1e293b;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-blue { background: #e0e7ff; color: #4338ca; }
        .badge-yellow { background: #fef3c7; color: #b45309; }
        .footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="header">
        <h1>
            <span>&#128196;</span>
            AVO ERP Table SQL Reference Exporter
        </h1>
        <p>
            Generates individual <code>{table_name}.sql</code> files for all tables in the database, containing table schema definition (CREATE TABLE) and up to the latest 100 records.
        </p>
    </div>

    <form method="POST" action="export_sql_reference.php">
        <input type="hidden" name="action" value="export">

        <div class="content">
            <!-- Detected Connections Section -->
            <div class="section">
                <span class="label">Target Database Connection</span>
                <p class="desc">Select the database connection to dump into individual table SQL files:</p>

                <?php if (!empty($db_connections)): ?>
                    <?php foreach ($db_connections as $key => $conn_meta): ?>
                        <label class="radio-card">
                            <input type="radio" name="target" value="<?php echo htmlspecialchars($key); ?>" <?php echo ($key === $selected_target) ? 'checked' : ''; ?>>
                            <div class="radio-card-body">
                                <h4><?php echo htmlspecialchars($conn_meta['label']); ?></h4>
                                <p>Detected from config.php &bull; Status: <span class="badge badge-green">Connected</span></p>
                            </div>
                        </label>
                    <?php endforeach; ?>

                    <?php if (count($db_connections) > 1): ?>
                        <label class="radio-card">
                            <input type="radio" name="target" value="all">
                            <div class="radio-card-body">
                                <h4>Export Both Databases (Service + CRM)</h4>
                                <p>Dumps tables from both databases into separated subfolders.</p>
                            </div>
                        </label>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="padding: 12px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 12px; color: #92400e; margin-bottom: 12px;">
                        <strong>Note:</strong> Could not auto-detect active database connections from <code>config.php</code>. Please enter MySQL connection credentials below:
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="input-group">
                            <span class="label">Host</span>
                            <input type="text" name="db_host" value="<?php echo htmlspecialchars($manual_host); ?>" class="input-control" required>
                        </div>
                        <div class="input-group">
                            <span class="label">Database Name</span>
                            <input type="text" name="db_name" value="<?php echo htmlspecialchars($manual_name); ?>" class="input-control" placeholder="e.g. avoservi_service" required>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="input-group">
                            <span class="label">Username</span>
                            <input type="text" name="db_user" value="<?php echo htmlspecialchars($manual_user); ?>" class="input-control" required>
                        </div>
                        <div class="input-group">
                            <span class="label">Password</span>
                            <input type="password" name="db_pass" value="<?php echo htmlspecialchars($manual_pass); ?>" class="input-control" placeholder="Database password">
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Limit Configuration -->
            <div class="section">
                <span class="label">Sample Records Limit per Table</span>
                <p class="desc">Max number of latest records to dump per table (ordered by primary key / ID / timestamp descending):</p>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="number" name="limit" min="1" max="1000" value="100" class="input-control" style="width: 140px;">
                    <span style="font-size: 12px; color: #64748b;">records (Default: 100 records)</span>
                </div>
            </div>

            <!-- Output Destination Notice -->
            <div class="section" style="padding: 12px; background: #f1f5f9; border-radius: 8px; font-size: 12px; color: #475569;">
                <strong>Output Folder:</strong> <code>./sql_reference/</code><br>
                Each table will be saved as <code>./sql_reference/{table_name}.sql</code> along with a <code>_manifest.json</code> and optional <code>sql_reference.zip</code> for fast 1-click download.
            </div>

            <button type="submit" class="btn-submit">
                <span>&rarr;</span> Start Table SQL Export
            </button>
        </div>
    </form>

    <div class="footer">
        <span>AVO ERP Database Utilities</span>
        <span>Output format: UTF-8 / utf8mb4 SQL</span>
    </div>
</div>

</body>
</html>
