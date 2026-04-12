<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Mapeia abreviação → ID novo
        $stateMap = State::pluck('id', 'abbreviation');

        // Busca cidades do banco legado com a UF do estado
        $legacyCities = DB::connection('legacy')
            ->table('cidade')
            ->join('estado', 'cidade.estado', '=', 'estado.id')
            ->select('cidade.nome', 'estado.uf')
            ->orderBy('estado.uf')
            ->orderBy('cidade.nome')
            ->get();

        $rows = [];
        $now = now();

        foreach ($legacyCities as $city) {
            $stateId = $stateMap[$city->uf] ?? null;
            if (! $stateId) {
                continue;
            }

            $rows[] = [
                'state_id'   => $stateId,
                'name'       => $city->nome,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert em lotes de 500
        foreach (array_chunk($rows, 500) as $chunk) {
            City::insert($chunk);
        }

        $this->command->info(count($rows) . ' cidades importadas do banco legado.');
    }
}
