<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static ?string $driver = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::loadEnv();

            $driver = getenv('DB_CONNECTION') ?: 'mysql';

            if ($driver === 'mysql') {
                $host = getenv('DB_HOST') ?: '127.0.0.1';
                $port = getenv('DB_PORT') ?: '3306';
                $dbName = getenv('DB_DATABASE') ?: 'avo_erp';
                $user = getenv('DB_USERNAME') ?: 'root';
                $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

                try {
                    // 1. Ensure the MySQL database exists
                    $adminPdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]);
                    $adminPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

                    // 2. Connect to the specific database
                    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    self::$driver = 'mysql';
                } catch (PDOException $e) {
                    // Fallback to SQLite if MySQL service is unavailable
                    self::$instance = self::createSqliteConnection();
                    self::$driver = 'sqlite';
                }
            } else {
                self::$instance = self::createSqliteConnection();
                self::$driver = 'sqlite';
            }

            self::initializeSchema(self::$instance, self::$driver);
        }

        return self::$instance;
    }

    public static function getDriver(): string
    {
        if (self::$driver === null) {
            self::getConnection();
        }
        return self::$driver ?? 'mysql';
    }

    public static function setConnection(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    private static function loadEnv(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, '#') || empty($line)) {
                    continue;
                }
                if (strpos($line, '=') !== false) {
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v);
                    putenv("{$k}={$v}");
                    $_ENV[$k] = $v;
                    $_SERVER[$k] = $v;
                }
            }
        }
    }

    private static function createSqliteConnection(): PDO
    {
        $dbDir = dirname(__DIR__, 2) . '/database';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }
        $dbPath = $dbDir . '/erp.sqlite';
        $pdo = new PDO("sqlite:{$dbPath}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        return $pdo;
    }

    public static function initializeSchema(PDO $pdo, string $driver = 'mysql'): void
    {
        $pk = ($driver === 'mysql') ? 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
        $timeUpdated = ($driver === 'mysql') 
            ? 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP' 
            : 'DATETIME DEFAULT CURRENT_TIMESTAMP';

        $schema = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id {$pk},
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'sales_rep',
    role_type VARCHAR(50) NOT NULL DEFAULT 'Sales',
    scope VARCHAR(20) NOT NULL DEFAULT 'own',
    level VARCHAR(10) NOT NULL DEFAULT 'L1',
    designation VARCHAR(100) DEFAULT 'Sales Executive',
    region VARCHAR(50) DEFAULT 'North',
    branch VARCHAR(100) DEFAULT 'HQ Hub',
    department VARCHAR(100) DEFAULT 'Commercial Sales',
    reporting_manager_id INT DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id {$pk},
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    category VARCHAR(100) NOT NULL,
    cost_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at {$timeUpdated}
);

CREATE TABLE IF NOT EXISTS customers (
    id {$pk},
    name VARCHAR(150) NOT NULL,
    company VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT NULL,
    tax_number VARCHAR(50) DEFAULT NULL,
    credit_limit DECIMAL(12, 2) NOT NULL DEFAULT 5000.00,
    outstanding_balance DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    address TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id {$pk},
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_by_id INT NOT NULL,
    created_by_name VARCHAR(100) NOT NULL,
    approved_by_id INT DEFAULT NULL,
    approved_by_name VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at {$timeUpdated}
);

