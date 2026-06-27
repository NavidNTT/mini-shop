<?php

namespace Modules\Auth\Services;

use Modules\Auth\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function register(array $data)
    {
        $user = $this->authRepository->create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('کاربر جدید ثبت‌نام کرد', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function login(array $data)
    {
        $user = $this->authRepository->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            Log::warning('تلاش ناموفق برای ورود', [
                'email' => $data['email'],
                'ip' => request()->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['اطلاعات ورود اشتباه است.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('کاربر وارد شد', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout($user)
    {
        $user->tokens()->delete();

        Log::info('کاربر خارج شد', [
            'user_id' => $user->id,
        ]);
    }
}
