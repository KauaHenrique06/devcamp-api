<?php

namespace App\Services\Auth;

use App\Exceptions\ApiException;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Contracts\User as ContractsUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialAuthService 
{
    public function redirect(array $data): array
    {
        $provider = $data['provider'];
        $redirectUri = $this->resolveRedirectUri($data, $provider);

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        $url = $driver
            ->stateless()
            ->redirectUrl($redirectUri)
            ->redirect()
            ->getTargetUrl();
        
        return ['url' => $url];
    }

    public function callback(array $data)
    {
        request()->merge(['code' => urldecode($data['code'])]);
        
        $provider = $data['provider'];
        $redirectUri = $this->resolveRedirectUri($data, $provider);

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        $socialUser = $driver
            ->stateless()
            ->redirectUrl($redirectUri)
            ->user();

        if(! $socialUser->getEmail()) {
            throw new ApiException("The provider doesn't return an e-mail!", 422);
        }

        $user = $this->findOrCreateUser($socialUser, $provider);

        return $this->responseWithToken(JWTAuth::fromUser($user), $user);
    }

    protected function resolveRedirectUri(array $data, string $provider)
    {
        $default = Config::get("services.{$provider}.redirect");

        if(empty($data['redirect_uri'])) {
            return $default;
        }

        $allowed = Config::get("services.{$provider}.allowed_redirects", [ $default ]);

        if(! in_array($data['redirect_uri'], $allowed, true)) {
            throw new ApiException('Not allowed redirect origin!', 422);
        }

        return $data['redirect_uri'];
    }

    protected function findOrCreateUser(ContractsUser $socialUser, string $provider): User
    {
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();
        
        if($user) {
            return $user;
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if($user) {
            $user->update([
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar'      => $socialUser->getAvatar() ?? $user->avatar
            ]);

            return $user;
        }

        $user = User::create([
            'name'              => $socialUser->getName() ?? $socialUser->getNickname(),
            'email'             => $socialUser->getEmail(),
            'provider'          => $provider,
            'provider_id'       => $socialUser->getId(),
            'avatar'            => $socialUser->getAvatar(),
            'password'          => null,
            'email_verified_at' => now() 
        ]);

        $user->assignRole('user');

        return $user;
    }

    protected function responseWithToken(string $token, User $user): array
    {
        $refreshTtlInSeconds = Config::get('jwt.refresh_ttl') * 60;

        return [
            'user' => new UserResource($user),
            'token' => $token,
            'refresh_expires_in' => $refreshTtlInSeconds,
        ];
    }
}