<?php
class ContaBancaria09 {
    private $saldo;

    public function __construct($saldo = 0) {
        $this->saldo = $saldo;
    }

    public function depositar($valor) {
        $this->saldo += $valor;
    }

    // Refatorado para validar o saldo
    public function sacar($valor) {
        if ($this->saldo >= $valor) {
            $this->saldo -= $valor;
            return true;
        }
        return false;
    }

    public function getSaldo() {
        return $this->saldo;
    }
}