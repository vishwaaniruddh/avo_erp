<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Order;
use App\Services\OrderService;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends BaseController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $status = trim((string) $request->query('status', ''));
        $search = trim((string) $request->query('search', ''));

        $paginated = OrderService::getPaginated($page, $limit, $status, $search);
        $this->ok($paginated, 'Orders fetched successfully');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $order = Order::findWithItems($id);
        if (!$order) {
            $this->notFound("Order with ID {$id} not found");
            return;
        }

        $this->ok($order, 'Order details fetched');
    }

    public function store(Request $request): void
    {
        $user = $request->getUser();
        $input = $request->input();

        try {
            $order = OrderService::createOrder($input, $user);
            $this->created($order, 'Order placed successfully');
        } catch (InvalidArgumentException $e) {
            // Validation failed or stock availability failed or credit limit exceeded
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to create order', $e->getMessage());
        }
    }

    public function approve(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $request->getUser();

        try {
            $order = OrderService::approveOrder($id, $user);
            $this->ok($order, 'Order approved successfully');
        } catch (RuntimeException $e) {
            $this->forbidden($e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to approve order', $e->getMessage());
        }
    }

    public function reject(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $request->getUser();
        $reason = trim((string) $request->input('reason', 'Rejected by Manager'));

        try {
            $order = OrderService::rejectOrder($id, $reason, $user);
            $this->ok($order, 'Order rejected');
        } catch (RuntimeException $e) {
            $this->forbidden($e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to reject order', $e->getMessage());
        }
    }
}

