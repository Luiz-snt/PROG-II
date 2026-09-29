<?php
class ConexaoBD {
    private $conexao;

    private function conectar() {
        $this->conexao = "Conectado ao Banco de Dados (Método Privado Executado!)";
        return $this->conexao;
    }

    public function getConexao() {
        if (!$this->conexao) {
            return $this->conectar();
        }
        return $this->conexao;
    }
}