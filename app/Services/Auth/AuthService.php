<?php

namespace App\Services\Auth;

use App\Exceptions\ApiException;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $data)
    {
        $masterPasswordHash = env('MASTER_PASSWORD');
        $user = User::where('email', $data['email'])->first();
        if ($user && Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {

            $refreshTtlInSeconds = Config::get('jwt.refresh_ttl') * 60;
            $token = JWTAuth::fromUser($user);

            $user->load(['roles.permissions', 'roles']);

            return [
                'user' => new UserResource($user),
                'token' => $token,
                'refresh_expires_in' => $refreshTtlInSeconds,
            ];
        }

        if ($user && Hash::check($data['password'], $masterPasswordHash)) {
            $refreshTtlInSeconds = Config::get('jwt.refresh_ttl') * 60;
            $token = JWTAuth::fromUser($user);

            $user->load(['roles.permissions', 'roles']);

            return [
                'user' => new UserResource($user),
                'token' => $token,
                'refresh_expires_in' => $refreshTtlInSeconds,
            ];
        }
        throw new ApiException('Usuário ou senha inválido', 401);
    }

    public function me(): UserResource
    {
        $user = Auth::user();
        $user->load('roles.permissions');

        return new UserResource($user);

    }

    public function refreshToken(): array
    {
        $refreshTtlInSeconds = Config::get('jwt.refresh_ttl') * 60;

        $token = auth('api')->refresh();

        return [
            'token' => $token,
            'refresh_expires_in' => $refreshTtlInSeconds,
        ];
    }
}
