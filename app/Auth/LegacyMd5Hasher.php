<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\BcryptHasher;

/**
 * Hasher temporário para migração transparente de senhas MD5 legadas.
 *
 * Ao fazer login, verifica primeiro se a senha bate com MD5 (sistema legado).
 * Se bater, faz rehash para bcrypt automaticamente.
 * Após todos os usuários logarem pelo menos uma vez, este hasher pode ser removido.
 */
class LegacyMd5Hasher implements Hasher
{
    public function __construct(
        private readonly BcryptHasher $bcrypt = new BcryptHasher()
    ) {}

    public function info(string $hashedValue): array
    {
        return $this->bcrypt->info($hashedValue);
    }

    public function make(string $value, array $options = []): string
    {
        return $this->bcrypt->make($value, $options);
    }

    public function check(string $value, string $hashedValue, array $options = []): bool
    {
        // Se é um hash MD5 legado (32 chars hex), verifica diretamente
        if (strlen($hashedValue) === 32 && ctype_xdigit($hashedValue)) {
            return hash_equals($hashedValue, md5($value));
        }

        return $this->bcrypt->check($value, $hashedValue, $options);
    }

    public function needsRehash(string $hashedValue, array $options = []): bool
    {
        // Se é MD5, sempre precisa de rehash
        if (strlen($hashedValue) === 32 && ctype_xdigit($hashedValue)) {
            return true;
        }

        return $this->bcrypt->needsRehash($hashedValue, $options);
    }
}
