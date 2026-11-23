<?php

namespace Tests\Unit;

use App\Core\Auth\CasbinEnforcer;
use App\Database\DatabaseSeeder;
use App\Models\User;
use App\Services\BiddingService;
use App\Services\IdentityService;
use App\Services\ScrapService;
use PHPUnit\Framework\TestCase;

class IdentityAndSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseSeeder::run();
    }

    /**
     * Requirement: SS-23, SS-33, SS-35 (3-Attribute Model: Role Type + Scope + Level)
     */
    public function test3DimensionalUserTupleModel(): void
    {
        $users = IdentityService::getAllUsers();
        $this->assertNotEmpty($users);

        // Verify that users carry role_type, scope, and level
        $admin = User::findByEmail('admin@avoerp.com');
        $this->assertEquals('Executive', $admin['role_type']);
        $this->assertEquals('global', $admin['scope']);
        $this->assertEquals('L3', $admin['level']);

        $salesA = User::findByEmail('sales@avoerp.com');
        $this->assertEquals('Sales', $salesA['role_type']);
        $this->assertEquals('own', $salesA['scope']);
        $this->assertEquals('L1', $salesA['level']);

        $clerk = User::findByEmail('clerk@avoerp.com');
        $this->assertEquals('Operations/Warehouse', $clerk['role_type']);
        $this->assertEquals('branch', $clerk['scope']);
        $this->assertEquals('L1', $clerk['level']);
    }

    /**
     * Requirement: RA-04 (Chinese-Wall Bidding Isolation)
     * Salesperson sees ONLY their quote for RFP # 2026-NTPC-09 and is blind to competing quotes
     */
    public function testChineseWallBiddingIsolation(): void
    {
        $salesA = User::findByEmail('sales@avoerp.com');
        $salesB = User::findByEmail('sales2@avoerp.com');

        $resultA = BiddingService::getBids($salesA);
        $this->assertEquals('salesperson_isolated', $resultA['view_mode']);
        $this->assertTrue($resultA['chinese_wall_active']);

        // Salesperson A must see ONLY quotes created by Salesperson A
        foreach ($resultA['bids'] as $bid) {
            $this->assertEquals($salesA['id'], $bid['salesperson_id']);
            $this->assertNotEquals($salesB['id'], $bid['salesperson_id']);
        }

        // Salesperson B must see ONLY quotes created by Salesperson B
        $resultB = BiddingService::getBids($salesB);
        $this->assertEquals('salesperson_isolated', $resultB['view_mode']);
        foreach ($resultB['bids'] as $bid) {
            $this->assertEquals($salesB['id'], $bid['salesperson_id']);
            $this->assertNotEquals($salesA['id'], $bid['salesperson_id']);
        }
    }

    /**
     * Requirement: RA-05 (Silent RFP Deduplication in Owner View)
     * Owner sees side-by-side quotes for same RFP with silent background link
     */
    public function testSilentRfpDeduplicationOwnerView(): void
    {
        $owner = User::findByEmail('admin@avoerp.com');
        $result = BiddingService::getBids($owner);

        $this->assertEquals('executive_unified', $result['view_mode']);
        $this->assertGreaterThanOrEqual(2, $result['total_bids']);
        // Unique RFP count should deduplicate identical tenders
        $this->assertLessThanOrEqual($result['total_bids'], $result['unique_rfp_count']);
    }

    /**
     * Requirement: RA-07 (Warehouse Scrap Blind-Entry & Anti-Collusion Gate)
     * Warehouse operator CAN record scrap discharge (write-only), but CANNOT read past scrap records
     */
    public function testWarehouseScrapBlindEntryGate(): void
    {
        $clerk = User::findByEmail('clerk@avoerp.com');

        // 1. Write/Submit is allowed
        $submitResult = ScrapService::submitDischarge([
            'material_type' => 'Copper Wire Coils',
            'weight_kg' => 25.5,
            'defect_reason' => 'Damaged insulation during rewind',
        ], $clerk);

        $this->assertEquals('discharged', $submitResult['status']);

        // 2. Reading cumulative ledger throws exception (Forbidden / 403)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Blind Entry Security Gate/');

        ScrapService::getLedger($clerk);
    }

    /**
     * Requirement: RA-08 (Scrap Executive View)
     * Owner / CFO has full visibility into scrap recovery ledger and valuations
     */
    public function testScrapExecutiveView(): void
    {
        $owner = User::findByEmail('admin@avoerp.com');
        $ledger = ScrapService::getLedger($owner);

        $this->assertArrayHasKey('records', $ledger);
        $this->assertArrayHasKey('summary', $ledger);
        $this->assertGreaterThan(0, $ledger['summary']['total_recovery_value']);
    }

    /**
     * Requirement: SS-24 (API-Layer Zero-Trust Enforcement via Casbin)
     */
    public function testCasbinZeroTrustEnforcement(): void
    {
        $salesA = User::findByEmail('sales@avoerp.com');
        $clerk = User::findByEmail('clerk@avoerp.com');
        $owner = User::findByEmail('admin@avoerp.com');

        // Scrap GET: Clerk forbidden (Blind entry gate)
        $this->assertFalse(CasbinEnforcer::enforceUser($clerk, '/api/scrap', 'GET'));

        // Scrap POST: Clerk allowed (Discharge entry)
        $this->assertTrue(CasbinEnforcer::enforceUser($clerk, '/api/scrap', 'POST'));

        // Scrap GET: Owner allowed
        $this->assertTrue(CasbinEnforcer::enforceUser($owner, '/api/scrap', 'GET'));

        // Salesperson cannot delete product
        $this->assertFalse(CasbinEnforcer::enforceUser($salesA, '/api/products/1', 'DELETE'));

        // User Module & Permission Matrix Access:
        // Regular staff (Salesperson L1, Clerk L1) are FORBIDDEN from creating/updating users or permissions
        $this->assertFalse(CasbinEnforcer::enforceUser($salesA, '/api/users', 'POST'));
        $this->assertFalse(CasbinEnforcer::enforceUser($clerk, '/api/users', 'POST'));
        $this->assertFalse(CasbinEnforcer::enforceUser($salesA, '/api/identity/matrix/1', 'PUT'));

        // Owner (Executive L3) and Admin are ALLOWED full access to User Module
        $this->assertTrue(CasbinEnforcer::enforceUser($owner, '/api/users', 'GET'));
        $this->assertTrue(CasbinEnforcer::enforceUser($owner, '/api/users', 'POST'));
        $this->assertTrue(CasbinEnforcer::enforceUser($owner, '/api/identity/matrix/1', 'PUT'));
    }
}

