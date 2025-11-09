<?php

namespace App\Filament\Pages\Auth;

// import the base Register class
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Register extends BaseRegister
{
    protected function handleRegistration(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // ✅ Assign default role here
        $user->assignRole('user');

        return $user;
    }
}
