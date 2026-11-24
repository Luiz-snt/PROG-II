<?php
declare(strict_types=1);

// Autoloader simples (PSR-4): BibliotecaORM\Domain\Livro => src/Domain/Livro.php
spl_autoload_register(static function (string $classe): void {
    $prefixo = 'BibliotecaORM\\';
    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }
    $arquivo = __DIR__ . '/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});
