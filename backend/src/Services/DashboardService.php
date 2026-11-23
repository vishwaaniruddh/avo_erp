<?php

namespace App\Services;

use App\Core\Cache\CacheManager;
use App\Models\BaseModel;
use App\Models\Product;

class DashboardService
{
    public static function getMetrics(): array
    {
        $cacheKey = 'dashboard:metrics';
        $cached = CacheManager::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $pdo = BaseModel::getPdo();

        // 1. Total Invoiced Revenue
        $revStmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM invoices");
        $totalRevenue = (float) $revStmt->fetch()['total_revenue'];

        // 2. Pending Approvals
        $pendingStmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending_approval'");
        $pendingApprovals = (int) $pendingStmt->fetch()['count'];

        // 3. Low Stock Items
        $lowStockStmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= reorder_level");
        $lowStockCount = (int) $lowStockStmt->fetch()['count'];

        // 4. Total Orders
        $orderCountStmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $totalOrders = (int) $orderCountStmt->fetch()['count'];

        // 5. Order Status Distribution (For Recharts Pie/Bar Chart)
        $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $statusDistribution = $statusStmt->fetchAll();

        // 6. Category Stock Summary (For Recharts Bar Chart)
        $catStmt = $pdo->query("SELECT category, COUNT(*) as item_count, SUM(stock_quantity) as total_stock, SUM(stock_quantity * cost_price) as inventory_value FROM products GROUP BY category");
        $categoryBreakdown = $catStmt->fetchAll();

        // 7. Recent Invoices
        $recentInvStmt = $pdo->query("SELECT * FROM invoices ORDER BY id DESC LIMIT 5");
        $recentInvoices = $recentInvStmt->fetchAll();

        // 8. Low Stock Alert Items
        $lowStockItems = Product::getLowStock();

        $data = [
            'kpis' => [
                'total_revenue' => $totalRevenue,
                'pending_approvals' => $pendingApprovals,
                'low_stock_count' => $lowStockCount,
                'total_orders' => $totalOrders,
            ],
            'status_distribution' => $statusDistribution,
            'category_breakdown' => $categoryBreakdown,
            'recent_invoices' => $recentInvoices,
            'low_stock_items' => $lowStockItems,
        ];

        CacheManager::set($cacheKey, $data, 60); // 1 min cache
        return $data;
    }
}

