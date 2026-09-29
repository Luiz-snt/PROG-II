<?php
class Funcionario {
    protected $salario;

    public function __construct($salario) {
        $this->salario = $salario;
    }
}

class Gerente extends Funcionario {
    public function setSalario($novoSalario) {
        $this->salario = $novoSalario;
    }

    public function getSalario() {
        return $this->salario;
    }
}