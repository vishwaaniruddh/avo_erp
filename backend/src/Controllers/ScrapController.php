<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\ScrapService;
use InvalidArgumentException;
use RuntimeException;

class ScrapController extends BaseController
{
    public function index(Request $request): void
    {
        $user = $request->getUser();
        try {
            $ledger = ScrapService::getLedger($user);
            $this->ok($ledger, 'Scrap ledger retrieved for authorized executive');
        } catch (RuntimeException $e) {
            // Anti-collusion blind gate hit (403 Forbidden)
            $this->forbidden($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to fetch scrap ledger', $e->getMessage());
        }
    }

    public function store(Request $request): void
    {
        $user = $request->getUser();
        try {
            $res = ScrapService::submitDischarge($request->input(), $user);
            $this->created($res, $res['message']);
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to log scrap discharge', $e->getMessage());
        }
    }
}

