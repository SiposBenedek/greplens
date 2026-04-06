<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeUserCommand extends Command
{
    protected $signature = 'make:user';
    protected $description = 'Create a new user interactively';

    public function handle(): int
    {
        $name     = $this->ask('Name');
        $email    = $this->ask('Email');
        $password = $this->secret('Password');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("User {$email} created successfully.");

        return self::SUCCESS;
    }
}
