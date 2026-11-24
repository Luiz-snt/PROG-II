<?php
declare(strict_types=1);

namespace BibliotecaORM\Domain;

use InvalidArgumentException;
use Stringable;

final class Livro implements Entidade, Stringable
{
    private ?int $id;
    private string $titulo;
    private string $isbn;
    private int $anoPublicacao;
    /** @var Autor[] */
    private array $autores = [];

    public function __construct(string $titulo, string $isbn, int $anoPublicacao, ?int $id = null)
    {
        if ($id !== null && $id <= 0) {
            throw new InvalidArgumentException('Identificador inválido.');
        }
        $this->id = $id;
        $this->setTitulo($titulo);
        $this->setIsbn($isbn);
        $this->setAnoPublicacao($anoPublicacao);
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getIsbn(): string { return $this->isbn; }
    public function getAnoPublicacao(): int { return $this->anoPublicacao; }

    public function setTitulo(string $titulo): void
    {
        $titulo = trim($titulo);
        if ($titulo === '' || mb_strlen($titulo) > 200) {
            throw new InvalidArgumentException('O título é obrigatório (até 200 caracteres).');
        }
        $this->titulo = $titulo;
    }

    public function setIsbn(string $isbn): void
    {
        $limpo = strtoupper(str_replace(['-', ' '], '', trim($isbn)));
        if (!preg_match('/^(\d{9}[\dX]|\d{13})$/', $limpo)) {
            throw new InvalidArgumentException('ISBN inválido: informe 10 ou 13 dígitos.');
        }
        $this->isbn = $limpo;
    }

    public function setAnoPublicacao(int $ano): void
    {
        $max = (int) date('Y') + 1;
        if ($ano < 1000 || $ano > $max) {
            throw new InvalidArgumentException("O ano deve estar entre 1000 e {$max}.");
        }
        $this->anoPublicacao = $ano;
    }

    /** Associa um autor sem duplicar (mesma instância ou mesmo id). */
    public function adicionarAutor(Autor $autor): void
    {
        foreach ($this->autores as $existente) {
            if ($existente === $autor
                || ($autor->getId() !== null && $existente->getId() === $autor->getId())) {
                return;
            }
        }
        $this->autores[] = $autor;
    }

    /** @param Autor[] $autores */
    public function definirAutores(array $autores): void
    {
        $this->autores = [];
        foreach ($autores as $autor) {
            $this->adicionarAutor($autor);
        }
    }

    /** @return Autor[] */
    public function getAutores(): array
    {
        return $this->autores;
    }

    public function __toString(): string
    {
        return "{$this->titulo} ({$this->anoPublicacao}) — ISBN {$this->isbn}";
    }
}
