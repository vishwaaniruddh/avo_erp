<?php

// 1. Fallback PSR-4 Autoloader for App\ namespace (guarantees core classes load even without Composer)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Load Composer autoloader if present
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use App\Controllers\AuditLogController;
use App\Controllers\AuthController;
use App\Controllers\BiddingController;
use App\Controllers\CustomerController;
use App\Controllers\DashboardController;
use App\Controllers\EmailController;
use App\Controllers\IdentityController;
use App\Controllers\InvoiceController;
use App\Controllers\MasterController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\SalesController;
use App\Controllers\ScrapController;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CasbinMiddleware;
use App\Core\Middleware\CorsMiddleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

// 3. Catch all uncaught exceptions with JSON 500 response
set_exception_handler(function (\Throwable $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        http_response_code(500);
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
});

// 4. Catch fatal PHP errors with JSON response
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
});

$request = new Request();

// Run CORS middleware immediately
CorsMiddleware::handle($request);

$router = new Router();

// --- Public Auth Routes ---
$router->post('/api/auth/login', [AuthController::class, 'login']);

// --- Protected Routes (Require JWT Auth + Casbin RBAC) ---
$authAndRbac = [AuthMiddleware::class, CasbinMiddleware::class];

// Auth profile
$router->get('/api/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);

// Identity, Roles & Access Control (SS6)
$router->get('/api/identity/roles', [IdentityController::class, 'getRoles'], $authAndRbac);
$router->get('/api/identity/matrix', [IdentityController::class, 'getMatrix'], $authAndRbac);
$router->put('/api/identity/matrix/role/{role}', [IdentityController::class, 'bulkUpdateRole'], $authAndRbac);
$router->put('/api/identity/matrix/{id}', [IdentityController::class, 'updateMatrix'], $authAndRbac);
$router->get('/api/users', [IdentityController::class, 'getUsers'], $authAndRbac);
$router->post('/api/users', [IdentityController::class, 'createUser'], $authAndRbac);
$router->put('/api/users/{id}', [IdentityController::class, 'updateUser'], $authAndRbac);
$router->delete('/api/users/{id}', [IdentityController::class, 'deleteUser'], $authAndRbac);

// Superadmin: Email Credentials & Template Management
$router->get('/api/email/settings', [EmailController::class, 'getSettings'], $authAndRbac);
$router->post('/api/email/settings', [EmailController::class, 'createSetting'], $authAndRbac);
$router->put('/api/email/settings/{id}', [EmailController::class, 'updateSetting'], $authAndRbac);
$router->delete('/api/email/settings/{id}', [EmailController::class, 'deleteSetting'], $authAndRbac);
$router->post('/api/email/settings/test', [EmailController::class, 'testConnection'], $authAndRbac);

$router->get('/api/email/templates', [EmailController::class, 'getTemplates'], $authAndRbac);
$router->get('/api/email/templates/{id}', [EmailController::class, 'showTemplate'], $authAndRbac);
$router->post('/api/email/templates', [EmailController::class, 'createTemplate'], $authAndRbac);
$router->put('/api/email/templates/{id}', [EmailController::class, 'updateTemplate'], $authAndRbac);
$router->delete('/api/email/templates/{id}', [EmailController::class, 'deleteTemplate'], $authAndRbac);

// Superadmin: Masters (Country, State, City, Zone, Courier)
$router->get('/api/masters/countries', [MasterController::class, 'getCountries'], $authAndRbac);
$router->post('/api/masters/countries', [MasterController::class, 'createCountry'], $authAndRbac);
$router->put('/api/masters/countries/{id}', [MasterController::class, 'updateCountry'], $authAndRbac);
$router->delete('/api/masters/countries/{id}', [MasterController::class, 'deleteCountry'], $authAndRbac);

$router->get('/api/masters/states', [MasterController::class, 'getStates'], $authAndRbac);
$router->post('/api/masters/states', [MasterController::class, 'createState'], $authAndRbac);
$router->put('/api/masters/states/{id}', [MasterController::class, 'updateState'], $authAndRbac);
$router->delete('/api/masters/states/{id}', [MasterController::class, 'deleteState'], $authAndRbac);

$router->get('/api/masters/cities', [MasterController::class, 'getCities'], $authAndRbac);
$router->post('/api/masters/cities', [MasterController::class, 'createCity'], $authAndRbac);
$router->put('/api/masters/cities/{id}', [MasterController::class, 'updateCity'], $authAndRbac);
$router->delete('/api/masters/cities/{id}', [MasterController::class, 'deleteCity'], $authAndRbac);

$router->get('/api/masters/zones', [MasterController::class, 'getZones'], $authAndRbac);
$router->post('/api/masters/zones', [MasterController::class, 'createZone'], $authAndRbac);
$router->put('/api/masters/zones/{id}', [MasterController::class, 'updateZone'], $authAndRbac);
$router->delete('/api/masters/zones/{id}', [MasterController::class, 'deleteZone'], $authAndRbac);

$router->get('/api/masters/couriers', [MasterController::class, 'getCouriers'], $authAndRbac);
$router->post('/api/masters/couriers', [MasterController::class, 'createCourier'], $authAndRbac);
$router->put('/api/masters/couriers/{id}', [MasterController::class, 'updateCourier'], $authAndRbac);
$router->delete('/api/masters/couriers/{id}', [MasterController::class, 'deleteCourier'], $authAndRbac);

// Module 06 / 09: Sales, Marketing, Tender & Bid Platform
$router->get('/api/sales/leads', [SalesController::class, 'getLeads'], $authAndRbac);
$router->post('/api/sales/leads', [SalesController::class, 'createLead'], $authAndRbac);
$router->put('/api/sales/leads/{id}/status', [SalesController::class, 'updateLeadStatus'], $authAndRbac);
$router->get('/api/sales/visits', [SalesController::class, 'getVisits'], $authAndRbac);
$router->post('/api/sales/visits', [SalesController::class, 'createVisit'], $authAndRbac);
$router->get('/api/sales/quotes', [SalesController::class, 'getQuotes'], $authAndRbac);
$router->get('/api/sales/quotes/{id}', [SalesController::class, 'getQuote'], $authAndRbac);
$router->post('/api/sales/quotes', [SalesController::class, 'createQuote'], $authAndRbac);
$router->put('/api/sales/quotes/{id}', [SalesController::class, 'updateQuote'], $authAndRbac);
$router->post('/api/sales/quotes/{id}/approve', [SalesController::class, 'approveQuote'], $authAndRbac);
$router->post('/api/sales/quotes/{id}/convert-so', [SalesController::class, 'convertQuoteToSO'], $authAndRbac);
$router->get('/api/sales/tenders', [SalesController::class, 'getTenders'], $authAndRbac);
$router->post('/api/sales/tenders', [SalesController::class, 'createTender'], $authAndRbac);
$router->put('/api/sales/tenders/{id}/outcome', [SalesController::class, 'updateTenderOutcome'], $authAndRbac);
$router->get('/api/sales/tenders/guarantees', [SalesController::class, 'getGuarantees'], $authAndRbac);
$router->get('/api/sales/rfp-clusters', [SalesController::class, 'getClusters'], $authAndRbac);
$router->post('/api/sales/rfp-clusters', [SalesController::class, 'createCluster'], $authAndRbac);
$router->put('/api/sales/rfp-clusters/{id}/outcome', [SalesController::class, 'updateClusterOutcome'], $authAndRbac);
$router->delete('/api/sales/rfp-clusters/{id}', [SalesController::class, 'deleteCluster'], $authAndRbac);
$router->get('/api/sales/orders/milestones', [SalesController::class, 'getOrderMilestones'], $authAndRbac);
$router->get('/api/sales/orders/{id}/audit-package', [SalesController::class, 'getAuditPackage'], $authAndRbac);

// Chinese-Wall Bidding & Silent Deduplication (RA-04, RA-05)
$router->get('/api/rfp-bids', [BiddingController::class, 'index'], $authAndRbac);
$router->post('/api/rfp-bids', [BiddingController::class, 'store'], $authAndRbac);

// Warehouse Scrap Blind-Entry & Anti-Collusion Gate (RA-07, RA-08)
$router->get('/api/scrap', [ScrapController::class, 'index'], $authAndRbac);
$router->post('/api/scrap', [ScrapController::class, 'store'], $authAndRbac);

// Dashboard
$router->get('/api/dashboard', [DashboardController::class, 'index'], $authAndRbac);

// Products
$router->get('/api/products', [ProductController::class, 'index'], $authAndRbac);
$router->get('/api/products/{id}', [ProductController::class, 'show'], $authAndRbac);
$router->post('/api/products', [ProductController::class, 'store'], $authAndRbac);
$router->put('/api/products/{id}', [ProductController::class, 'update'], $authAndRbac);
$router->patch('/api/products/{id}/stock', [ProductController::class, 'adjustStock'], $authAndRbac);
$router->delete('/api/products/{id}', [ProductController::class, 'destroy'], $authAndRbac);

// Customers
$router->get('/api/customers', [CustomerController::class, 'index'], $authAndRbac);
$router->get('/api/customers/{id}', [CustomerController::class, 'show'], $authAndRbac);
$router->post('/api/customers', [CustomerController::class, 'store'], $authAndRbac);
$router->put('/api/customers/{id}', [CustomerController::class, 'update'], $authAndRbac);
$router->delete('/api/customers/{id}', [CustomerController::class, 'destroy'], $authAndRbac);

// Orders
$router->get('/api/orders', [OrderController::class, 'index'], $authAndRbac);
$router->get('/api/orders/{id}', [OrderController::class, 'show'], $authAndRbac);
$router->post('/api/orders', [OrderController::class, 'store'], $authAndRbac);
$router->post('/api/orders/{id}/approve', [OrderController::class, 'approve'], $authAndRbac);
$router->post('/api/orders/{id}/reject', [OrderController::class, 'reject'], $authAndRbac);

// Invoices
$router->get('/api/invoices', [InvoiceController::class, 'index'], $authAndRbac);
$router->get('/api/invoices/{id}', [InvoiceController::class, 'show'], $authAndRbac);
$router->post('/api/invoices', [InvoiceController::class, 'generate'], $authAndRbac);
$router->post('/api/invoices/{id}/pay', [InvoiceController::class, 'pay'], $authAndRbac);

// Audit Logs
$router->get('/api/audit-logs', [AuditLogController::class, 'index'], $authAndRbac);

$router->dispatch($request);
