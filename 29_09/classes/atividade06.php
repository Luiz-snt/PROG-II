<?php
class Config {
    protected $parametros = [];
}

class AppConfig extends Config {
    public function setParametro($chave, $valor) {
        $this->parametros[$chave] = $valor;
    }

    public function getParametro($chave) {
        return $this->parametros[$chave] ?? null;
    }
}