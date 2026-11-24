<?php
declare(strict_types=1);

namespace BibliotecaORM\Infrastructure;

use BibliotecaORM\Domain\Autor;

final class AutorRepository extends AbstractRepository
{
    protected function tabela(): string { return 'autores'; }
    protected function colunas(): array { return ['nome', 'nacionalidade']; }
    protected function classeEntidade(): string { return Autor::class; }

    protected function extrair(object $entidade): array
    {
        /** @var Autor $entidade */
        return ['nome' => $entidade->getNome(), 'nacionalidade' => $entidade->getNacionalidade()];
    }

    public function hidratar(array $linha): Autor
    {
        return new Autor($linha['nome'], $linha['nacionalidade'], (int) $linha['id']);
    }

    public function buscar(int $id): ?Autor { return parent::buscar($id); }
    public function salvar(object $entidade): Autor { return parent::salvar($entidade); }
}
