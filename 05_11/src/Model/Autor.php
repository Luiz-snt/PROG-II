<?php

class Autor
{
    private int $id;
    private string $nome;
    private ?string $nacionalidade;

    public function __construct(string $nome, ?string $nacionalidade = null)
    {
        $this->nome = $nome;
        $this->nacionalidade = $nacionalidade;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getNacionalidade(): ?string
    {
        return $this->nacionalidade;
    }

    public function setNacionalidade(?string $nacionalidade): void
    {
        $this->nacionalidade = $nacionalidade;
    }
}