CREATE TABLE IF NOT EXISTS order_items (
    id {$pk},
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) NOT NULL,
    tax_amount DECIMAL(12, 2) NOT NULL,
    line_total DECIMAL(12, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS invoices (
    id {$pk},
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    order_id INT NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    tax_amount DECIMAL(12, 2) NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    checksum VARCHAR(64) NOT NULL,
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id {$pk},
    user_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT 'System',
    user_role VARCHAR(50) DEFAULT 'system',
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    old_values TEXT DEFAULT NULL,
    new_values TEXT DEFAULT NULL,
    ip_address VARCHAR(50) DEFAULT '127.0.0.1',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rfp_bids (
    id {$pk},
    rfp_number VARCHAR(100) NOT NULL,
    normalized_rfp VARCHAR(100) NOT NULL,
    rfp_title VARCHAR(200) NOT NULL,
    end_client VARCHAR(150) NOT NULL,
    integrator_name VARCHAR(150) NOT NULL,
    salesperson_id INT NOT NULL,
    salesperson_name VARCHAR(100) NOT NULL,
    quote_amount DECIMAL(12, 2) NOT NULL,
    cost_amount DECIMAL(12, 2) NOT NULL,
    est_margin_pct DECIMAL(5, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'submitted',
    silent_link_id VARCHAR(64) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scrap_ledger (
    id {$pk},
    material_type VARCHAR(100) NOT NULL,
    weight_kg DECIMAL(10, 2) NOT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    defect_reason TEXT NOT NULL,
    warehouse_branch VARCHAR(100) NOT NULL DEFAULT 'Central Storage Hub',
    operator_id INT NOT NULL,
    operator_name VARCHAR(100) NOT NULL,
    est_recovery_value DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permission_matrix (
    id {$pk},
    role_type VARCHAR(50) NOT NULL,
    min_level VARCHAR(10) NOT NULL DEFAULT 'L1',
    module_key VARCHAR(50) NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    can_view INT NOT NULL DEFAULT 1,
    can_create INT NOT NULL DEFAULT 0,
    can_edit INT NOT NULL DEFAULT 0,
    can_approve INT NOT NULL DEFAULT 0,
    can_delete INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS sales_leads (
    id {$pk},
    lead_number VARCHAR(30) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_email VARCHAR(120),
    contact_phone VARCHAR(25) NOT NULL,
    city VARCHAR(50),
    state VARCHAR(50),
    region VARCHAR(50) DEFAULT 'North',
    sale_type VARCHAR(50) NOT NULL DEFAULT 'End_Customer',
    product_class VARCHAR(100),
    estimated_value DECIMAL(14,2) DEFAULT 0.00,
    source VARCHAR(50) DEFAULT 'Direct Inquiry',
    assigned_to_user_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'New',
    lost_reason VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at {$timeUpdated}
);

CREATE TABLE IF NOT EXISTS sales_visits (
    id {$pk},
    lead_id INT NULL,
    customer_name VARCHAR(150) NOT NULL,
    salesperson_id INT NOT NULL,
    visit_date DATE NOT NULL,
    check_in_time DATETIME NOT NULL,
    check_out_time DATETIME NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    location_name VARCHAR(150) NULL,
    contact_person_met VARCHAR(100) NOT NULL,
    discussion_notes TEXT,
    next_follow_up DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sales_quotations (
    id {$pk},
    quote_number VARCHAR(35) NOT NULL UNIQUE,
    lead_id INT NULL,
    customer_name VARCHAR(150) NOT NULL,
    sale_type VARCHAR(50) NOT NULL DEFAULT 'End_Customer',
    salesperson_id INT NOT NULL,
    version VARCHAR(10) DEFAULT 'v1.0',
    subtotal_amount DECIMAL(14,2) NOT NULL,
    discount_amount DECIMAL(14,2) DEFAULT 0.00,
    total_cost_price DECIMAL(14,2) NOT NULL,
    total_msp_floor DECIMAL(14,2) NOT NULL,
    final_quote_amount DECIMAL(14,2) NOT NULL,
    gross_margin_percent DECIMAL(6,2) NOT NULL,
    is_below_msp TINYINT(1) DEFAULT 0,
    msp_shortfall_amount DECIMAL(14,2) DEFAULT 0.00,
    approval_status VARCHAR(50) DEFAULT 'Draft',
    approved_by_user_id INT NULL,
    approval_remarks TEXT NULL,
    valid_until DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quotation_items (
    id {$pk},
    quotation_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_sku VARCHAR(60),
    quantity INT NOT NULL DEFAULT 1,
    unit_cost_price DECIMAL(12,2) NOT NULL,
    unit_msp_floor DECIMAL(12,2) NOT NULL,
    quoted_unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(14,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS tender_register (
    id {$pk},
    tender_ref_no VARCHAR(80) NOT NULL UNIQUE,
    issuing_authority VARCHAR(150) NOT NULL,
    authority_type VARCHAR(50) NOT NULL DEFAULT 'PSU',
    tender_title VARCHAR(255) NOT NULL,
    estimated_value DECIMAL(15,2) NOT NULL,
    published_date DATE NOT NULL,
    bid_submission_deadline DATETIME NOT NULL,
    quote_strategy VARCHAR(50) NOT NULL DEFAULT 'Direct_AVO',
    allocated_dealer_name VARCHAR(150) NULL,
    assigned_officer_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Identified',
    l1_vendor_name VARCHAR(150) NULL,
    l1_price DECIMAL(15,2) NULL,
    avo_quoted_price DECIMAL(15,2) NULL,
    outcome_remarks TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tender_guarantees (
    id {$pk},
    tender_id INT NOT NULL,
    guarantee_type VARCHAR(50) NOT NULL DEFAULT 'EMD_BG',
    amount DECIMAL(14,2) NOT NULL,
    issuing_bank VARCHAR(100) NOT NULL,
    bg_number VARCHAR(80),
    effective_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    claim_expiry_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Active',
    release_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS si_rfp_clusters (
    id {$pk},
    cluster_code VARCHAR(40) NOT NULL UNIQUE,
    end_client_name VARCHAR(150) NOT NULL,
    project_scope VARCHAR(255) NOT NULL,
    est_total_deal_value DECIMAL(15,2) NOT NULL,
    cluster_status VARCHAR(50) DEFAULT 'Active_Bidding',
    winning_si_name VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS si_rfp_bids (
    id {$pk},
    cluster_id INT NOT NULL,
    si_company_name VARCHAR(150) NOT NULL,
    quotation_id INT NULL,
    salesperson_name VARCHAR(100) NOT NULL,
    quoted_amount DECIMAL(14,2) NOT NULL,
    bid_status VARCHAR(50) DEFAULT 'Submitted'
);

CREATE TABLE IF NOT EXISTS email_settings (
    id {$pk},
    mail_driver VARCHAR(50) NOT NULL DEFAULT 'smtp',
    mail_host VARCHAR(255) NOT NULL,
    mail_port INT NOT NULL DEFAULT 587,
    mail_username VARCHAR(255) NOT NULL,
    mail_password VARCHAR(255) NOT NULL,
    mail_encryption VARCHAR(20) NOT NULL DEFAULT 'tls',
    mail_from_address VARCHAR(255) NOT NULL,
    mail_from_name VARCHAR(255) NOT NULL DEFAULT 'AVO Industrial ERP',
    reply_to_address VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_templates (
    id {$pk},
    template_key VARCHAR(100) NOT NULL UNIQUE,
    template_name VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html LONGTEXT NOT NULL,
    available_variables VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'General',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS countries (
    id {$pk},
    country_name VARCHAR(100) NOT NULL UNIQUE,
    country_code VARCHAR(10) NOT NULL UNIQUE,
    phone_code VARCHAR(10) NOT NULL,
    currency_code VARCHAR(10) NOT NULL DEFAULT 'INR',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS states (
    id {$pk},
    country_id INT NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    state_code VARCHAR(10) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_country_state (country_id, state_name)
);

CREATE TABLE IF NOT EXISTS cities (
    id {$pk},
    state_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    pincode_prefix VARCHAR(10) NULL,
    tier VARCHAR(20) NOT NULL DEFAULT 'Tier-2',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_state_city (state_id, city_name)
);

CREATE TABLE IF NOT EXISTS zones (
    id {$pk},
    zone_name VARCHAR(100) NOT NULL UNIQUE,
    zone_code VARCHAR(20) NOT NULL UNIQUE,
    regional_hub VARCHAR(150) NULL,
    description VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS couriers (
    id {$pk},
    courier_name VARCHAR(100) NOT NULL UNIQUE,
    courier_code VARCHAR(50) NOT NULL UNIQUE,
    tracking_url_template VARCHAR(255) NULL,
    contact_person VARCHAR(100) NULL,
    contact_phone VARCHAR(50) NULL,
    contact_email VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
SQL;

        // Split by statement and execute safely
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }
    }
}
