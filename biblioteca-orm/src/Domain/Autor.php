<?php
declare(strict_types=1);

namespace BibliotecaORM\Domain;

use InvalidArgumentException;
use Stringable;

final class Autor implements Entidade, Stringable
{
    // Sem setter: o id só entra pelo construtor (hidratação) ou pelo repositório após o INSERT.
    private ?int $id;
    private string $nome;
    private ?string $nacionalidade;

    public function __construct(string $nome, ?string $nacionalidade = null, ?int $id = null)
    {
        if ($id !== null && $id <= 0) {
            throw new InvalidArgumentException('Identificador inválido.');
        }
        $this->id = $id;
        $this->setNome($nome);
        $this->setNacionalidade($nacionalidade);
    }

    public function getId(): ?int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getNacionalidade(): ?string { return $this->nacionalidade; }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '' || mb_strlen($nome) > 120) {
            throw new InvalidArgumentException('O nome do autor é obrigatório (até 120 caracteres).');
        }
        $this->nome = $nome;
    }

    public function setNacionalidade(?string $nacionalidade): void
    {
        $nacionalidade = $nacionalidade === null ? null : trim($nacionalidade);
        if ($nacionalidade === '') {
            $nacionalidade = null;
        }
        if ($nacionalidade !== null && mb_strlen($nacionalidade) > 60) {
            throw new InvalidArgumentException('A nacionalidade deve ter até 60 caracteres.');
        }
        $this->nacionalidade = $nacionalidade;
    }

    public function __toString(): string
    {
        return $this->nome . ($this->nacionalidade ? " ({$this->nacionalidade})" : '');
    }
}
