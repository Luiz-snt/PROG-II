<?php
class Produto02 {
    public $nome;
    private $preco;

    public function __construct($nome, $preco) {
        $this->nome = $nome;
        $this->setPreco($preco);
    }

    public function getPreco() {
        return $this->preco;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }
}