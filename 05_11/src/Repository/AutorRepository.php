<?php

class AutorRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Cadastra um novo autor e retorna o ID gerado (AUTO_INCREMENT).
     */
    public function create(string $nome, ?string $nacionalidade = null): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO autor (nome, nacionalidade) VALUES (:nome, :nacionalidade)'
            );

            $stmt->execute([
                ':nome' => $nome,
                ':nacionalidade' => $nacionalidade
            ]);

            // Retorna o ID atribuído pelo banco (atende ao Exercício 1)
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new \Exception("Erro ao cadastrar autor: " . $e->getMessage());
        }
    }

    /**
     * Busca um autor pelo ID.
     */
    public function buscarPorId(int $id): ?Autor
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, nacionalidade FROM autor WHERE id = :id');
        $stmt->execute([':id' => $id]);
        
        $linha = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($linha === false) {
            return null;
        }

        $autor = new Autor($linha['nome'], $linha['nacionalidade']);
        $autor->setId((int)$linha['id']);

        return $autor;
    }
}