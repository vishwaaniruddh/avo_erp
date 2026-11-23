<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends BaseController
{
    public function login(Request $request): void
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if (empty($email) || empty($password)) {
            $this->unprocessable('Email and password are required', [
                'email' => empty($email) ? 'Email is required' : null,
                'password' => empty($password) ? 'Password is required' : null,
            ]);
            return;
        }

        $result = AuthService::authenticate($email, $password);
        if (!$result) {
            $this->unauthorized('Invalid email or password credentials');
            return;
        }

        $this->ok($result, 'Login successful');
    }

    public function me(Request $request): void
    {
        $authUser = $request->getUser();
        if (!$authUser) {
            $this->unauthorized('Unauthenticated');
            return;
        }

        $user = User::find($authUser['id']);
        if (!$user) {
            $this->notFound('User not found');
            return;
        }

        unset($user['password_hash']);
        $this->ok($user, 'User profile fetched');
    }
}

