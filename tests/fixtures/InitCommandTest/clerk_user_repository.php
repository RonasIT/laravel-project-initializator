<?php

namespace App\Support\Clerk;

use App\Models\Role;
use App\Models\User;
use Lcobucci\JWT\Token;
use RonasIT\Clerk\Repositories\UserRepository;

class ClerkUserRepository extends UserRepository
{
    public function fromToken(Token $token): User
    {
        $user = parent::fromToken($token);

        return User::firstOrCreate([
            'clerk_id' => $user->getAuthIdentifier(),
        ], [
            'role_id' => Role::USER,
        ]);
    }
}