<?php

namespace App\Database;

use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\IdentityService;

class DatabaseSeeder
{
    public static function run(): void
    {
        $pdo = Database::getConnection();

        // 1. Seed All 14 Archetype Users (SS-35)
        foreach (IdentityService::ARCHETYPES as $arch) {
            $existing = User::findByEmail($arch['email']);
            if (!$existing) {
                User::insert([
                    'name' => $arch['sample_name'],
                    'email' => $arch['email'],
                    'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                    'role' => match ($arch['role_type']) {
                        'Executive', 'Admin' => 'admin',
                        'Finance' => 'accountant',
                        'Sales' => ($arch['level'] === 'L2' ? 'manager' : 'sales_rep'),
                        'Operations/Warehouse' => ($arch['level'] === 'L2' ? 'manager' : 'inventory_clerk'),
                        default => 'sales_rep',
                    },
                    'role_type' => $arch['role_type'],
                    'scope' => $arch['scope'],
                    'level' => $arch['level'],
                    'designation' => $arch['title'],
                    'region' => match ($arch['scope']) {
                        'own' => 'North Zone',
                        'region' => 'Western Territory',
                        'branch' => 'Delhi Central Hub',
                        default => 'All Territories (Global)',
                    },
                    'branch' => 'Delhi Central Hub',
                    'department' => $arch['department'],
                ]);
            } else {
                // Update with 3-tuple
                User::update($existing['id'], [
                    'role_type' => $arch['role_type'],
                    'scope' => $arch['scope'],
                    'level' => $arch['level'],
                    'designation' => $arch['title'],
                    'department' => $arch['department'],
                ]);
            }
        }

        // Establish reporting hierarchy (RA-03)
        $admin = User::findByEmail('admin@avoerp.com');
        $salesHead = User::findByEmail('manager@avoerp.com');
        $spA = User::findByEmail('sales@avoerp.com');
        $spB = User::findByEmail('sales2@avoerp.com');

        if ($admin && $salesHead) {
            User::update($salesHead['id'], ['reporting_manager_id' => $admin['id']]);
        }
        if ($salesHead && $spA && $spB) {
            User::update($spA['id'], ['reporting_manager_id' => $salesHead['id']]);
            User::update($spB['id'], ['reporting_manager_id' => $salesHead['id']]);
        }

        // 2. Seed Products
        $products = [
            [
                'sku' => 'SRV-DL-380',
                'name' => 'Enterprise Rack Server X9',
                'description' => '2U Dual Xeon 64-core 256GB ECC RAM with Redundant Power',
                'category' => 'Servers & Hardware',
                'cost_price' => 2400.00,
                'selling_price' => 3850.00,
                'tax_rate' => 18.00,
                'stock_quantity' => 15,
                'reorder_level' => 5,
                'unit' => 'unit',
            ],
            [
                'sku' => 'SW-ERP-ENT',
                'name' => 'AVO ERP Enterprise License (1-Year)',
                'description' => 'Unlimited users, full API integrations, dedicated SLA support',
                'category' => 'Software & Cloud',
                'cost_price' => 800.00,
                'selling_price' => 1950.00,
                'tax_rate' => 18.00,
                'stock_quantity' => 200,
                'reorder_level' => 20,
                'unit' => 'license',
            ],
            [
                'sku' => 'NET-SW-48P',
                'name' => 'Managed 48-Port PoE+ Gigabit Switch',
                'description' => 'Layer 3 Managed Switch with 4x 10G SFP+ Uplinks',
                'category' => 'Networking',
                'cost_price' => 620.00,
                'selling_price' => 980.00,
                'tax_rate' => 12.00,
                'stock_quantity' => 4,
                'reorder_level' => 8,
                'unit' => 'pcs',
            ],
            [
                'sku' => 'IOT-SEN-IND',
                'name' => 'Industrial Telemetry Sensor Probe',
                'description' => 'High-precision vibration, temperature & pressure IoT transducer',
                'category' => 'Industrial Components',
                'cost_price' => 95.00,
                'selling_price' => 175.00,
                'tax_rate' => 10.00,
                'stock_quantity' => 85,
                'reorder_level' => 25,
                'unit' => 'pcs',
            ],
            [
                'sku' => 'CAB-FO-OM4',
                'name' => 'Fiber Optic Patch Cord OM4 50M',
                'description' => 'Duplex LC-LC Multimode 100G ready aqua jacket',
                'category' => 'Networking',
                'cost_price' => 22.00,
                'selling_price' => 48.00,
                'tax_rate' => 12.00,
                'stock_quantity' => 140,
                'reorder_level' => 30,
                'unit' => 'pcs',
            ],
            [
                'sku' => 'PWR-UPS-3KVA',
                'name' => 'Online Double-Conversion UPS 3000VA',
                'description' => 'Rackmount battery backup with LCD and network card',
                'category' => 'Power & Infrastructure',
                'cost_price' => 710.00,
                'selling_price' => 1150.00,
                'tax_rate' => 18.00,
                'stock_quantity' => 3,
                'reorder_level' => 6,
                'unit' => 'unit',
            ],
        ];

        foreach ($products as $p) {
            if (!Product::findBySku($p['sku'])) {
                Product::insert($p);
            }
        }

