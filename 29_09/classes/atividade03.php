<?php
class ContaBancaria03 {
    private $saldo;

    public function __construct($saldo = 0) {
        $this->saldo = $saldo;
    }

    public function depositar($valor) {
        $this->saldo += $valor;
    }

    public function sacar($valor) {
        $this->saldo -= $valor;
    }

    public function getSaldo() {
        return $this->saldo;
    }
}