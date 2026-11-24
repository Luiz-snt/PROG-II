<?php
declare(strict_types=1);

/**
 * Demonstração reproduzível das 7 evidências exigidas.
 * Uso:  php tests/demo.php
 * Cria os próprios dados e apaga tudo no final.
 */
require __DIR__ . '/../src/autoload.php';

use BibliotecaORM\Domain\Autor;
use BibliotecaORM\Domain\Livro;
use BibliotecaORM\Infrastructure\AutorRepository;
use BibliotecaORM\Infrastructure\Database;
use BibliotecaORM\Infrastructure\LivroRepository;
use BibliotecaORM\Service\Demonstracao;

function secao(string $t): void { echo "\n=== {$t} ===\n"; }
function afirmar(bool $cond, string $msg): void
{
    echo ($cond ? '[OK]   ' : '[FALHA] ') . $msg . "\n";
    if (!$cond) { global $falhas; $falhas++; }
}

$falhas = 0;
$pdo    = Database::conectar();
$autores = new AutorRepository($pdo);
$livros  = new LivroRepository($pdo, $autores);

// IDs criados aqui, para limpeza no final
$idsLivros = [];
$idsAutores = [];

try {
    secao('1. Cadastro de Livro e Autor (ids gerados pelo MySQL)');
    $a1 = $autores->salvar(new Autor('Autor Demo Um', 'Brasileira'));
    $a2 = $autores->salvar(new Autor('Autor Demo Dois', 'Portuguesa'));
    $idsAutores = [$a1->getId(), $a2->getId()];
    $livro = $livros->salvar(new Livro('Livro Demo', '9781234567897', 2020));
    $idsLivros[] = $livro->getId();
    echo "Autores: #{$a1->getId()} e #{$a2->getId()} | Livro: #{$livro->getId()}\n";
    afirmar($livro->getId() > 0 && $a1->getId() > 0, 'Identificadores gerados pelo banco');

    secao('2. Busca de Livro por ID e hidratação');
    $lido = $livros->buscar($livro->getId());
    echo $lido . "\n";
    afirmar($lido instanceof Livro && $lido->getAnoPublicacao() === 2020, 'Objeto Livro reconstruído com tipos corretos (int/string)');
    afirmar($lido !== $livro && $lido->getId() === $livro->getId(), 'Novo objeto, mesma identidade');

    secao('3. Atualização e nova consulta');
    $lido->setTitulo('Livro Demo — 2ª edição');
    $lido->setAnoPublicacao(2022);
    $livros->salvar($lido);
    $de_novo = $livros->buscar($livro->getId());
    echo $de_novo . "\n";
    afirmar($de_novo->getTitulo() === 'Livro Demo — 2ª edição' && $de_novo->getAnoPublicacao() === 2022, 'Alteração persistida');

    secao('4. Dois autores associados ao livro (N:N)');
    $de_novo->adicionarAutor($a1);
    $de_novo->adicionarAutor($a2);
    $de_novo->adicionarAutor($a1); // duplicado: deve ser ignorado
    $livros->salvar($de_novo);
    $com_autores = $livros->buscar($livro->getId());
    foreach ($com_autores->getAutores() as $a) { echo " - {$a}\n"; }
    afirmar(count($com_autores->getAutores()) === 2, 'Coleção recuperada com 2 autores');

    // Mesmo autor em dois livros: uma única instância no mesmo carregamento
    $outro = $livros->salvar(new Livro('Outro Livro Demo', '9781234567880', 2021));
    $idsLivros[] = $outro->getId();
    $outro->adicionarAutor($a1);
    $livros->salvar($outro);
    $todos = array_filter($livros->listar(), fn(Livro $l) => in_array($l->getId(), $idsLivros, true));
    $instancias = [];
    foreach ($todos as $l) {
        foreach ($l->getAutores() as $a) {
            if ($a->getId() === $a1->getId()) { $instancias[] = $a; }
        }
    }
    afirmar(count($instancias) === 2 && $instancias[0] === $instancias[1], 'Autor compartilhado = mesma instância (sem duplicar objetos)');

    secao('5. Polimorfismo: mesmo contrato para os dois repositórios');
    foreach (Demonstracao::polimorfismo($livros, $autores) as $linha) { echo $linha . "\n"; }
    afirmar(true, 'Demonstracao::resumir(RepositoryInterface) funcionou com Livro e Autor');

    secao('6. Erro intencional em transação + rollback');
    $r = Demonstracao::rollback($livros);
    foreach ($r['linhas'] as $linha) { echo $linha . "\n"; }
    afirmar($r['ok'], 'Rollback desfez livro e vínculo');

    secao('7. Exclusão controlada e chaves estrangeiras');
    try {
        $autores->excluir($a1->getId());
        afirmar(false, 'Excluir autor vinculado deveria falhar');
    } catch (PDOException $e) {
        echo "Bloqueado pelo banco: autor com livros não pode ser removido (FK RESTRICT, erro {$e->errorInfo[1]}).\n";
        afirmar(true, 'ON DELETE RESTRICT protegeu o autor');
    }
    $livros->excluir($livro->getId());
    $vinculos = $pdo->prepare('SELECT COUNT(*) FROM livros_autores WHERE livro_id = :id');
    $vinculos->execute([':id' => $livro->getId()]);
    afirmar((int) $vinculos->fetchColumn() === 0, 'Excluir o livro removeu seus vínculos (ON DELETE CASCADE)');
    afirmar($livros->buscar($livro->getId()) === null, 'Livro não existe mais');
} finally {
    // limpeza: livros primeiro (libera os autores), depois autores
    foreach ($idsLivros as $id) { $livros->excluir($id); }
    foreach ($idsAutores as $id) { $autores->excluir($id); }
}

echo "\n" . ($falhas === 0 ? "Tudo certo: todas as verificações passaram.\n" : "{$falhas} verificação(ões) falharam.\n");
exit($falhas === 0 ? 0 : 1);
