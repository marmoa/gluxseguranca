<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AttributeInputType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientContract;
use App\Models\Equipment;
use App\Models\Item;
use App\Models\Norm;
use App\Models\Standard;
use App\Models\State;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MigrateLegacyData extends Command
{
    protected $signature = 'migrate:legacy-data
                            {--fresh : Limpa tabelas de destino antes de importar (DESTRUTIVO)}';

    protected $description = 'Migra dados mestres do sistema legado (glux_hom) para o sistema novo';

    public function handle(): int
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║   Migração de Dados Mestres — G-Lux          ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->newLine();

        try {
            DB::connection('legacy')->getPdo();
            $this->line('  <fg=green>✓</> Conexão com banco legado OK');
        } catch (Throwable $e) {
            $this->error('  Não foi possível conectar ao banco legado: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            if (! $this->input->isInteractive() || $this->confirm('  ⚠️  --fresh irá APAGAR dados existentes nas tabelas de destino. Confirmar?', false)) {
                $this->truncateTables();
            } else {
                $this->info('  Operação cancelada.');

                return self::FAILURE;
            }
        }

        $this->newLine();

        // Ordem de inserção garante que FKs sejam resolvidas
        $standardMap = $this->migrateStandards();
        $tagMap = $this->migrateTags();
        $normMap = $this->migrateNorms();
        [$attributeMap, $attrValueMap] = $this->migrateAttributes();
        $itemMap = $this->migrateItems($standardMap, $tagMap);
        $this->migrateItemPivots($itemMap, $attributeMap, $attrValueMap, $normMap);
        $stateMap = State::pluck('id', 'abbreviation')->toArray();
        $cityMap = $this->buildCityMap($stateMap);
        $clientMap = $this->migrateClients($standardMap, $stateMap, $cityMap);
        $this->migrateClientContracts($clientMap);
        $this->migrateEquipment();
        $this->migrateUsers($clientMap);

        $this->newLine();
        $this->info('✅ Migração concluída com sucesso!');
        $this->newLine();

        return self::SUCCESS;
    }

    /** @return array<int,int> legacyId → newId */
    private function migrateStandards(): array
    {
        $this->line('  → <comment>Padrões</comment>...');

        $rows = DB::connection('legacy')->table('padroes')->get();
        $map = [];
        $now = now();

        foreach ($rows as $row) {
            $record = Standard::firstOrCreate(
                ['name' => trim($row->name)],
                [
                    'description' => $row->descricao,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $map[$row->idpadrao] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($map).' padrões');

        return $map;
    }

    /** @return array<int,int> legacyId → newId */
    private function migrateTags(): array
    {
        $this->line('  → <comment>Etiquetas</comment>...');

        $rows = DB::connection('legacy')->table('etiquetas')->where('ativo', 'S')->get();
        $map = [];
        $now = now();

        foreach ($rows as $row) {
            $record = Tag::firstOrCreate(
                ['name' => trim($row->nome_etiqueta)],
                ['created_at' => $row->create_date ?? $now, 'updated_at' => $now]
            );
            $map[$row->idetiquetas] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($map).' etiquetas');

        return $map;
    }

    /** @return array<int,int> legacyId → newId */
    private function migrateNorms(): array
    {
        $this->line('  → <comment>Normas</comment>...');

        $rows = DB::connection('legacy')->table('normas')->where('ativo', 'S')->get();
        $map = [];
        $now = now();

        foreach ($rows as $row) {
            $normName = trim($row->nome_norma);

            $record = Norm::firstOrCreate(
                ['code' => Str::limit($normName, 50, '')],
                [
                    'name' => $normName,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $map[$row->idnormas] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($map).' normas');

        return $map;
    }

    /**
     * @return array{array<int,int>, array<int,int>}
     *                                               [attributeMap (legacyId→newId), attrValueMap (legacyId→newId)]
     */
    private function migrateAttributes(): array
    {
        $this->line('  → <comment>Atributos e Valores</comment>...');

        $legacyAttrs = DB::connection('legacy')->table('atributos')
            ->where('ativo', 'S')
            ->get();

        $attributeMap = [];
        $now = now();

        foreach ($legacyAttrs as $row) {
            $inputType = match ($row->input_type) {
                '2' => AttributeInputType::Select->value,
                default => AttributeInputType::Text->value,
            };

            $record = Attribute::firstOrCreate(
                ['name' => trim($row->nome_atributo)],
                [
                    'input_type' => $inputType,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $attributeMap[$row->idatributos] = $record->id;
        }

        // Valores dos atributos select
        $legacyValues = DB::connection('legacy')->table('atributo_valor')->get();
        $attrValueMap = [];
        $sortCounters = [];

        foreach ($legacyValues as $row) {
            $newAttrId = $attributeMap[$row->idref_atributo] ?? null;

            if ($newAttrId === null) {
                continue;
            }

            $sortCounters[$newAttrId] = ($sortCounters[$newAttrId] ?? 0) + 1;

            $record = AttributeValue::firstOrCreate(
                ['attribute_id' => $newAttrId, 'value' => trim($row->valor)],
                [
                    'sort_order' => $sortCounters[$newAttrId],
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $attrValueMap[$row->id] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($attributeMap).' atributos, '.count($attrValueMap).' valores');

        return [$attributeMap, $attrValueMap];
    }

    /**
     * @param  array<int,int>  $standardMap
     * @param  array<int,int>  $tagMap
     * @return array<int,int> legacyId → newId
     */
    private function migrateItems(array $standardMap, array $tagMap): array
    {
        $this->line('  → <comment>Itens</comment>...');

        $rows = DB::connection('legacy')->table('itens')->where('ativo', 'S')->get();
        $map = [];
        $now = now();

        foreach ($rows as $row) {
            $record = Item::firstOrCreate(
                ['name' => trim($row->nome_item), 'standard_id' => $standardMap[$row->idref_padrao] ?? null],
                [
                    'digit_count' => (int) $row->quantidade_digitos,
                    'expiration_months' => (int) $row->vencimento,
                    'tag_id' => $tagMap[$row->idref_etiqueta] ?? null,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $map[$row->iditens] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($map).' itens');

        return $map;
    }

    /**
     * @param  array<int,int>  $itemMap
     * @param  array<int,int>  $attributeMap
     * @param  array<int,int>  $attrValueMap
     * @param  array<int,int>  $normMap
     */
    private function migrateItemPivots(
        array $itemMap,
        array $attributeMap,
        array $attrValueMap,
        array $normMap
    ): void {
        $this->line('  → <comment>Vínculos (atributos, valores, normas)</comment>...');

        $now = now();

        // item_attribute pivot
        $attrPivots = DB::connection('legacy')->table('itens_atributos')->get();
        $attrCount = 0;
        $sortCounters = [];

        foreach ($attrPivots as $row) {
            $itemId = $itemMap[$row->ref_iditens] ?? null;
            $attrId = $attributeMap[$row->ref_idatributos] ?? null;

            if ($itemId === null || $attrId === null) {
                continue;
            }

            $key = "{$itemId}-{$attrId}";

            if (! DB::table('item_attribute')->where('item_id', $itemId)->where('attribute_id', $attrId)->exists()) {
                $sortCounters[$itemId] = ($sortCounters[$itemId] ?? 0) + 1;

                DB::table('item_attribute')->insert([
                    'item_id' => $itemId,
                    'attribute_id' => $attrId,
                    'sort_order' => $sortCounters[$itemId],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $attrCount++;
            }
        }

        // item_attribute_value pivot (select default values)
        $valPivots = DB::connection('legacy')->table('item_atributo_valor')->get();
        $valCount = 0;

        // Build attribute_value → attribute_id lookup
        $avToAttr = AttributeValue::pluck('attribute_id', 'id')->toArray();

        foreach ($valPivots as $row) {
            $itemId = $itemMap[$row->idref_item] ?? null;
            $avId = $attrValueMap[$row->idref_atributo_valor] ?? null;

            if ($itemId === null || $avId === null) {
                continue;
            }

            $attrId = $avToAttr[$avId] ?? null;

            if ($attrId === null) {
                continue;
            }

            if (! DB::table('item_attribute_value')
                ->where('item_id', $itemId)
                ->where('attribute_value_id', $avId)
                ->exists()) {
                DB::table('item_attribute_value')->insert([
                    'item_id' => $itemId,
                    'attribute_id' => $attrId,
                    'attribute_value_id' => $avId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $valCount++;
            }
        }

        // item_norm pivot
        $normPivots = DB::connection('legacy')->table('itens_normas')->get();
        $normCount = 0;

        foreach ($normPivots as $row) {
            $itemId = $itemMap[$row->ref_iditens] ?? null;
            $normId = $normMap[$row->ref_idnormas] ?? null;

            if ($itemId === null || $normId === null) {
                continue;
            }

            if (! DB::table('item_norm')->where('item_id', $itemId)->where('norm_id', $normId)->exists()) {
                DB::table('item_norm')->insert([
                    'item_id' => $itemId,
                    'norm_id' => $normId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $normCount++;
            }
        }

        $this->line("     <fg=green>✓</> {$attrCount} vínculos atributo, {$valCount} valores default, {$normCount} normas");
    }

    /**
     * Constrói mapa de IDs do legado → IDs do sistema novo para cidades.
     * Estratégia: join com estado legado → UF → state_id novo → city por nome.
     *
     * @param  array<string,int>  $stateMap  abbreviation → new state id
     * @return array<int,int> legacyCityId → newCityId
     */
    private function buildCityMap(array $stateMap): array
    {
        $legacyCities = DB::connection('legacy')
            ->table('cidade')
            ->join('estado', 'cidade.estado', '=', 'estado.id')
            ->select('cidade.id as legacy_id', 'cidade.nome as name', 'estado.uf')
            ->get();

        // Indexar cidades novas por state_id + name para busca rápida
        $newCities = City::with('state')
            ->get(['id', 'state_id', 'name'])
            ->keyBy(fn ($c) => $c->state_id.'|'.Str::upper(trim($c->name)));

        $map = [];

        foreach ($legacyCities as $lc) {
            $newStateId = $stateMap[$lc->uf] ?? null;

            if ($newStateId === null) {
                continue;
            }

            $key = $newStateId.'|'.Str::upper(trim($lc->name));
            $newCity = $newCities[$key] ?? null;

            if ($newCity !== null) {
                $map[$lc->legacy_id] = $newCity->id;
            }
        }

        $this->line('     <fg=green>✓</> '.count($map).' cidades mapeadas');

        return $map;
    }

    /**
     * @param  array<int,int>  $standardMap
     * @param  array<string,int>  $stateMap  abbreviation → new state id
     * @param  array<int,int>  $cityMap  legacyCityId → newCityId
     * @return array<int,int> legacyClientId → newClientId
     */
    private function migrateClients(array $standardMap, array $stateMap, array $cityMap): array
    {
        $this->line('  → <comment>Clientes</comment>...');

        $rows = DB::connection('legacy')
            ->table('clientes')
            ->where('ativo', 'S')
            ->get();

        $map = [];
        $now = now();
        // Rastreia CNPJs já usados para tratar filiais com mesmo CNPJ
        $usedCnpjs = Client::whereNotNull('cnpj')->pluck('id', 'cnpj')->toArray();
        // Mapa UF legada → new state id via estado.uf
        $legacyStateToUf = DB::connection('legacy')
            ->table('estado')
            ->pluck('uf', 'id')
            ->toArray();

        foreach ($rows as $row) {
            $newStateId = isset($row->idref_estado)
                ? ($stateMap[$legacyStateToUf[$row->idref_estado] ?? ''] ?? null)
                : null;

            $tradeName = trim($row->nome_fantasia);
            $cnpj = preg_replace('/\D/', '', (string) $row->cnpj);

            // Se CNPJ já foi usado por outro cliente (filiais), importa sem CNPJ
            $cnpjToStore = (isset($usedCnpjs[$cnpj]) ? null : ($cnpj ?: null));

            $record = Client::firstOrCreate(
                ['trade_name' => $tradeName, 'cnpj' => $cnpjToStore],
                [
                    'company_name' => trim($row->razao_social),
                    'trade_name' => $tradeName,
                    'cnpj' => $cnpjToStore,
                    'email' => $row->email ?: null,
                    'phone' => $row->telefone ?: null,
                    'address' => $row->endereco ?: null,
                    'standard_id' => $standardMap[$row->idref_padrao] ?? null,
                    'state_id' => $newStateId,
                    'city_id' => $cityMap[$row->idref_cidade] ?? null,
                    'contact_name' => $row->nome_responsavel ?: null,
                    'contact_phone' => $row->celular_responsavel ?: null,
                    'contact_mobile' => $row->celular_responsavel ?: null,
                    'contact_email' => $row->email_responsavel ?: null,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );

            if ($cnpjToStore !== null) {
                $usedCnpjs[$cnpj] = $record->id;
            }

            $map[$row->idclientes] = $record->id;
        }

        $this->line('     <fg=green>✓</> '.count($map).' clientes');

        return $map;
    }

    /** @param array<int,int> $clientMap */
    private function migrateClientContracts(array $clientMap): void
    {
        $this->line('  → <comment>Contratos</comment>...');

        $rows = DB::connection('legacy')->table('cliente_contrato')->get();
        $now = now();
        $count = 0;

        foreach ($rows as $row) {
            $clientId = $clientMap[$row->idref_cliente] ?? null;

            if ($clientId === null) {
                continue;
            }

            ClientContract::firstOrCreate(
                ['client_id' => $clientId, 'number' => trim($row->contrato)],
                [
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $count++;
        }

        $this->line("     <fg=green>✓</> {$count} contratos");
    }

    private function migrateEquipment(): void
    {
        $this->line('  → <comment>Equipamentos</comment>...');

        $rows = DB::connection('legacy')->table('equipamentos')->where('ativo', 'S')->get();
        $now = now();
        $count = 0;

        foreach ($rows as $row) {
            Equipment::firstOrCreate(
                ['certificate_number' => trim($row->numero_certificado)],
                [
                    'name' => trim($row->nome_equipamento),
                    'brand' => trim($row->laboratorio) ?: null,
                    'calibrated_at' => $row->data_calibracao,
                    'is_active' => true,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );
            $count++;
        }

        $this->line("     <fg=green>✓</> {$count} equipamentos");
    }

    /** @param array<int,int> $clientMap */
    private function migrateUsers(array $clientMap): void
    {
        $this->line('  → <comment>Usuários</comment>...');

        $rows = DB::connection('legacy')->table('usuarios')->where('situacao', 'A')->get();
        $now = now();
        $count = 0;

        // Pré-carrega emails já usados para detecção rápida de conflito
        $usedEmails = User::pluck('email', 'login')->toArray();

        foreach ($rows as $row) {
            $role = match ($row->permissao) {
                'admin' => 'admin',
                'campo' => 'campo',
                default => 'comum',
            };

            $login = trim($row->login);
            $email = strtolower(trim($row->email));

            // E-mail duplicado no legado: gera sintético para não violar unique(email)
            if (isset($usedEmails[$login]) === false &&
                in_array($email, array_values($usedEmails), true)) {
                $email = $login.'@legacy.import';
            }

            $user = User::firstOrCreate(
                ['login' => $login],
                [
                    'name' => trim($row->nome),
                    'login' => $login,
                    'email' => $email,
                    'cpf' => $row->cpf ?: null,
                    'phone' => $row->celular ?: null,
                    // Senha em MD5 — LegacyMd5Hasher autentica transparentemente
                    'password' => $row->senha,
                    'must_change_password' => false,
                    'is_active' => true,
                    'client_id' => $row->idcliente ? ($clientMap[$row->idcliente] ?? null) : null,
                    'created_at' => $row->create_date ?? $now,
                    'updated_at' => $now,
                ]
            );

            $usedEmails[$login] = $email;
            $user->syncRoles([$role]);
            $count++;
        }

        $this->line("     <fg=green>✓</> {$count} usuários");
    }

    private function truncateTables(): void
    {
        $this->warn('  Limpando tabelas...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'item_norm',
            'item_attribute_value',
            'item_attribute',
            'items',
            'attribute_values',
            'attributes',
            'item_norm',
            'norms',
            'tags',
            'client_contracts',
            'clients',
            'equipment',
            'standards',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        // Usuários legados (manter super admin e admin do sistema)
        User::whereNotIn('email', ['admin@glux.com.br', 'superadmin@glux.com.br'])->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->line('     <fg=green>✓</> Tabelas limpas');
    }
}
