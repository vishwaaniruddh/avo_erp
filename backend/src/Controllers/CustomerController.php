<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Customer;
use App\Services\CustomerService;
use InvalidArgumentException;

class CustomerController extends BaseController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $search = trim((string) $request->query('search', ''));

        if ($request->query('all') === 'true') {
            $this->ok(CustomerService::getAll(), 'Customers fetched successfully');
            return;
        }

        $paginated = CustomerService::getPaginated($page, $limit, $search);
        $this->ok($paginated, 'Customers fetched successfully');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $customer = CustomerService::getById($id);
        if (!$customer) {
            $this->notFound("Customer with ID {$id} not found");
            return;
        }

        $this->ok($customer, 'Customer details fetched');
    }

    public function store(Request $request): void
    {
        $user = $request->getUser();
        $input = $request->input();

        try {
            $customer = CustomerService::create($input, $user);
            $this->created($customer, 'Customer created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Could not create customer', $e->getMessage());
        }
    }
}