        // 3. Seed Customers
        $customers = [
            [
                'name' => 'Global Logistics Tech Corp',
                'company' => 'Global Logistics Tech Corp',
                'email' => 'procurement@globallogistics.io',
                'phone' => '+1-555-019-2831',
                'tax_number' => 'US-EIN-9843101',
                'credit_limit' => 25000.00,
                'outstanding_balance' => 4543.00,
                'address' => '742 Evergreen Gateway, Suite 400, Chicago, IL',
            ],
            [
                'name' => 'Apex Healthcare Systems',
                'company' => 'Apex Health Group Ltd',
                'email' => 'purchasing@apexhealth.org',
                'phone' => '+1-555-014-9922',
                'tax_number' => 'US-EIN-8812903',
                'credit_limit' => 50000.00,
                'outstanding_balance' => 0.00,
                'address' => '1200 Medical Center Way, Austin, TX',
            ],
            [
                'name' => 'Vanguard Cyber Defense',
                'company' => 'Vanguard Defense Solutions Inc',
                'email' => 'orders@vanguardcyber.net',
                'phone' => '+1-555-018-7711',
                'tax_number' => 'US-EIN-7721094',
                'credit_limit' => 15000.00,
                'outstanding_balance' => 1097.60,
                'address' => '300 Silicon Hill Blvd, San Jose, CA',
            ],
        ];

        foreach ($customers as $c) {
            $existingCust = Customer::findByEmail($c['email']);
            if (!$existingCust) {
                Customer::insert($c);
            } else {
                Customer::update($existingCust['id'], [
                    'credit_limit' => $c['credit_limit'],
                    'outstanding_balance' => $c['outstanding_balance'],
                ]);
            }
        }

        // 4. Seed Chinese-Wall Bidding Quotes for RFP # 2026-NTPC-09 (RA-04, RA-05)
        $rfpCheck = $pdo->query("SELECT COUNT(*) as cnt FROM rfp_bids");
        if ((int)$rfpCheck->fetch()['cnt'] === 0 && $spA && $spB) {
            $rfpNumber = '2026-NTPC-09';
            $norm = '2026ntpc09';
            $silentLinkId = 'LINK-' . strtoupper(substr(md5($norm), 0, 8));

            // Quote 1: Salesperson A quoting Integrator Alpha
            $pdo->prepare("INSERT INTO rfp_bids (rfp_number, normalized_rfp, rfp_title, end_client, integrator_name, salesperson_id, salesperson_name, quote_amount, cost_amount, est_margin_pct, status, silent_link_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([
                $rfpNumber,
                $norm,
                '50MW Solar Substation SCADA & UPS Deployment',
                'NTPC Renewable Energy Corporation',
                'Integrator Alpha Tech Solutions',
                $spA['id'],
                $spA['name'],
                485000.00,
                360000.00,
                25.77,
                'submitted',
                $silentLinkId,
            ]);

            // Quote 2: Salesperson B quoting Integrator Beta (Competing Integrator)
            $pdo->prepare("INSERT INTO rfp_bids (rfp_number, normalized_rfp, rfp_title, end_client, integrator_name, salesperson_id, salesperson_name, quote_amount, cost_amount, est_margin_pct, status, silent_link_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([
                $rfpNumber,
                $norm,
                '50MW Solar Substation SCADA & UPS Deployment',
                'NTPC Renewable Energy Corporation',
                'Integrator Beta Power Systems',
                $spB['id'],
                $spB['name'],
                512000.00,
                375000.00,
                26.76,
                'submitted',
                $silentLinkId,
            ]);
        }

        // 5. Seed Scrap Ledger (RA-07, RA-08)
        $clerk = User::findByEmail('clerk@avoerp.com');
        $scrapCheck = $pdo->query("SELECT COUNT(*) as cnt FROM scrap_ledger");
        if ((int)$scrapCheck->fetch()['cnt'] === 0 && $clerk) {
            $sampleScraps = [
                ['material_type' => 'Copper Wire Coils', 'weight_kg' => 145.5, 'defect_reason' => 'Damaged during high-tension coil rewinding', 'est_recovery_value' => 1746.00],
                ['material_type' => 'Tested Battery Cells (Lithium)', 'weight_kg' => 84.0, 'defect_reason' => 'Capacity degraded below 60% SOH in thermal bench', 'est_recovery_value' => 2394.00],
                ['material_type' => 'Salvageable Inverter PCBs', 'weight_kg' => 32.0, 'defect_reason' => 'Blown IGBT gates, copper planes intact for rework', 'est_recovery_value' => 1440.00],
            ];
            foreach ($sampleScraps as $s) {
                $pdo->prepare("INSERT INTO scrap_ledger (material_type, weight_kg, serial_number, defect_reason, warehouse_branch, operator_id, operator_name, est_recovery_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")->execute([
                    $s['material_type'],
                    $s['weight_kg'],
                    'SN-SCRAP-' . strtoupper(substr(uniqid(), -6)),
                    $s['defect_reason'],
                    'Delhi Central Hub',
                    $clerk['id'],
                    $clerk['name'],
                    $s['est_recovery_value'],
                ]);
            }
        }

        // 6. Seed Dynamic Permission Matrix (SS-25)
        IdentityService::seedDefaultPermissionMatrix($pdo);
    }
}
