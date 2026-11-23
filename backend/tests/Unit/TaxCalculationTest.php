<?php

namespace Tests\Unit;

use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class TaxCalculationTest extends TestCase
{
    public function testAuthoritativeTaxCalculationSingleItem(): void
    {
        $items = [
            [
                'product_id' => 1,
                'quantity' => 2,
                'unit_price' => 100.00,
                'tax_rate' => 18.00,
            ],
        ];

        $result = OrderService::calculateAuthoritativeTotals($items, 0.0);

        // Subtotal = 2 * 100 = 200
        // Tax = 200 * 0.18 = 36
        // Grand Total = 236
        $this->assertEquals(200.00, $result['subtotal']);
        $this->assertEquals(36.00, $result['tax_amount']);
        $this->assertEquals(0.00, $result['discount_amount']);
        $this->assertEquals(236.00, $result['grand_total']);
    }

    public function testAuthoritativeTaxCalculationMultipleItemsAndDiscount(): void
    {
        $items = [
            [
                'product_id' => 1,
                'quantity' => 1,
                'unit_price' => 1000.00,
                'tax_rate' => 18.00, // tax: 180.00
            ],
            [
                'product_id' => 2,
                'quantity' => 4,
                'unit_price' => 50.00,
                'tax_rate' => 10.00, // subtotal: 200, tax: 20.00
            ],
        ];

        // Total subtotal: 1200.00, Total tax: 200.00, Discount: 50.00 -> Grand Total: 1350.00
        $result = OrderService::calculateAuthoritativeTotals($items, 50.00);

        $this->assertEquals(1200.00, $result['subtotal']);
        $this->assertEquals(200.00, $result['tax_amount']);
        $this->assertEquals(50.00, $result['discount_amount']);
        $this->assertEquals(1350.00, $result['grand_total']);
    }

    public function testInvalidQuantityThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $items = [
            [
                'product_id' => 1,
                'quantity' => 0,
                'unit_price' => 100.00,
                'tax_rate' => 10.00,
            ],
        ];

        OrderService::calculateAuthoritativeTotals($items);
    }
}

