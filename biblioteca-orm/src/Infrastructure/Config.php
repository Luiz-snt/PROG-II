<?php
declare(strict_types=1);

namespace BibliotecaORM\Infrastructure;

/** Lê configuração do arquivo .env (se existir) ou de variáveis de ambiente.*/
final class Config
{
    public static function carregar(): array
    {
        $env = [];
        $arquivo = dirname(__DIR__, 2) . '/.env';
        if (is_file($arquivo)) {
            foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
                $linha = trim($linha);
                if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $linha, 2);
                $env[trim($k)] = trim($v, " \t\"'");
            }
        }
        $pega = static fn(string $k, string $padrao): string => $env[$k] ?? (getenv($k) !== false ? getenv($k) : $padrao);

        return [
            'host' => $pega('DB_HOST', '127.0.0.1'),
            'port' => (int) $pega('DB_PORT', '3306'),
            'name' => $pega('DB_NAME', 'biblioteca_orm'),
            'user' => $pega('DB_USER', 'root'),
            'pass' => $pega('DB_PASS', ''),
        ];
    }
}
