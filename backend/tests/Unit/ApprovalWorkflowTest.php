<?php

namespace Tests\Unit;

use App\Database\DatabaseSeeder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseSeeder::run();
    }

    public function testHighValueOrderTriggersPendingApproval(): void
    {
        $salesRep = User::findByEmail('sales@avoerp.com');
        $customer = Customer::findByEmail('procurement@globallogistics.io');
        $product = Product::findBySku('SRV-DL-380'); // $3850.00 (> 1000 threshold)

        $order = OrderService::createOrder([
            'customer_id' => $customer['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 1,
                    'unit_price' => $product['selling_price'],
                ],
            ],
            'notes' => 'High value order test',
        ], $salesRep);

        $this->assertEquals(OrderService::STATUS_PENDING, $order['status']);
    }

    public function testManagerCanApprovePendingOrder(): void
    {
        $manager = User::findByEmail('manager@avoerp.com');
        $customer = Customer::findByEmail('procurement@globallogistics.io');
        $product = Product::findBySku('SRV-DL-380');

        $order = OrderService::createOrder([
            'customer_id' => $customer['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 1,
                    'unit_price' => $product['selling_price'],
                ],
            ],
        ], $manager);

        $approved = OrderService::approveOrder($order['id'], $manager);
        $this->assertEquals(OrderService::STATUS_APPROVED, $approved['status']);
    }

    public function testUnauthorizedRoleCannotApproveOrder(): void
    {
        $salesRep = User::findByEmail('sales@avoerp.com');
        $manager = User::findByEmail('manager@avoerp.com');
        $customer = Customer::findByEmail('procurement@globallogistics.io');
        $product = Product::findBySku('SRV-DL-380');

        $order = OrderService::createOrder([
            'customer_id' => $customer['id'],
            'items' => [
                [
                    'product_id' => $product['id'],
                    'quantity' => 1,
                    'unit_price' => $product['selling_price'],
                ],
            ],
        ], $manager);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Permission denied/');

        // Sales Rep cannot approve!
        OrderService::approveOrder($order['id'], $salesRep);
    }
}

