<?php
declare(strict_types=1);

namespace BibliotecaORM\Infrastructure;

use BibliotecaORM\Domain\Autor;
use BibliotecaORM\Domain\Livro;
use PDO;
use Throwable;

final class LivroRepository extends AbstractRepository
{
    public function __construct(PDO $pdo, private AutorRepository $autores)
    {
        parent::__construct($pdo);
    }

    protected function tabela(): string { return 'livros'; }
    protected function colunas(): array { return ['titulo', 'isbn', 'ano_publicacao']; }
    protected function classeEntidade(): string { return Livro::class; }

    protected function extrair(object $entidade): array
    {
        /** @var Livro $entidade */
        return [
            'titulo'         => $entidade->getTitulo(),
            'isbn'           => $entidade->getIsbn(),
            'ano_publicacao' => $entidade->getAnoPublicacao(),
        ];
    }

    public function hidratar(array $linha): Livro
    {
        return new Livro($linha['titulo'], $linha['isbn'], (int) $linha['ano_publicacao'], (int) $linha['id']);
    }

    public function buscar(int $id): ?Livro
    {
        $livro = parent::buscar($id);
        if ($livro !== null) {
            $this->carregarAutores([$livro]);
        }
        return $livro;
    }

    public function listar(): array
    {
        $livros = parent::listar();
        $this->carregarAutores($livros); // 1 consulta para todos (evita N+1)
        return $livros;
    }

    /** Livro + vínculos N:N na MESMA transação: ou grava tudo, ou nada. */
    public function salvar(object $entidade): Livro
    {
        $this->garantirTipo($entidade);
        /** @var Livro $entidade */
        $eraNovo = $entidade->getId() === null;
        $autoresNovos = array_filter($entidade->getAutores(), fn(Autor $a) => $a->getId() === null);

        try {
            return $this->emTransacao(function () use ($entidade) {
                parent::salvar($entidade);
                $this->sincronizarVinculos($entidade);
                return $entidade;
            });
        } catch (Throwable $e) {
            // O banco desfez o INSERT/UPDATE, mas os objetos ainda têm id preenchido. Limpa para não confundir o código consumidor.
            if ($eraNovo) {
                $this->definirId($entidade, null);
            }
            foreach ($autoresNovos as $autor) {
                $this->definirId($autor, null);
            }
            throw $e;
        }
    }

    private function sincronizarVinculos(Livro $livro): void
    {
        $del = $this->pdo->prepare('DELETE FROM livros_autores WHERE livro_id = :livro');
        $del->execute([':livro' => $livro->getId()]);

        $ins = $this->pdo->prepare('INSERT INTO livros_autores (livro_id, autor_id) VALUES (:livro, :autor)');
        foreach ($livro->getAutores() as $autor) {
            if ($autor->getId() === null) {
                $this->autores->salvar($autor);
            }
            $ins->execute([':livro' => $livro->getId(), ':autor' => $autor->getId()]);
        }
    }

    /**
     * Carrega os autores de vários livros de uma vez. Um mapa id => Autor garante que o mesmo
     * autor seja UMA única instância em todos os livros deste carregamento (identity map).
     *
     * @param Livro[] $livros
     */
    private function carregarAutores(array $livros): void
    {
        if ($livros === []) {
            return;
        }
        $porId = [];
        foreach ($livros as $livro) {
            $porId[$livro->getId()] = $livro;
        }
        $marcadores = implode(',', array_fill(0, count($porId), '?'));
        $st = $this->pdo->prepare(
            "SELECT la.livro_id, a.* FROM livros_autores la
               JOIN autores a ON a.id = la.autor_id
              WHERE la.livro_id IN ($marcadores)
              ORDER BY a.nome"
        );
        $st->execute(array_keys($porId));

        $mapa = [];
        foreach ($st->fetchAll() as $linha) {
            $autorId = (int) $linha['id'];
            $mapa[$autorId] ??= $this->autores->hidratar($linha);
            $porId[(int) $linha['livro_id']]->adicionarAutor($mapa[$autorId]);
        }
    }
}
