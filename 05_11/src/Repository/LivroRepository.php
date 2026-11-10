<?php

class LivroRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * READ: Retorna todos os livros cadastrados.
     */
    public function getAll(): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, titulo, isbn, ano_publicacao, preco, quantidade, id_autor FROM livro ORDER BY titulo');
        $stmt->execute();
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return empty($linhas) ? null : $linhas;
    }

    /**
     * READ: Busca por ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, titulo, isbn, ano_publicacao, preco, quantidade, id_autor FROM livro WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $linha;
    }

    /**
     * CREATE: Insere um novo livro.
     */
    public function create(string $titulo, string $isbn, int $anoPublicacao, float $preco, int $idAutor, int $quantidade = 0): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor)
             VALUES (:titulo, :isbn, :ano_publicacao, :preco, :quantidade, :id_autor)'
        );

        return $stmt->execute([
            ':titulo' => $titulo,
            ':isbn' => $isbn,
            ':ano_publicacao' => $anoPublicacao,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_autor' => $idAutor
        ]);
    }

    /**
     * UPDATE: Atualiza os dados de um livro.
     */
    public function update(int $id, string $titulo, string $isbn, int $anoPublicacao, float $preco, int $idAutor, int $quantidade): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE livro 
             SET titulo = :titulo, isbn = :isbn, ano_publicacao = :ano_publicacao, 
                 preco = :preco, quantidade = :quantidade, id_autor = :id_autor 
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':titulo' => $titulo,
            ':isbn' => $isbn,
            ':ano_publicacao' => $anoPublicacao,
            ':preco' => $preco,
            ':quantidade' => $quantidade,
            ':id_autor' => $idAutor
        ]);
    }

    /**
     * DELETE: Remove um livro com base no ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM livro WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * TCL / Transação: Baixa de estoque atômica com transação explícita.
     */
    public function baixarEstoque(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE livro SET quantidade = quantidade - 1 WHERE id = :id AND quantidade > 0'
            );
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * READ (Consulta Relacional): Lista livros integrando os dados do autor via LEFT JOIN (Exercício 7).
     * @return array|null
     */
    public function listarComAutores(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT 
                l.id, 
                l.titulo, 
                l.isbn, 
                l.ano_publicacao, 
                l.preco, 
                l.quantidade, 
                l.id_autor, 
                a.nome AS autor_nome
             FROM livro AS l
             LEFT JOIN autor AS a ON a.id = l.id_autor
             ORDER BY l.titulo ASC'
        );

        $stmt->execute();
        $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($linhas)) {
            return null;
        }

        return $linhas;
    }
}