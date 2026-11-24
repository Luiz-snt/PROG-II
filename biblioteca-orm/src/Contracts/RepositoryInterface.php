<?php
declare(strict_types=1);

namespace BibliotecaORM\Contracts;

interface RepositoryInterface
{
    public function buscar(int $id): ?object;

    /** @return object[] */
    public function listar(): array;

    /** Insere (id nulo) ou atualiza (id existente) e devolve a entidade. */
    public function salvar(object $entidade): object;

    public function excluir(int $id): bool;
}
