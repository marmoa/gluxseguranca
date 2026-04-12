<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);

        // Criar usuário admin inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@glux.com.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@glux.com.br',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        $admin->syncRoles(['admin']);

        $this->command->info('Usuário admin criado: admin@glux.com.br / admin123');
        $this->command->warn('IMPORTANTE: Altere a senha no primeiro acesso!');

        // Criar super admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@glux.com.br'],
            [
                'name' => 'Super Administrador',
                'email' => 'superadmin@glux.com.br',
                'login' => 'superadmin',
                'password' => Hash::make('Glux@2026!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $superAdmin->syncRoles(['admin']);

        // Gerar permissões do Shield para o painel admin
        $this->command->info('Gerando permissões do Shield...');
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
        ]);
        $this->command->info(Artisan::output());

        // Atribuir super_admin ao super admin
        Artisan::call('shield:super-admin', [
            '--user' => $superAdmin->id,
            '--panel' => 'admin',
        ]);
        $this->command->info('Super admin configurado: superadmin@glux.com.br / Glux@2026!');
    }
}
