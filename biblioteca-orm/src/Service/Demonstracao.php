<?php
declare(strict_types=1);

namespace BibliotecaORM\Service;

use BibliotecaORM\Contracts\RepositoryInterface;
use BibliotecaORM\Domain\Autor;
use BibliotecaORM\Domain\Livro;
use BibliotecaORM\Infrastructure\LivroRepository;
use PDOException;

/** Demonstrações usadas pelo script de linha de comando (tests/demo.php). */
final class Demonstracao
{
    /**
     * R5 — Polimorfismo: recebe QUALQUER RepositoryInterface e não consulta a classe concreta.
     * @return array{repositorio:string,total:int,primeiro:string}
     */
    public static function resumir(RepositoryInterface $repo): array
    {
        $itens = $repo->listar();
        return [
            'repositorio' => (new \ReflectionClass($repo))->getShortName(),
            'total'       => count($itens),
            'primeiro'    => $itens ? (string) $itens[0] : '(vazio)',
        ];
    }

    /** @return string[] linhas de saída */
    public static function polimorfismo(RepositoryInterface ...$repositorios): array
    {
        $linhas = [];
        foreach ($repositorios as $repo) {
            $r = self::resumir($repo);
            $linhas[] = sprintf('%s → %d registro(s). Primeiro: %s', $r['repositorio'], $r['total'], $r['primeiro']);
        }
        return $linhas;
    }

    /**
     * R9 — Salva um livro vinculado a um autor com id inexistente.
     * O INSERT do livro funciona, o vínculo viola a FK, e a transação inteira é desfeita.
     * @return array{linhas:string[],ok:bool}
     */
    public static function rollback(LivroRepository $livros): array
    {
        $antes   = count($livros->listar());
        $livro   = new Livro('Livro de teste (deve sumir)', '9990000000001', 2024);
        $livro->adicionarAutor(new Autor('Autor que não existe', null, 2147483000));
        $linhas  = ["Livros antes: {$antes}", 'Salvando livro + vínculo com autor inexistente...'];

        try {
            $livros->salvar($livro);
            $linhas[] = 'ERRO: era esperada uma falha.';
        } catch (PDOException $e) {
            $linhas[] = 'Falha capturada (SQLSTATE ' . $e->getCode() . '): violação de chave estrangeira.';
        }

        $depois = count($livros->listar());
        $ok     = $antes === $depois && $livro->getId() === null;
        $linhas[] = "Livros depois: {$depois}. Id do objeto em memória: " . var_export($livro->getId(), true);
        $linhas[] = $ok ? 'ROLLBACK confirmado: nada foi gravado.' : 'ROLLBACK FALHOU.';

        return ['linhas' => $linhas, 'ok' => $ok];
    }
}
