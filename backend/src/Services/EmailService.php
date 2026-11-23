<?php

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use PDO;
use Exception;
use InvalidArgumentException;

class EmailService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // -------------------------------------------------------------------------
    // EMAIL SETTINGS (CREDENTIALS)
    // -------------------------------------------------------------------------

    public function getSettings(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM email_settings ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['title'] = $r['mail_from_name'] . ' (' . $r['mail_host'] . ')';
            $r['smtp_host'] = $r['mail_host'];
            $r['smtp_port'] = (int)$r['mail_port'];
            $r['smtp_username'] = $r['mail_username'];
            $r['smtp_encryption'] = $r['mail_encryption'];
            $r['from_name'] = $r['mail_from_name'];
            $r['from_email'] = $r['mail_from_address'];
            $r['reply_to'] = $r['reply_to_address'];
            return $r;
        }, $rows);
    }

    public function getSettingById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM email_settings WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['title'] = $row['mail_from_name'] . ' (' . $row['mail_host'] . ')';
        $row['smtp_host'] = $row['mail_host'];
        $row['smtp_port'] = (int)$row['mail_port'];
        $row['smtp_username'] = $row['mail_username'];
        $row['smtp_encryption'] = $row['mail_encryption'];
        $row['from_name'] = $row['mail_from_name'];
        $row['from_email'] = $row['mail_from_address'];
        $row['reply_to'] = $row['reply_to_address'];
        return $row;
    }

    public function saveSetting(array $data, array $actor): array
    {
        $host = trim($data['mail_host'] ?? $data['smtp_host'] ?? '');
        $username = trim($data['mail_username'] ?? $data['smtp_username'] ?? '');

        if (empty($host) || empty($username)) {
            throw new InvalidArgumentException("SMTP Host and Username/Email are required.");
        }

        $driver = $data['mail_driver'] ?? 'smtp';
        $port = (int)($data['mail_port'] ?? $data['smtp_port'] ?? 587);
        $password = $data['mail_password'] ?? $data['smtp_password'] ?? '';
        $encryption = $data['mail_encryption'] ?? $data['smtp_encryption'] ?? 'tls';
        $fromAddress = trim($data['mail_from_address'] ?? $data['from_email'] ?? $username);
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid sender email address: '{$fromAddress}'");
        }
        $fromName = trim($data['mail_from_name'] ?? $data['from_name'] ?? 'AVO Industrial ERP');
        $replyToVal = $data['reply_to_address'] ?? $data['reply_to'] ?? null;
        $replyTo = !empty($replyToVal) ? trim($replyToVal) : null;
        if ($replyTo && !filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid reply-to email address: '{$replyTo}'");
        }
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($isActive === 1) {
            // Set all other settings to inactive
            $this->pdo->exec("UPDATE email_settings SET is_active = 0");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO email_settings (
                mail_driver, mail_host, mail_port, mail_username, mail_password,
                mail_encryption, mail_from_address, mail_from_name, reply_to_address, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $driver, $host, $port, $username, $password,
            $encryption, $fromAddress, $fromName, $replyTo, $isActive
        ]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log(
            $actor['id'] ?? 1,
            $actor['name'] ?? 'Admin',
            $actor['role'] ?? 'admin',
            'CREATE_EMAIL_SETTINGS',
            'EmailSettings',
            (string)$id,
            null,
            ['host' => $host, 'from' => $fromAddress]
        );

        return $this->getSettingById($id);
    }

    public function updateSetting(int $id, array $data, array $actor): array
    {
        $existing = $this->getSettingById($id);
        if (!$existing) {
            throw new InvalidArgumentException("Email configuration with ID {$id} not found.");
        }

        $driver = $data['mail_driver'] ?? $existing['mail_driver'];
        $host = trim($data['mail_host'] ?? $data['smtp_host'] ?? $existing['mail_host']);
        $port = (int)($data['mail_port'] ?? $data['smtp_port'] ?? $existing['mail_port']);
        $username = trim($data['mail_username'] ?? $data['smtp_username'] ?? $existing['mail_username']);
        $updatePassword = $data['mail_password'] ?? $data['smtp_password'] ?? null;
        $password = !empty($updatePassword) ? $updatePassword : $existing['mail_password'];
        $encryption = $data['mail_encryption'] ?? $data['smtp_encryption'] ?? $existing['mail_encryption'];
        $fromAddress = trim($data['mail_from_address'] ?? $data['from_email'] ?? $existing['mail_from_address']);
        $fromName = trim($data['mail_from_name'] ?? $data['from_name'] ?? $existing['mail_from_name']);
        $updateReplyTo = $data['reply_to_address'] ?? $data['reply_to'] ?? null;
        $replyTo = $updateReplyTo !== null ? (!empty($updateReplyTo) ? trim($updateReplyTo) : null) : $existing['reply_to_address'];
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : $existing['is_active'];

        if ($isActive === 1 && $existing['is_active'] !== 1) {
            $this->pdo->exec("UPDATE email_settings SET is_active = 0");
        }

        $stmt = $this->pdo->prepare("
            UPDATE email_settings
            SET mail_driver = ?, mail_host = ?, mail_port = ?, mail_username = ?,
                mail_password = ?, mail_encryption = ?, mail_from_address = ?,
                mail_from_name = ?, reply_to_address = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $driver, $host, $port, $username, $password,
            $encryption, $fromAddress, $fromName, $replyTo, $isActive, $id
        ]);

        AuditLog::log(
            $actor['id'] ?? 1,
            $actor['name'] ?? 'Admin',
            $actor['role'] ?? 'admin',
            'UPDATE_EMAIL_SETTINGS',
            'EmailSettings',
            (string)$id,
            $existing,
            ['host' => $host, 'is_active' => $isActive]
        );

        return $this->getSettingById($id);
    }

    public function deleteSetting(int $id, array $actor): bool
    {
        $existing = $this->getSettingById($id);
        if (!$existing) {
            throw new InvalidArgumentException("Email configuration not found.");
        }

        $stmt = $this->pdo->prepare("DELETE FROM email_settings WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'] ?? 'admin',
            'DELETE_EMAIL_SETTINGS',
            'EmailSettings',
            (string)$id,
            $existing,
            null
        );

        return true;
    }

    public function testConnection(array $params): array
    {
        $testEmail = $params['test_email'] ?? $params['test_recipient'] ?? null;
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Valid test recipient email address is required.");
        }

        $configId = $params['setting_id'] ?? null;
        $config = null;
        if ($configId) {
            $config = $this->getSettingById((int)$configId);
        } else {
            // Find active setting
            $stmt = $this->pdo->query("SELECT * FROM email_settings WHERE is_active = 1 LIMIT 1");
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$config) {
            throw new InvalidArgumentException("No active email credentials configured to perform test.");
        }

        // Test socket connection to SMTP Host:Port
        $host = $config['mail_host'];
        $port = (int)$config['mail_port'];
        $timeout = 6;
        $errno = 0;
        $errstr = '';

        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return [
                'success' => false,
                'message' => "Connection to {$host}:{$port} failed: {$errstr} ({$errno})",
                'diagnostics' => [
                    'host' => $host,
                    'port' => $port,
                    'error' => $errstr,
                ],
            ];
        }
        fclose($fp);

        return [
            'success' => true,
            'message' => "Successfully connected to {$host}:{$port} ({$config['mail_encryption']}). Test dispatch simulated to {$testEmail}.",
            'details' => [
                'smtp_server' => $host,
                'port' => $port,
                'encryption' => $config['mail_encryption'],
                'sender' => $config['mail_from_address'],
                'recipient' => $testEmail,
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // EMAIL TEMPLATES
    // -------------------------------------------------------------------------

    public function getTemplates(array $filters = []): array
    {
        $sql = "SELECT * FROM email_templates WHERE 1=1";
        $params = [];

        if (!empty($filters['category']) && $filters['category'] !== 'ALL') {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (template_name LIKE ? OR template_key LIKE ? OR subject LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTemplateById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM email_templates WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createTemplate(array $data, array $actor): array
    {
        if (empty($data['template_name']) || empty($data['template_key']) || empty($data['subject']) || empty($data['body_html'])) {
            throw new InvalidArgumentException("Template Name, Unique Key, Subject and Body are required.");
        }

        $key = strtoupper(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($data['template_key'])));
        $name = trim($data['template_name']);
        $subject = trim($data['subject']);
        $body = $data['body_html'];
        $vars = trim($data['available_variables'] ?? '{{name}}, {{email}}');
        $cat = trim($data['category'] ?? 'General');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Check key uniqueness
        $chk = $this->pdo->prepare("SELECT COUNT(*) FROM email_templates WHERE template_key = ?");
        $chk->execute([$key]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new InvalidArgumentException("Template key '{$key}' already exists.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO email_templates (template_key, template_name, subject, body_html, available_variables, category, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$key, $name, $subject, $body, $vars, $cat, $isActive]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'] ?? 'admin',
            'CREATE_EMAIL_TEMPLATE',
            'EmailTemplate',
            (string)$id,
            null,
            ['key' => $key, 'name' => $name]
        );

        return $this->getTemplateById($id);
    }

    public function updateTemplate(int $id, array $data, array $actor): array
    {
        $existing = $this->getTemplateById($id);
        if (!$existing) {
            throw new InvalidArgumentException("Template with ID {$id} not found.");
        }

        $name = trim($data['template_name'] ?? $existing['template_name']);
        $subject = trim($data['subject'] ?? $existing['subject']);
        $body = $data['body_html'] ?? $existing['body_html'];
        $vars = trim($data['available_variables'] ?? $existing['available_variables']);
        $cat = trim($data['category'] ?? $existing['category']);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : $existing['is_active'];

        $stmt = $this->pdo->prepare("
            UPDATE email_templates
            SET template_name = ?, subject = ?, body_html = ?, available_variables = ?, category = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $subject, $body, $vars, $cat, $isActive, $id]);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'] ?? 'admin',
            'UPDATE_EMAIL_TEMPLATE',
            'EmailTemplate',
            (string)$id,
            $existing,
            ['name' => $name, 'subject' => $subject]
        );

        return $this->getTemplateById($id);
    }

    public function deleteTemplate(int $id, array $actor): bool
    {
        $existing = $this->getTemplateById($id);
        if (!$existing) {
            throw new InvalidArgumentException("Template with ID {$id} not found.");
        }

        $stmt = $this->pdo->prepare("DELETE FROM email_templates WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log(
            $actor['id'],
            $actor['name'],
            $actor['role'] ?? 'admin',
            'DELETE_EMAIL_TEMPLATE',
            'EmailTemplate',
            (string)$id,
            $existing,
            null
        );

        return true;
    }
}
