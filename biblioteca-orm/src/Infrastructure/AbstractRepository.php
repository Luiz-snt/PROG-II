<?php
declare(strict_types=1);

namespace BibliotecaORM\Infrastructure;

use BibliotecaORM\Contracts\RepositoryInterface;
use BibliotecaORM\Domain\Entidade;
use InvalidArgumentException;
use PDO;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * Comportamentos compartilhados do mapeamento objeto-relacional:
 * buscar, listar, salvar (INSERT/UPDATE), excluir e transação.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct(protected PDO $pdo)
    {
        // Nomes de tabela/coluna vêm SEMPRE das subclasses (lista fixa), nunca de entrada externa.
        $this->validarIdentificador($this->tabela());
        foreach ($this->colunas() as $coluna) {
            $this->validarIdentificador($coluna);
        }
    }

    abstract protected function tabela(): string;

    /** @return string[] colunas graváveis (whitelist), sem o id */
    abstract protected function colunas(): array;

    /** @return class-string<Entidade> */
    abstract protected function classeEntidade(): string;

    /** Objeto -> [coluna => valor] */
    abstract protected function extrair(object $entidade): array;

    /** Linha do banco -> objeto (hidratação) */
    abstract public function hidratar(array $linha): object;

    // ---------- comportamentos compartilhados ----------

    public function buscar(int $id): ?object
    {
        $st = $this->pdo->prepare(sprintf('SELECT * FROM `%s` WHERE id = :id', $this->tabela()));
        $st->execute([':id' => $id]);
        $linha = $st->fetch();

        return $linha === false ? null : $this->hidratar($linha);
    }

    public function listar(): array
    {
        $st = $this->pdo->query(sprintf('SELECT * FROM `%s` ORDER BY id', $this->tabela()));

        return array_map(fn(array $l) => $this->hidratar($l), $st->fetchAll());
    }

    public function salvar(object $entidade): object
    {
        $this->garantirTipo($entidade);

        return $this->emTransacao(function () use ($entidade) {
            $entidade->getId() === null ? $this->inserir($entidade) : $this->atualizar($entidade);
            return $entidade;
        });
    }

    public function excluir(int $id): bool
    {
        $st = $this->pdo->prepare(sprintf('DELETE FROM `%s` WHERE id = :id', $this->tabela()));
        $st->execute([':id' => $id]);

        return $st->rowCount() > 0;
    }

    /** Executa $acao em transação; se já houver uma aberta, participa dela. Rollback em qualquer exceção. */
    public function emTransacao(callable $acao): mixed
    {
        if ($this->pdo->inTransaction()) {
            return $acao();
        }
        $this->pdo->beginTransaction();
        try {
            $resultado = $acao();
            $this->pdo->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // ---------- internos ----------

    protected function inserir(object $entidade): void
    {
        $dados = $this->extrair($entidade);
        $cols  = $this->colunas();
        $sql   = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->tabela(),
            implode(', ', array_map(fn($c) => "`$c`", $cols)),
            implode(', ', array_map(fn($c) => ":$c", $cols))
        );
        $this->pdo->prepare($sql)->execute($this->parametros($dados));
        $this->definirId($entidade, (int) $this->pdo->lastInsertId());
    }

    protected function atualizar(object $entidade): void
    {
        $dados = $this->extrair($entidade);
        $sets  = implode(', ', array_map(fn($c) => "`$c` = :$c", $this->colunas()));
        $sql   = sprintf('UPDATE `%s` SET %s WHERE id = :id', $this->tabela(), $sets);
        $st    = $this->pdo->prepare($sql);
        $st->execute($this->parametros($dados) + [':id' => $entidade->getId()]);

        if ($st->rowCount() === 0) {
            throw new RuntimeException('Registro não encontrado para atualização.');
        }
    }

    /** Só o repositório grava a identidade*/
    protected function definirId(object $entidade, ?int $id): void
    {
        (new ReflectionProperty($entidade, 'id'))->setValue($entidade, $id);
    }

    protected function garantirTipo(object $entidade): void
    {
        $classe = $this->classeEntidade();
        if (!$entidade instanceof $classe) {
            throw new InvalidArgumentException(sprintf('%s só persiste objetos %s.', static::class, $classe));
        }
    }

    private function parametros(array $dados): array
    {
        $params = [];
        foreach ($this->colunas() as $coluna) {
            $params[":$coluna"] = $dados[$coluna];
        }
        return $params;
    }

    private function validarIdentificador(string $nome): void
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/', $nome)) {
            throw new InvalidArgumentException("Identificador SQL não permitido: {$nome}");
        }
    }
}
