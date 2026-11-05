<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetAdminPassword extends Command
{
    /**
     * Reset the password for the super admin (or any account by email) so an
     * operator who has been locked out can log back in. Handy on local/dev
     * setups where the seeded password was changed and the hash can't be read.
     */
    protected $signature = 'admin:reset-password
                            {--email= : Email of the account to reset (defaults to the super admin)}
                            {--password= : New password (prompted securely if omitted; blank = "password123")}
                            {--keep-change-flag : Leave must_change_password enabled instead of clearing it}';

    protected $description = 'Reset the password for the super admin (or a given account) so you can log back in';

    public function handle(): int
    {
        $email = $this->option('email');

        $user = $email
            ? User::where('email', $email)->first()
            : User::where('role', 'super_admin')->orderBy('id')->first();

        if (!$user) {
            $this->error($email
                ? "No user found with email: {$email}"
                : 'No super_admin account found in the database.');

            return self::FAILURE;
        }

        $password = $this->option('password');

        if ($password === null) {
            $password = $this->secret("New password for {$user->email} (blank = password123)");
        }

        if (!$password) {
            $password = 'password123';
        }

        // The User model casts 'password' => 'hashed', so assigning the plain
        // value here stores a proper bcrypt hash (and never double-hashes).
        $user->password = $password;

        if (!$this->option('keep-change-flag')) {
            $user->must_change_password = false;
        }

        $user->save();

        $this->info("\u{2713} Password reset for {$user->email} (role: {$user->role}).");
        $this->line('  You can now log in with that password.');

        return self::SUCCESS;
    }
}
