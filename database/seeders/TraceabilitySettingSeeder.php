<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TraceabilitySetting;
use Illuminate\Database\Seeder;

class TraceabilitySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'digit_count' => 4,
                'range_start' => 1001,
                'range_end'   => 9999,
                'last_used'   => 0,
                'label'       => 'Série 4 dígitos',
                'is_active'   => true,
            ],
            [
                'digit_count' => 6,
                'range_start' => 100001,
                'range_end'   => 599999,
                'last_used'   => 0,
                'label'       => 'Série 6 dígitos',
                'is_active'   => true,
            ],
        ];

        foreach ($settings as $setting) {
            TraceabilitySetting::updateOrCreate(
                ['digit_count' => $setting['digit_count']],
                $setting
            );
        }

        $this->command->info('Configurações de rastreabilidade criadas: 4 e 6 dígitos.');
    }
}
