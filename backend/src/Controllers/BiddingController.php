<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\BiddingService;
use InvalidArgumentException;

class BiddingController extends BaseController
{
    public function index(Request $request): void
    {
        $user = $request->getUser();
        $data = BiddingService::getBids($user);
        $this->ok($data, 'RFP bids fetched with Chinese-Wall isolation active');
    }

    public function store(Request $request): void
    {
        $user = $request->getUser();
        try {
            $bid = BiddingService::submitBid($request->input(), $user);
            $this->created($bid, 'RFP quote registered. Silent deduplication indexed in background.');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to record RFP quote', $e->getMessage());
        }
    }
}

