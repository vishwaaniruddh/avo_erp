<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Product;
use App\Services\ProductService;
use InvalidArgumentException;
use RuntimeException;

class ProductController extends BaseController
{
    public function index(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $search = trim((string) $request->query('search', ''));

        // If client specifically asks for all products (e.g. for dropdowns in order form)
        if ($request->query('all') === 'true') {
            $this->ok(ProductService::getAll(), 'Products fetched successfully');
            return;
        }

        $paginated = ProductService::getPaginated($page, $limit, $search);
        $this->ok($paginated, 'Products fetched successfully');
    }

    public function show(Request $request): void
    {
        $id = (int) $request->param('id');
        $product = ProductService::getById($id);
        if (!$product) {
            $this->notFound("Product with ID {$id} not found");
            return;
        }

        $this->ok($product, 'Product details fetched');
    }

    public function store(Request $request): void
    {
        $user = $request->getUser();
        $input = $request->input();

        try {
            $product = ProductService::create($input, $user);
            $this->created($product, 'Product created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Could not create product', $e->getMessage());
        }
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $request->getUser();
        $input = $request->input();

        try {
            $product = ProductService::update($id, $input, $user);
            $this->ok($product, 'Product updated successfully');
        } catch (RuntimeException $e) {
            $this->notFound($e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Could not update product', $e->getMessage());
        }
    }

    public function adjustStock(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $request->getUser();
        $delta = (int) $request->input('delta', 0);
        $reason = (string) $request->input('reason', 'Manual Adjustment');

        if ($delta === 0) {
            $this->badRequest('Stock adjustment delta cannot be 0');
            return;
        }

        try {
            $product = ProductService::updateStock($id, $delta, $user, $reason);
            $this->ok($product, 'Product stock updated successfully');
        } catch (RuntimeException $e) {
            $this->notFound($e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        }
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        $product = Product::find($id);
        if (!$product) {
            $this->notFound("Product with ID {$id} not found");
            return;
        }

        Product::delete($id);
        $this->ok(null, 'Product deleted successfully');
    }
}

