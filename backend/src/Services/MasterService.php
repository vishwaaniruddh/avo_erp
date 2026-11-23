<?php

namespace App\Services;

use App\Core\Database;
use App\Models\AuditLog;
use PDO;
use Exception;
use InvalidArgumentException;

class MasterService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // -------------------------------------------------------------------------
    // 1. COUNTRIES MASTER
    // -------------------------------------------------------------------------

    public function getCountries(array $filters = []): array
    {
        $sql = "SELECT * FROM countries WHERE 1=1";
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (country_name LIKE ? OR country_code LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY country_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['name'] = $r['country_name'];
            $r['code'] = $r['country_code'];
            $r['currency'] = $r['currency_code'];
            $r['is_active'] = $r['status'] === 'active' ? 1 : 0;
            return $r;
        }, $rows);
    }

    public function getCountryById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['name'] = $row['country_name'];
        $row['code'] = $row['country_code'];
        $row['currency'] = $row['currency_code'];
        $row['is_active'] = $row['status'] === 'active' ? 1 : 0;
        return $row;
    }

    public function createCountry(array $data, array $actor): array
    {
        $name = trim($data['country_name'] ?? $data['name'] ?? '');
        $code = strtoupper(trim($data['country_code'] ?? $data['code'] ?? ''));
        $phone = trim($data['phone_code'] ?? '+91');
        $curr = strtoupper(trim($data['currency_code'] ?? $data['currency'] ?? 'INR'));
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? 'active');

        if (empty($name) || empty($code)) {
            throw new InvalidArgumentException("Country Name and Code are required.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO countries (country_name, country_code, phone_code, currency_code, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $code, $phone, $curr, $status]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'CREATE_COUNTRY', 'Country', (string)$id, null, ['name' => $name]);
        return $this->getCountryById($id);
    }

    public function updateCountry(int $id, array $data, array $actor): array
    {
        $existing = $this->getCountryById($id);
        if (!$existing) throw new InvalidArgumentException("Country not found");

        $name = trim($data['country_name'] ?? $data['name'] ?? $existing['country_name']);
        $code = strtoupper(trim($data['country_code'] ?? $data['code'] ?? $existing['country_code']));
        $phone = trim($data['phone_code'] ?? $existing['phone_code']);
        $curr = strtoupper(trim($data['currency_code'] ?? $data['currency'] ?? $existing['currency_code']));
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? $existing['status']);

        $stmt = $this->pdo->prepare("
            UPDATE countries SET country_name = ?, country_code = ?, phone_code = ?, currency_code = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $code, $phone, $curr, $status, $id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'UPDATE_COUNTRY', 'Country', (string)$id, $existing, ['name' => $name]);
        return $this->getCountryById($id);
    }

    public function deleteCountry(int $id, array $actor): bool
    {
        $existing = $this->getCountryById($id);
        if (!$existing) throw new InvalidArgumentException("Country not found");

        // Check state dependency
        $chk = $this->pdo->prepare("SELECT COUNT(*) FROM states WHERE country_id = ?");
        $chk->execute([$id]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new InvalidArgumentException("Cannot delete country: dependent states exist.");
        }

        $stmt = $this->pdo->prepare("DELETE FROM countries WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log($actor['id'], $actor['name'], $actor['role'] ?? 'admin', 'DELETE_COUNTRY', 'Country', (string)$id, $existing, null);
        return true;
    }

    // -------------------------------------------------------------------------
    // 2. STATES MASTER
    // -------------------------------------------------------------------------

    public function getStates(array $filters = []): array
    {
        $sql = "
            SELECT s.*, c.country_name, c.country_code
            FROM states s
            JOIN countries c ON s.country_id = c.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($filters['country_id']) && $filters['country_id'] !== 'ALL') {
            $sql .= " AND s.country_id = ?";
            $params[] = (int)$filters['country_id'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (s.state_name LIKE ? OR s.state_code LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY s.state_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['name'] = $r['state_name'];
            $r['code'] = $r['state_code'];
            $r['is_active'] = $r['status'] === 'active' ? 1 : 0;
            return $r;
        }, $rows);
    }

    public function getStateById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*, c.country_name, c.country_code
            FROM states s
            JOIN countries c ON s.country_id = c.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['name'] = $row['state_name'];
        $row['code'] = $row['state_code'];
        $row['is_active'] = $row['status'] === 'active' ? 1 : 0;
        return $row;
    }

    public function createState(array $data, array $actor): array
    {
        $name = trim($data['state_name'] ?? $data['name'] ?? '');
        $countryId = (int)($data['country_id'] ?? 1);
        $code = !empty($data['state_code'] ?? $data['code']) ? strtoupper(trim($data['state_code'] ?? $data['code'])) : null;
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? 'active');

        if (empty($name) || empty($countryId)) {
            throw new InvalidArgumentException("State Name and Country are required.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO states (country_id, state_name, state_code, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$countryId, $name, $code, $status]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'CREATE_STATE', 'State', (string)$id, null, ['name' => $name]);
        return $this->getStateById($id);
    }

    public function updateState(int $id, array $data, array $actor): array
    {
        $existing = $this->getStateById($id);
        if (!$existing) throw new InvalidArgumentException("State not found");

        $countryId = isset($data['country_id']) ? (int)$data['country_id'] : $existing['country_id'];
        $name = trim($data['state_name'] ?? $data['name'] ?? $existing['state_name']);
        $code = array_key_exists('state_code', $data) || array_key_exists('code', $data)
            ? strtoupper(trim($data['state_code'] ?? $data['code'] ?? ''))
            : $existing['state_code'];
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? $existing['status']);

        $stmt = $this->pdo->prepare("
            UPDATE states SET country_id = ?, state_name = ?, state_code = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$countryId, $name, $code, $status, $id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'UPDATE_STATE', 'State', (string)$id, $existing, ['name' => $name]);
        return $this->getStateById($id);
    }

    public function deleteState(int $id, array $actor): bool
    {
        $existing = $this->getStateById($id);
        if (!$existing) throw new InvalidArgumentException("State not found");

        // Check city dependency
        $chk = $this->pdo->prepare("SELECT COUNT(*) FROM cities WHERE state_id = ?");
        $chk->execute([$id]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new InvalidArgumentException("Cannot delete state: dependent cities exist.");
        }

        $stmt = $this->pdo->prepare("DELETE FROM states WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log($actor['id'], $actor['name'], $actor['role'] ?? 'admin', 'DELETE_STATE', 'State', (string)$id, $existing, null);
        return true;
    }

    // -------------------------------------------------------------------------
    // 3. CITIES MASTER
    // -------------------------------------------------------------------------

    public function getCities(array $filters = []): array
    {
        $sql = "
            SELECT ct.*, s.state_name, s.state_code, c.country_name, c.country_code
            FROM cities ct
            JOIN states s ON ct.state_id = s.id
            JOIN countries c ON s.country_id = c.id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($filters['state_id']) && $filters['state_id'] !== 'ALL') {
            $sql .= " AND ct.state_id = ?";
            $params[] = (int)$filters['state_id'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $sql .= " AND ct.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['tier']) && $filters['tier'] !== 'ALL') {
            $sql .= " AND ct.tier = ?";
            $params[] = $filters['tier'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (ct.city_name LIKE ? OR ct.pincode_prefix LIKE ? OR s.state_name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY ct.city_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['name'] = $r['city_name'];
            $r['pincode'] = $r['pincode_prefix'];
            $r['is_active'] = $r['status'] === 'active' ? 1 : 0;
            return $r;
        }, $rows);
    }

    public function getCityById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ct.*, s.state_name, s.state_code, c.country_name, c.country_code
            FROM cities ct
            JOIN states s ON ct.state_id = s.id
            JOIN countries c ON s.country_id = c.id
            WHERE ct.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['name'] = $row['city_name'];
        $row['pincode'] = $row['pincode_prefix'];
        $row['is_active'] = $row['status'] === 'active' ? 1 : 0;
        return $row;
    }

    public function createCity(array $data, array $actor): array
    {
        $name = trim($data['city_name'] ?? $data['name'] ?? '');
        $stateId = (int)($data['state_id'] ?? 1);
        $pincode = !empty($data['pincode_prefix'] ?? $data['pincode']) ? trim($data['pincode_prefix'] ?? $data['pincode']) : null;
        $tier = trim($data['tier'] ?? 'Tier-2');
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? 'active');

        if (empty($name) || empty($stateId)) {
            throw new InvalidArgumentException("City Name and State are required.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO cities (state_id, city_name, pincode_prefix, tier, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$stateId, $name, $pincode, $tier, $status]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'CREATE_CITY', 'City', (string)$id, null, ['name' => $name]);
        return $this->getCityById($id);
    }

    public function updateCity(int $id, array $data, array $actor): array
    {
        $existing = $this->getCityById($id);
        if (!$existing) throw new InvalidArgumentException("City not found");

        $stateId = isset($data['state_id']) ? (int)$data['state_id'] : $existing['state_id'];
        $name = trim($data['city_name'] ?? $data['name'] ?? $existing['city_name']);
        $pincode = array_key_exists('pincode_prefix', $data) || array_key_exists('pincode', $data)
            ? trim($data['pincode_prefix'] ?? $data['pincode'] ?? '')
            : $existing['pincode_prefix'];
        $tier = trim($data['tier'] ?? $existing['tier']);
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? $existing['status']);

        $stmt = $this->pdo->prepare("
            UPDATE cities SET state_id = ?, city_name = ?, pincode_prefix = ?, tier = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$stateId, $name, $pincode, $tier, $status, $id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'UPDATE_CITY', 'City', (string)$id, $existing, ['name' => $name]);
        return $this->getCityById($id);
    }

    public function deleteCity(int $id, array $actor): bool
    {
        $existing = $this->getCityById($id);
        if (!$existing) throw new InvalidArgumentException("City not found");

        $stmt = $this->pdo->prepare("DELETE FROM cities WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'DELETE_CITY', 'City', (string)$id, $existing, null);
        return true;
    }

    // -------------------------------------------------------------------------
    // 4. ZONES MASTER
    // -------------------------------------------------------------------------

    public function getZones(array $filters = []): array
    {
        $sql = "SELECT * FROM zones WHERE 1=1";
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (zone_name LIKE ? OR zone_code LIKE ? OR regional_hub LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY zone_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['name'] = $r['zone_name'];
            $r['code'] = $r['zone_code'];
            $r['is_active'] = $r['status'] === 'active' ? 1 : 0;
            return $r;
        }, $rows);
    }

    public function getZoneById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM zones WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['name'] = $row['zone_name'];
        $row['code'] = $row['zone_code'];
        $row['is_active'] = $row['status'] === 'active' ? 1 : 0;
        return $row;
    }

    public function createZone(array $data, array $actor): array
    {
        $name = trim($data['zone_name'] ?? $data['name'] ?? '');
        $code = strtoupper(trim($data['zone_code'] ?? $data['code'] ?? ''));
        $hub = trim($data['regional_hub'] ?? '');
        $desc = trim($data['description'] ?? '');
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? 'active');

        if (empty($name) || empty($code)) {
            throw new InvalidArgumentException("Zone Name and Code are required.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO zones (zone_name, zone_code, regional_hub, description, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $code, $hub, $desc, $status]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'CREATE_ZONE', 'Zone', (string)$id, null, ['name' => $name]);
        return $this->getZoneById($id);
    }

    public function updateZone(int $id, array $data, array $actor): array
    {
        $existing = $this->getZoneById($id);
        if (!$existing) throw new InvalidArgumentException("Zone not found");

        $name = trim($data['zone_name'] ?? $data['name'] ?? $existing['zone_name']);
        $code = strtoupper(trim($data['zone_code'] ?? $data['code'] ?? $existing['zone_code']));
        $hub = trim($data['regional_hub'] ?? $existing['regional_hub']);
        $desc = trim($data['description'] ?? $existing['description']);
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? $existing['status']);

        $stmt = $this->pdo->prepare("
            UPDATE zones SET zone_name = ?, zone_code = ?, regional_hub = ?, description = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $code, $hub, $desc, $status, $id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'UPDATE_ZONE', 'Zone', (string)$id, $existing, ['name' => $name]);
        return $this->getZoneById($id);
    }

    public function deleteZone(int $id, array $actor): bool
    {
        $existing = $this->getZoneById($id);
        if (!$existing) throw new InvalidArgumentException("Zone not found");

        $stmt = $this->pdo->prepare("DELETE FROM zones WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'DELETE_ZONE', 'Zone', (string)$id, $existing, null);
        return true;
    }

    // -------------------------------------------------------------------------
    // 5. COURIERS MASTER
    // -------------------------------------------------------------------------

    public function getCouriers(array $filters = []): array
    {
        $sql = "SELECT * FROM couriers WHERE 1=1";
        $params = [];
        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (courier_name LIKE ? OR courier_code LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }
        $sql .= " ORDER BY courier_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) {
            $r['name'] = $r['courier_name'];
            $r['code'] = $r['courier_code'];
            $r['contact_number'] = !empty($r['contact_phone']) ? $r['contact_phone'] : ($r['helpline_number'] ?? '');
            $r['is_active'] = $r['status'] === 'active' ? 1 : 0;
            return $r;
        }, $rows);
    }

    public function getCourierById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM couriers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['name'] = $row['courier_name'];
        $row['code'] = $row['courier_code'];
        $row['contact_number'] = !empty($row['contact_phone']) ? $row['contact_phone'] : ($row['helpline_number'] ?? '');
        $row['is_active'] = $row['status'] === 'active' ? 1 : 0;
        return $row;
    }

    public function createCourier(array $data, array $actor): array
    {
        $name = trim($data['courier_name'] ?? $data['name'] ?? '');
        $code = strtoupper(trim($data['courier_code'] ?? $data['code'] ?? ''));
        if (empty($code) && !empty($name)) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 8));
        }
        $url = trim($data['tracking_url_template'] ?? '');
        $contactPerson = trim($data['contact_person'] ?? '');
        $phone = trim($data['contact_phone'] ?? $data['contact_number'] ?? $data['helpline_number'] ?? '');
        $email = trim($data['contact_email'] ?? '');
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? 'active');

        if (empty($name)) {
            throw new InvalidArgumentException("Courier Name is required.");
        }

        if (!empty($phone)) {
            $cleanPhone = preg_replace('/[\s\-\(\)\.]/', '', $phone);
            $digitsOnly = ltrim($cleanPhone, '+');
            if (strlen($digitsOnly) < 8 || strlen($digitsOnly) > 15 || !preg_match('/^\+?[0-9]{8,15}$/', $cleanPhone)) {
                throw new InvalidArgumentException("Invalid courier contact/helpline number '{$phone}'. Must be between 8 and 15 digits.");
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO couriers (courier_name, courier_code, tracking_url_template, contact_person, contact_phone, contact_email, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $code, $url, $contactPerson, $phone, $email, $status]);
        $id = (int)$this->pdo->lastInsertId();

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'CREATE_COURIER', 'Courier', (string)$id, null, ['name' => $name]);
        return $this->getCourierById($id);
    }

    public function updateCourier(int $id, array $data, array $actor): array
    {
        $existing = $this->getCourierById($id);
        if (!$existing) throw new InvalidArgumentException("Courier not found");

        $name = trim($data['courier_name'] ?? $data['name'] ?? $existing['courier_name']);
        $code = strtoupper(trim($data['courier_code'] ?? $data['code'] ?? $existing['courier_code']));
        $url = trim($data['tracking_url_template'] ?? $existing['tracking_url_template']);
        $contactPerson = trim($data['contact_person'] ?? $existing['contact_person']);
        $phone = trim($data['contact_phone'] ?? $data['contact_number'] ?? $existing['contact_phone']);
        $email = trim($data['contact_email'] ?? $existing['contact_email']);
        $status = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : ($data['status'] ?? $existing['status']);

        $stmt = $this->pdo->prepare("
            UPDATE couriers SET courier_name = ?, courier_code = ?, tracking_url_template = ?, contact_person = ?, contact_phone = ?, contact_email = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $code, $url, $contactPerson, $phone, $email, $status, $id]);

        AuditLog::log($actor['id'] ?? 1, $actor['name'] ?? 'Admin', $actor['role'] ?? 'admin', 'UPDATE_COURIER', 'Courier', (string)$id, $existing, ['name' => $name]);
        return $this->getCourierById($id);
    }

    public function deleteCourier(int $id, array $actor): bool
    {
        $existing = $this->getCourierById($id);
        if (!$existing) throw new InvalidArgumentException("Courier not found");

        $stmt = $this->pdo->prepare("DELETE FROM couriers WHERE id = ?");
        $stmt->execute([$id]);

        AuditLog::log($actor['id'], $actor['name'], $actor['role'] ?? 'admin', 'DELETE_COURIER', 'Courier', (string)$id, $existing, null);
        return true;
    }
}
