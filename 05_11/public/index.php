<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/Model/Autor.php';
require_once __DIR__ . '/../src/Model/Livro.php';
require_once __DIR__ . '/../src/Repository/AutorRepository.php';
require_once __DIR__ . '/../src/Repository/LivroRepository.php';

$mensagem = '';
$tipoMensagem = '';

try {
    $pdo = Database::conectar('mysql');
    $autorRepo = new AutorRepository($pdo);
    $livroRepo = new LivroRepository($pdo);

    // Processa o cadastro manual via Formulário (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar_livro') {
            $titulo = trim($_POST['titulo']);
            $isbn = trim($_POST['isbn']);
            $ano = (int)$_POST['ano_publicacao'];
            $preco = (float)$_POST['preco'];
            $autorId = (int)$_POST['autor_id'];
            $qtd = (int)$_POST['quantidade'];

            if (!empty($titulo) && !empty($isbn) && $autorId > 0) {
                $livroRepo->create($titulo, $isbn, $ano, $preco, $autorId, $qtd);
                $mensagem = "Livro '{$titulo}' cadastrado com sucesso!";
                $tipoMensagem = 'success';
            } else {
                $mensagem = "Preencha todos os campos obrigatórios!";
                $tipoMensagem = 'error';
            }
        }
    }
} catch (Exception $e) {
    $mensagem = "Erro de Conexão/Execução: " . $e->getMessage();
    $tipoMensagem = 'error';
}

// Busca todos os autores para preencher o <select> do formulário
$autores = [];
// Busca a lista completa de livros com autores para a tabela
$livrosComAutores = [];

if (isset($autorRepo) && isset($livroRepo)) {
    try {
        // Se houver um método listarTodos() no seu AutorRepository, utilize-o.
        // Caso contrário, uma consulta simples busca os autores:
        $stmt = $pdo->query("SELECT id, nome FROM autor ORDER BY nome ASC");
        $autores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $livrosComAutores = $livroRepo->listarComAutores();
    } catch (Exception $e) {
        // Trata eventual erro de listagem
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Gestão - Biblioteca</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f9f9f9; color: #333; }
        h1, h2 { color: #2c3e50; }
        .card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: #27ae60; font-weight: bold; padding: 10px; background: #e8f8f5; border-radius: 4px; }
        .error { color: #c0392b; font-weight: bold; padding: 10px; background: #fadbd8; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background-color: #27ae60; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background-color: #219150; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>

<div class="card">
    <h1>Painel de Gestão - Biblioteca</h1>
    <p>Projeto Integrador: <strong>Banco de Dados II</strong></p>
    
    <?php if ($mensagem): ?>
        <p class="<?= $tipoMensagem ?>"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>
</div>

<!-- FORMULÁRIO DE INSERÇÃO -->
<div class="card">
    <h2>Cadastrar Novo Livro</h2>
    <form action="" method="POST">
        <input type="hidden" name="acao" value="cadastrar_livro">
        
        <div class="grid-form">
            <div class="form-group">
                <label for="titulo">Título do Livro *</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ex: Dom Casmurro">
            </div>

            <div class="form-group">
                <label for="autor_id">Autor *</label>
                <select id="autor_id" name="autor_id" required>
                    <option value="">Selecione um autor...</option>
                    <?php foreach ($autores as $autor): ?>
                        <option value="<?= $autor['id'] ?>"><?= htmlspecialchars($autor['nome']) ?> (ID: <?= $autor['id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="isbn">ISBN *</label>
                <input type="text" id="isbn" name="isbn" required placeholder="Ex: 9788532512349">
            </div>

            <div class="form-group">
                <label for="ano_publicacao">Ano de Publicação</label>
                <input type="number" id="ano_publicacao" name="ano_publicacao" value="<?= date('Y') ?>">
            </div>

            <div class="form-group">
                <label for="preco">Preço (R$)</label>
                <input type="number" step="0.01" id="preco" name="preco" value="29.90">
            </div>

            <div class="form-group">
                <label for="quantidade">Quantidade em Estoque</label>
                <input type="number" id="quantidade" name="quantidade" value="1">
            </div>
        </div>

        <button type="submit" class="btn">Cadastrar Livro</button>
    </form>
</div>

<!-- LISTAGEM COMPLETA DO BANCO -->
<div class="card">
    <h2>Acervo Completo de Livros</h2>
    
    <?php if ($livrosComAutores): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>ISBN</th>
                    <th>Ano</th>
                    <th>Preço</th>
                    <th>Qtd</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livrosComAutores as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><strong><?= htmlspecialchars($item['titulo']) ?></strong></td>
                        <td><?= htmlspecialchars($item['autor_nome'] ?? 'Sem Autor') ?></td>
                        <td><?= htmlspecialchars($item['isbn']) ?></td>
                        <td><?= $item['ano_publicacao'] ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td><?= $item['quantidade'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum livro cadastrado no banco de dados.</p>
    <?php endif; ?>
</div>

</body>
</html>