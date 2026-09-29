<?php
class Usuario {
    private $senha;

    public function __construct($senha) {
        $this->senha = $senha;
    }

    public function verificarSenha($senhaDigitada) {
        return $this->senha === $senhaDigitada;
    }
}