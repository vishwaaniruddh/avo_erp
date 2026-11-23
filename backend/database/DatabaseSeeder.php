<?php

namespace App\Database;

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class DatabaseSeeder
{
    public static function run(): void
    {
        $pdo = Database::getConnection();

        // 1. Seed Users
        $users = [
            [
                'name' => 'Alexander Vance (Admin)',
                'email' => 'admin@avoerp.com',
                'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
                'role' => 'admin',
            ],
            [
                'name' => 'Morgan Blake (Manager)',
                'email' => 'manager@avoerp.com',
                'password_hash' => password_hash('manager123', PASSWORD_BCRYPT),
                'role' => 'manager',
            ],
            [
                'name' => 'Cassandra Cole (Accountant)',
                'email' => 'accountant@avoerp.com',
                'password_hash' => password_hash('accountant123', PASSWORD_BCRYPT),
                'role' => 'accountant',
            ],
            [
                'name' => 'Lucas Reed (Sales Rep)',
                'email' => 'sales@avoerp.com',
                'password_hash' => password_hash('sales123', PASSWORD_BCRYPT),
                'role' => 'sales_rep',
            ],
            [
                'name' => 'Harper Quinn (Inventory Clerk)',
                'email' => 'clerk@avoerp.com',
                'password_hash' => password_hash('clerk123', PASSWORD_BCRYPT),
                'role' => 'inventory_clerk',
            ],
        ];

        foreach ($users as $u) {
            if (!User::findByEmail($u['email'])) {
                User::insert($u);
            }
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
                'stock_quantity' => 4, // Low stock on purpose!
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
                'stock_quantity' => 3, // Low stock on purpose!
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
            if (!Customer::findByEmail($c['email'])) {
                Customer::insert($c);
            }
        }

        // 4. Seed Initial Orders and Invoices
        $admin = User::findByEmail('admin@avoerp.com');
        $cust1 = Customer::findByEmail('procurement@globallogistics.io');
        $pServer = Product::findBySku('SRV-DL-380');
        $pSwitch = Product::findBySku('NET-SW-48P');

        if ($cust1 && $pServer && $pSwitch) {
            $existingOrders = Order::all();
            if (empty($existingOrders)) {
                // Order 1: Approved & Invoiced
                $ord1Num = 'ORD-20261101-9011';
                $ord1Id = Order::insert([
                    'order_number' => $ord1Num,
                    'customer_id' => $cust1['id'],
                    'customer_name' => $cust1['name'],
                    'status' => 'invoiced',
                    'subtotal' => 3850.00,
                    'tax_amount' => 693.00,
                    'discount_amount' => 0.00,
                    'grand_total' => 4543.00,
                    'created_by_id' => $admin['id'],
                    'created_by_name' => $admin['name'],
                    'approved_by_id' => $admin['id'],
                    'approved_by_name' => $admin['name'],
                    'notes' => 'Priority datacenter infrastructure rollout',
                ]);

                OrderItem::insert([
                    'order_id' => $ord1Id,
                    'product_id' => $pServer['id'],
                    'product_name' => $pServer['name'],
                    'sku' => $pServer['sku'],
                    'quantity' => 1,
                    'unit_price' => 3850.00,
                    'tax_rate' => 18.00,
                    'tax_amount' => 693.00,
                    'line_total' => 4543.00,
                ]);

                $inv1Num = 'INV-20261101-00001';
                $issuedAt = '2026-11-01 10:00:00';
                $checksum = hash('sha256', "{$inv1Num}|{$ord1Id}|{$cust1['id']}|4543.00|{$issuedAt}");
                Invoice::insert([
                    'invoice_number' => $inv1Num,
                    'order_id' => $ord1Id,
                    'customer_id' => $cust1['id'],
                    'customer_name' => $cust1['name'],
                    'subtotal' => 3850.00,
                    'tax_amount' => 693.00,
                    'total_amount' => 4543.00,
                    'status' => 'unpaid',
                    'due_date' => '2026-12-01',
                    'checksum' => $checksum,
                    'issued_at' => $issuedAt,
                    'notes' => 'Official invoice for Order ORD-20261101-9011',
                ]);

                // Order 2: Pending Approval (high value)
                $ord2Num = 'ORD-20261110-8422';
                $ord2Id = Order::insert([
                    'order_number' => $ord2Num,
                    'customer_id' => $cust1['id'],
                    'customer_name' => $cust1['name'],
                    'status' => 'pending_approval',
                    'subtotal' => 1960.00,
                    'tax_amount' => 235.20,
                    'discount_amount' => 50.00,
                    'grand_total' => 2145.20,
                    'created_by_id' => $admin['id'],
                    'created_by_name' => $admin['name'],
                    'notes' => 'Awaiting manager approval for 2x network switches',
                ]);

                OrderItem::insert([
                    'order_id' => $ord2Id,
                    'product_id' => $pSwitch['id'],
                    'product_name' => $pSwitch['name'],
                    'sku' => $pSwitch['sku'],
                    'quantity' => 2,
                    'unit_price' => 980.00,
                    'tax_rate' => 12.00,
                    'tax_amount' => 235.20,
                    'line_total' => 2195.20,
                ]);

                // Audit log
                AuditLog::log(
                    $admin['id'],
                    $admin['name'],
                    $admin['role'],
                    'SYSTEM_INITIALIZATION',
                    'System',
                    '1',
                    null,
                    ['message' => 'AVO ERP Database seeded with sample records']
                );
            }
        }
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    DatabaseSeeder::run();
    echo "AVO ERP Database seeded successfully.\n";
}

