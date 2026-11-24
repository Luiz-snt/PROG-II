<?php
declare(strict_types=1);

namespace BibliotecaORM\Domain;

/** Toda entidade persistível expõe sua identidade (somente leitura). */
interface Entidade
{
    public function getId(): ?int;
}
