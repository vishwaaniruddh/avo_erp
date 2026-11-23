<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\IdentityService;
use InvalidArgumentException;

class IdentityController extends BaseController
{
    public function getRoles(Request $request): void
    {
        $this->ok([
            'role_types' => array_values(IdentityService::ROLE_TYPES),
            'archetypes' => IdentityService::ARCHETYPES,
            'formula' => 'User Access = Role Type + Scope + Level (SS-23, SS-33)',
        ], 'Identity role definitions retrieved');
    }

    public function getUsers(Request $request): void
    {
        $users = IdentityService::getAllUsers();
        $this->ok($users, 'User directory retrieved');
    }

    public function createUser(Request $request): void
    {
        $actor = $request->getUser();
        try {
            $user = IdentityService::createUser($request->input(), $actor);
            $this->created($user, 'User provisioned with 3-attribute tuple');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to create user', $e->getMessage());
        }
    }

    public function updateUser(Request $request): void
    {
        $id = (int) $request->param('id');
        $actor = $request->getUser();
        try {
            $user = IdentityService::updateUser($id, $request->input(), $actor);
            $this->ok($user, 'User attributes updated');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to update user', $e->getMessage());
        }
    }

    public function deleteUser(Request $request): void
    {
        $id = (int) $request->param('id');
        $actor = $request->getUser();
        try {
            IdentityService::deleteUser($id, $actor);
            $this->ok(null, 'User successfully deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (\Exception $e) {
            $this->serverError('Failed to delete user', $e->getMessage());
        }
    }

    public function getMatrix(Request $request): void
    {
        $matrix = IdentityService::getPermissionMatrix();
        $this->ok($matrix, 'Permission matrix retrieved');
    }

    public function updateMatrix(Request $request): void
    {
        $id = (int) $request->param('id');
        $actor = $request->getUser();
        $res = IdentityService::updatePermissionMatrixRow($id, $request->input(), $actor);
        $this->ok($res, 'Permission matrix updated');
    }

    public function bulkUpdateRole(Request $request): void
    {
        $role = $request->param('role');
        $preset = $request->input('preset') ?? 'read_only';
        $actor = $request->getUser();
        $res = IdentityService::bulkUpdateRole($role, $preset, $actor);
        $this->ok($res, "Permissions for role '{$role}' updated to preset '{$preset}'");
    }
}

