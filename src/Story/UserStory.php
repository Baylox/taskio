<?php

namespace App\Story;

use Zenstruck\Foundry\Story;

use App\Factory\AccountFactory;


final class UserStory extends Story
{
    public function build(): void
    {
        // 20 user accounts linked to the user role
        AccountFactory::createMany(20, fn() => [
            'role' => 'ROLE_USER',
        ]);

        // 1 admin account
        AccountFactory::createOne([
            'email' => 'admin@example.com',
            'role'  => 'ROLE_ADMIN',
            'password' => 'adminpassword',
            'isVerified' => true,
            'name'      => 'Admin',
            'lastname'  => 'User',
        ]);

        // 1 super admin account
        AccountFactory::createOne([
            'email' => 'superadmin@example.com',
            'role'  => 'ROLE_SUPER_ADMIN',
            'password' => 'superadminpassword',
            'isVerified' => true,
            'name'      => 'Super',
            'lastname'  => 'Admin',
        ]);
    }
}
