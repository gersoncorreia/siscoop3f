<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production.
     */
    public function run(): void
    {
        // 1. Create Master Admin for Central Domain
        $adminEmail = 'eudemirfernando@gmail.com'; // ALTERE PARA SEU EMAIL

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Eudemir Fernando',
                'password' => bcrypt('Mudar@123#'), // ALTERE IMEDIATAMENTE
                'email_verified_at' => now(),
            ]
        );

        echo "Master Admin criado com: $adminEmail / Mudar@123# \n";
    }
}
