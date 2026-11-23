<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\MasterService;
use Exception;
use InvalidArgumentException;

class MasterController extends BaseController
{
    private MasterService $masterService;

    public function __construct()
    {
        $this->masterService = new MasterService();
    }

    // -------------------------------------------------------------------------
    // COUNTRIES
    // -------------------------------------------------------------------------

    public function getCountries(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $data = $this->masterService->getCountries($filters);
            $this->ok($data, 'Countries retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createCountry(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->createCountry($request->input(), $actor);
            $this->created($res, 'Country created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create country', $e->getMessage());
        }
    }

    public function updateCountry(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->updateCountry($id, $request->input(), $actor);
            $this->ok($res, 'Country updated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update country', $e->getMessage());
        }
    }

    public function deleteCountry(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->masterService->deleteCountry($id, $actor);
            $this->ok(null, 'Country deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete country', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // STATES
    // -------------------------------------------------------------------------

    public function getStates(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $data = $this->masterService->getStates($filters);
            $this->ok($data, 'States retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createState(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->createState($request->input(), $actor);
            $this->created($res, 'State created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create state', $e->getMessage());
        }
    }

    public function updateState(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->updateState($id, $request->input(), $actor);
            $this->ok($res, 'State updated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update state', $e->getMessage());
        }
    }

    public function deleteState(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->masterService->deleteState($id, $actor);
            $this->ok(null, 'State deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete state', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // CITIES
    // -------------------------------------------------------------------------

    public function getCities(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $data = $this->masterService->getCities($filters);
            $this->ok($data, 'Cities retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createCity(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->createCity($request->input(), $actor);
            $this->created($res, 'City created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create city', $e->getMessage());
        }
    }

    public function updateCity(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->updateCity($id, $request->input(), $actor);
            $this->ok($res, 'City updated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update city', $e->getMessage());
        }
    }

    public function deleteCity(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->masterService->deleteCity($id, $actor);
            $this->ok(null, 'City deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete city', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // ZONES
    // -------------------------------------------------------------------------

    public function getZones(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $data = $this->masterService->getZones($filters);
            $this->ok($data, 'Zones retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createZone(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->createZone($request->input(), $actor);
            $this->created($res, 'Zone created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create zone', $e->getMessage());
        }
    }

    public function updateZone(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->updateZone($id, $request->input(), $actor);
            $this->ok($res, 'Zone updated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update zone', $e->getMessage());
        }
    }

    public function deleteZone(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->masterService->deleteZone($id, $actor);
            $this->ok(null, 'Zone deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete zone', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // COURIERS
    // -------------------------------------------------------------------------

    public function getCouriers(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $data = $this->masterService->getCouriers($filters);
            $this->ok($data, 'Couriers retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createCourier(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->createCourier($request->input(), $actor);
            $this->created($res, 'Courier created successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create courier', $e->getMessage());
        }
    }

    public function updateCourier(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->masterService->updateCourier($id, $request->input(), $actor);
            $this->ok($res, 'Courier updated successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update courier', $e->getMessage());
        }
    }

    public function deleteCourier(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->masterService->deleteCourier($id, $actor);
            $this->ok(null, 'Courier deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete courier', $e->getMessage());
        }
    }
}
