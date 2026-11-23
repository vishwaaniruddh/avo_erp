<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Invoice;
use App\Services\InvoiceService;
use InvalidArgumentException;
use RuntimeException;

class InvoiceController extends BaseController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('search', ''));

        $paginated = InvoiceService::getPaginated($page, $limit, $status, $search);
        $this->ok($paginated, 'Invoices fetched successfully');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $invoice = Invoice::findWithDetails($id);
        if (!$invoice) {
            $this->notFound("Invoice with ID {$id} not found");
            return;
        }

        $this->ok($invoice, 'Invoice details fetched');
    }

    public function generate(Request $request): void
    {
        $user = $request->getUser();
        $orderId = (int) $request->input('order_id');
        $dueDays = (int) $request->input('due_days', 30);

        if ($orderId <= 0) {
            $this->unprocessable('Valid order_id is required to generate an invoice');
            return;
        }

        try {
            $invoice = InvoiceService::generateFromOrder($orderId, $user, $dueDays);
            $this->created($invoice, 'Authoritative invoice generated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (RuntimeException $e) {
            $this->badRequest($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to generate invoice', $e->getMessage());
        }
    }

    public function pay(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $request->getUser();

        try {
            $invoice = InvoiceService::markAsPaid($id, $user);
            $this->ok($invoice, 'Invoice marked as paid');
        } catch (RuntimeException $e) {
            $this->notFound($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to update invoice payment', $e->getMessage());
        }
    }
}

