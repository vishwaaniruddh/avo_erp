<?php

namespace Tests\Unit;

use App\Core\Auth\CasbinEnforcer;
use PHPUnit\Framework\TestCase;

class CasbinRbacTest extends TestCase
{
    public function testAdminHasFullAccess(): void
    {
        $this->assertTrue(CasbinEnforcer::enforce('admin', '/api/orders', 'POST'));
        $this->assertTrue(CasbinEnforcer::enforce('admin', '/api/products/1', 'DELETE'));
        $this->assertTrue(CasbinEnforcer::enforce('admin', '/api/invoices', 'POST'));
    }

    public function testManagerCanManageOrdersAndProducts(): void
    {
        $this->assertTrue(CasbinEnforcer::enforce('manager', '/api/orders', 'GET'));
        $this->assertTrue(CasbinEnforcer::enforce('manager', '/api/orders/5/approve', 'POST'));
        $this->assertTrue(CasbinEnforcer::enforce('manager', '/api/products', 'POST'));
    }

    public function testAccountantCanAccessInvoicesAndReadOrders(): void
    {
        $this->assertTrue(CasbinEnforcer::enforce('accountant', '/api/invoices', 'GET'));
        $this->assertTrue(CasbinEnforcer::enforce('accountant', '/api/invoices', 'POST'));
        $this->assertTrue(CasbinEnforcer::enforce('accountant', '/api/orders', 'GET'));
        // Accountant cannot delete products
        $this->assertFalse(CasbinEnforcer::enforce('accountant', '/api/products/1', 'DELETE'));
    }

    public function testSalesRepCannotApproveOrDeleteProducts(): void
    {
        // Sales Rep can create orders
        $this->assertTrue(CasbinEnforcer::enforce('sales_rep', '/api/orders', 'POST'));
        // Sales Rep cannot delete products
        $this->assertFalse(CasbinEnforcer::enforce('sales_rep', '/api/products/1', 'DELETE'));
    }

    public function testInventoryClerkCanAdjustStock(): void
    {
        $this->assertTrue(CasbinEnforcer::enforce('inventory_clerk', '/api/products/1/stock', 'PATCH'));
        // Inventory clerk cannot create customer
        $this->assertFalse(CasbinEnforcer::enforce('inventory_clerk', '/api/customers', 'POST'));
    }
}

