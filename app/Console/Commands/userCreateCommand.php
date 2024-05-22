<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class userCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command to create user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user['name'] = $this->ask('name of the user?');
        $user['email'] = $this->ask('email of the user?');
        $user['password'] = $this->secret('What is the password?');
        // I hashed the password in the UseObserver

        $roleName = $this->choice('Role of the user', ['admin', 'editor'], 1);
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error('Role not found');

            return -1;
        }
        $validator = Validator::make($user, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'email', 'unique:'.User::class],
            'password' => ['required', Password::defaults()],

        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return -1;
        }

        DB::transaction(function () use ($user, $role) {
            $newUser = User::create($user);
            $newUser->roles()->attach($role->id);
        });

        $this->info('user with'.$user['email'].' created succfuly');

        return 0;

    }
}
