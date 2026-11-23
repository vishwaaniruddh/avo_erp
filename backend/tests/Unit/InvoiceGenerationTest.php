<?php

namespace Tests\Unit;

use App\Database\DatabaseSeeder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class InvoiceGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseSeeder::run();
    }

    public function testAuthoritativeInvoiceGenerationChecksumAndState(): void
    {
        $manager = User::findByEmail('manager@avoerp.com');
        $customer = Customer::findByEmail('procurement@globallogistics.io');
        $product = Product::findBySku('SW-ERP-ENT');

        // Create and approve order
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

        // Approve it
        OrderService::approveOrder($order['id'], $manager);

        // Generate Invoice
        $invoice = InvoiceService::generateFromOrder($order['id'], $manager);

        $this->assertNotEmpty($invoice['invoice_number']);
        $this->assertEquals($order['id'], $invoice['order_id']);
        $this->assertNotEmpty($invoice['checksum']);
        $this->assertEquals('unpaid', $invoice['status']);

        // Verify order transitioned to invoiced
        $refreshedOrder = Order::find($order['id']);
        $this->assertEquals(OrderService::STATUS_INVOICED, $refreshedOrder['status']);
    }

    public function testCannotGenerateInvoiceForUnapprovedOrder(): void
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
        ], $manager); // Remains pending_approval (> 1000)

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Authoritative invoice can only be generated for an APPROVED order/');

        InvoiceService::generateFromOrder($order['id'], $manager);
    }
}

