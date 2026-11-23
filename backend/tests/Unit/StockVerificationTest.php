<?php

namespace Tests\Unit;

use App\Database\DatabaseSeeder;
use App\Models\Product;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

class StockVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseSeeder::run();
    }

    public function testStockVerificationPassesWhenStockAvailable(): void
    {
        $product = Product::findBySku('SRV-DL-380');
        $this->assertNotNull($product);

        $items = [
            [
                'product_id' => $product['id'],
                'quantity' => 1,
            ],
        ];

        $verified = OrderService::verifyStockAvailability($items);
        $this->assertArrayHasKey($product['id'], $verified);
    }

    public function testStockVerificationThrowsWhenDeficit(): void
    {
        $product = Product::findBySku('SRV-DL-380');
        $this->assertNotNull($product);

        $items = [
            [
                'product_id' => $product['id'],
                'quantity' => 999999, // Deficit!
            ],
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        OrderService::verifyStockAvailability($items);
    }
}

