<?php
class Cliente {
    public $nome;
    protected $cpf;
    private $telefone;

    public function __construct($nome, $cpf, $telefone) {
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
    }

    public function testarAcessos() {
        return "Dentro da classe: Nome={$this->nome}, CPF={$this->cpf}, Tel={$this->telefone}";
    }
}