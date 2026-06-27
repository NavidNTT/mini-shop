<?php

namespace Modules\Auth\Repositories;

use App\Enums\UserRole;
use App\Models\User;

class AuthRepository
{
    public function create(array $data): User
    {
        $data['role'] = UserRole::Customer->value;

        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
