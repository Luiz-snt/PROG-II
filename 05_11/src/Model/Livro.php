<?php

class Livro
{
    private int $id;
    private string $titulo;
    private string $isbn;
    private int $anoPublicacao;
    private float $preco;
    private int $quantidade;
    private int $idAutor; // Não vou criar a classe no momento, nao foi solicitado na atividade

    public function __construct(string $titulo, string $isbn, int $anoPublicacao, float $preco, int $idAutor, int $quantidade = 0)
    {
        $this->titulo = $titulo;
        $this->isbn = $isbn;
        $this->anoPublicacao = $anoPublicacao;
        $this->preco = $preco;
        $this->quantidade = $quantidade;
        $this->idAutor = $idAutor;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    public function getAnoPublicacao(): int
    {
        return $this->anoPublicacao;
    }

    public function setPreco(int $preco): void
    {
        $this->preco = $preco;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function setAnoPublicacao(int $anoPublicacao): void
    {
        $this->anoPublicacao = $anoPublicacao;
    }

    public function getQuantidade(): int
    {
        return $this->quantidade;
    }

    public function setQuantidade(int $quantidade): void
    {
        $this->quantidade = $quantidade;
    }

    public function getIdAutor(): int
    {
        return $this->idAutor;
    }

    public function setIdAutor(int $idAutor): void
    {
        $this->idAutor = $idAutor;
    }
}