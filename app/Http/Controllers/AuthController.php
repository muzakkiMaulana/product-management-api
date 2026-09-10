<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'username' => $request->validated('username'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $guard = Auth::guard('web');

        if (! $guard->once($request->validated())) {
            throw new AuthenticationException('Invalid username or password.');
        }

        $tokens = DB::transaction(fn () => $this->issueTokens($guard->user()));

        return response()->json(['data' => $tokens])
            ->header('Cache-Control', 'no-store');
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $tokens = DB::transaction(function () use ($request) {
            // Only one request may consume a refresh token, including concurrent requests.
            $refreshToken = RefreshToken::where('token_hash', hash('sha256', $request->validated('refresh_token')))
                ->lockForUpdate()
                ->first();

            if (! $refreshToken || ! $refreshToken->expires_at->isFuture()) {
                throw new AuthenticationException('Invalid or expired refresh token.');
            }

            $user = $refreshToken->user;
            $refreshToken->accessToken->delete();

            return $this->issueTokens($user);
        }, 3);

        return response()->json(['data' => $tokens])
            ->header('Cache-Control', 'no-store');
    }

    /** @return array<string, mixed> */
    private function issueTokens(User $user): array
    {
        $accessExpiresAt = now()->addMinutes(config('tokens.access_ttl_minutes'));
        $refreshExpiresAt = now()->addMinutes(config('tokens.refresh_ttl_minutes'));
        $accessToken = $user->createToken('api', ['*'], $accessExpiresAt);
        $plainRefreshToken = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'personal_access_token_id' => $accessToken->accessToken->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'expires_at' => $refreshExpiresAt,
        ]);

        return [
            'user' => new UserResource($user),
            'authentication_token' => $accessToken->plainTextToken,
            'refresh_token' => $plainRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('tokens.access_ttl_minutes') * 60,
            'expires_at' => $accessExpiresAt->toISOString(),
            'refresh_expires_at' => $refreshExpiresAt->toISOString(),
        ];
    }
}
