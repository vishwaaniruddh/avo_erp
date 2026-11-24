<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\SalesService;
use Exception;

class SalesController extends BaseController
{
    private SalesService $salesService;

    public function __construct()
    {
        $this->salesService = new SalesService();
    }

    // -------------------------------------------------------------------------
    // Leads & Prospects
    // -------------------------------------------------------------------------

    public function getLeads(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $filters = $request->getQueryParams();
            $leads = $this->salesService->getLeads($filters, $user);
            $this->ok($leads, 'Leads fetched successfully');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createLead(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $data = $request->getBody() ?? [];
            $lead = $this->salesService->createLead($data, $user);
            $this->created($lead, 'Lead created successfully');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function updateLeadStatus(Request $request, string $id): void
    {
        try {
            $data = $request->getBody() ?? [];
            $status = $data['status'] ?? 'Contacted';
            $lostReason = $data['lost_reason'] ?? null;
            $this->salesService->updateLeadStatus((int)$id, $status, $lostReason);
            $this->ok(null, 'Lead status updated');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Field Visits
    // -------------------------------------------------------------------------

    public function getVisits(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $filters = $request->getQueryParams();
            $visits = $this->salesService->getVisits($filters, $user);
            $this->ok($visits, 'Field visits fetched successfully');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createVisit(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $data = $request->getBody() ?? [];
            $visit = $this->salesService->createVisit($data, $user);
            $this->created($visit, 'Field visit recorded with GPS check-in');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Quotations & Below-MSP Gate
    // -------------------------------------------------------------------------

    public function getQuotes(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $filters = $request->getQueryParams();
            $quotes = $this->salesService->getQuotes($filters, $user);
            $this->ok($quotes, 'Quotations fetched successfully');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function getQuote(Request $request): void
    {
        try {
            $id = $request->param('id');
            $quote = $this->salesService->getQuoteById((int)$id);
            if (!$quote) {
                $this->notFound('Quotation not found');
                return;
            }
            $this->ok($quote, 'Quotation fetched successfully');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createQuote(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $data = $request->getBody() ?? [];
            $quote = $this->salesService->createQuote($data, $user);
            $this->created($quote, 'Quotation generated and evaluated against Floor MSP');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function updateQuote(Request $request): void
    {
        try {
            $id = $request->param('id');
            $user = $request->getUser() ?? [];
            $data = $request->getBody() ?? [];
            $quote = $this->salesService->updateQuote((int)$id, $data, $user);
            $this->ok($quote, 'Quotation updated and re-evaluated against Floor MSP');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function approveQuote(Request $request): void
    {
        try {
            $id = $request->param('id');
            $user = $request->getUser() ?? [];
            // Role enforcement: VP / Director (L3) or Executive
            $isL3 = in_array($user['level'] ?? '', ['L3']) || in_array($user['role_type'] ?? '', ['Executive', 'Admin']);
            if (!$isL3) {
                $this->forbidden('Only Level 3 (VP / Director / Executive) can authorize below-MSP quotations.');
                return;
            }

            $data = $request->getBody() ?? [];
            $approved = (bool)($data['approved'] ?? true);
            $remarks = $data['remarks'] ?? null;
            $quote = $this->salesService->approveQuote((int)$id, $approved, $remarks, $user);
            $this->ok($quote, $approved ? 'Quotation approved' : 'Quotation rejected');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function convertQuoteToSO(Request $request): void
    {
        try {
            $id = $request->param('id');
            $user = $request->getUser() ?? [];
            $result = $this->salesService->convertQuoteToSO((int)$id, $user);
            $this->ok($result, 'Quotation successfully converted to Sales Order');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Tender Register & Guarantees (EMD / PBG)
    // -------------------------------------------------------------------------

    public function getTenders(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $tenders = $this->salesService->getTenders($filters);
            $this->ok($tenders, 'Tender register fetched successfully');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createTender(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $data = $request->getBody() ?? [];
            $tender = $this->salesService->createTender($data, $user);
            $this->created($tender, 'Tender record logged successfully');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function updateTenderOutcome(Request $request): void
    {
        try {
            $id = $request->param('id');
            $data = $request->getBody() ?? [];
            $this->salesService->updateTenderOutcome((int)$id, $data);
            $this->ok(null, 'Tender outcome and L1 price updated');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function getGuarantees(Request $request): void
    {
        try {
            $guarantees = $this->salesService->getGuarantees();
            $this->ok($guarantees, 'Bank guarantees and EMDs fetched');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // SI Multi-Bid RFP Clusters
    // -------------------------------------------------------------------------

    public function getClusters(Request $request): void
    {
        try {
            $data = $this->salesService->getClusters();
            $this->ok($data, 'SI multi-bid RFP clusters fetched');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createCluster(Request $request): void
    {
        try {
            $data = $request->getBody() ?? [];
            $result = $this->salesService->createCluster($data);
            $this->created($result, 'SI multi-bid cluster logged');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function updateClusterOutcome(Request $request): void
    {
        try {
            $id = (int)$request->param('id');
            $data = $request->getBody() ?? [];
            $this->salesService->updateClusterOutcome($id, $data);
            $this->ok(['id' => $id], 'Cluster outcome updated successfully');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    public function deleteCluster(Request $request): void
    {
        try {
            $id = (int)$request->param('id');
            $this->salesService->deleteCluster($id);
            $this->ok(['id' => $id], 'Cluster deleted successfully');
        } catch (Exception $e) {
            $this->badRequest($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Order Milestones & Audit Package
    // -------------------------------------------------------------------------

    public function getOrderMilestones(Request $request): void
    {
        try {
            $user = $request->getUser() ?? [];
            $orders = $this->salesService->getOrderMilestones($user);
            $this->ok($orders, 'Order milestones fetched');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function getAuditPackage(Request $request): void
    {
        try {
            $id = $request->param('id');
            $package = $this->salesService->getAuditPackage((int)$id);
            $this->ok($package, 'Audit package ready for download');
        } catch (Exception $e) {
            $this->notFound($e->getMessage());
        }
    }
}
