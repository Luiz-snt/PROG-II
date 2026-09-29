<?php
class Pedido {
    private $itens = [];

    public function inserirItem($item) {
        $this->itens[] = $item;
    }

    public function listarItens() {
        return $this->itens;
    }
}