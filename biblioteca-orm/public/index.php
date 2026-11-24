<?php
declare(strict_types=1);

/**
 * Interface web da BibliotecaORM.
 */
require __DIR__ . '/../src/autoload.php';

use BibliotecaORM\Domain\Autor;
use BibliotecaORM\Domain\Livro;
use BibliotecaORM\Infrastructure\AutorRepository;
use BibliotecaORM\Infrastructure\Database;
use BibliotecaORM\Infrastructure\LivroRepository;

session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));

function e(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function ir(string $pagina): never { header('Location: ?p=' . $pagina); exit; }
function aviso(string $tipo, string $texto): void { $_SESSION['aviso'] = [$tipo, $texto]; }

function mensagemBanco(PDOException $ex): string
{
    return match ($ex->errorInfo[1] ?? 0) {
        1062    => 'Já existe um livro com esse ISBN.',
        1451    => 'Não foi possível excluir: este autor ainda está vinculado a livros. Remova o vínculo editando os livros.',
        1452    => 'Referência inválida: o autor ou livro informado não existe.',
        default => 'Erro no banco de dados. Verifique a conexão e o schema.',
    };
}

// ---------- conexão ----------
try {
    $pdo     = Database::conectar();
    $autores = new AutorRepository($pdo);
    $livros  = new LivroRepository($pdo, $autores);
} catch (PDOException $ex) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;max-width:40rem;margin:3rem auto;padding:0 1rem">'
       . '<h1>Sem conexão com o banco</h1><p>Confira o arquivo <code>.env</code> (copie de <code>.env.example</code>) '
       . 'e se o schema foi importado (<code>sql/schema.sql</code>).</p></body>';
    exit;
}

// ---------- ações (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destino = 'livros';
    try {
        if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('Sessão expirada. Recarregue a página e tente de novo.');
        }
        switch ($_POST['acao'] ?? '') {
            case 'livro_salvar':
                $id    = (int) ($_POST['id'] ?? 0);
                $livro = $id > 0 ? $livros->buscar($id) : null;
                if ($id > 0 && $livro === null) {
                    throw new RuntimeException('Livro não encontrado.');
                }
                if ($livro === null) {
                    $livro = new Livro((string) $_POST['titulo'], (string) $_POST['isbn'], (int) $_POST['ano']);
                } else {
                    $livro->setTitulo((string) $_POST['titulo']);
                    $livro->setIsbn((string) $_POST['isbn']);
                    $livro->setAnoPublicacao((int) $_POST['ano']);
                }
                $escolhidos = [];
                foreach ((array) ($_POST['autores'] ?? []) as $aid) {
                    $autor = $autores->buscar((int) $aid) ?? throw new RuntimeException('Autor selecionado não existe.');
                    $escolhidos[] = $autor;
                }
                $livro->definirAutores($escolhidos);
                $livros->salvar($livro);
                aviso('ok', $id > 0 ? 'Livro atualizado.' : 'Livro cadastrado.');
                break;

            case 'livro_excluir':
                $livros->excluir((int) $_POST['id']);
                aviso('ok', 'Livro excluído.');
                break;

            case 'autor_salvar':
                $destino = 'autores';
                $autores->salvar(new Autor((string) $_POST['nome'], (string) ($_POST['nacionalidade'] ?? '')));
                aviso('ok', 'Autor cadastrado.');
                break;

            case 'autor_excluir':
                $destino = 'autores';
                $autores->excluir((int) $_POST['id']);
                aviso('ok', 'Autor excluído.');
                break;

        }
    } catch (PDOException $ex) {
        aviso('erro', mensagemBanco($ex));
    } catch (InvalidArgumentException | RuntimeException $ex) {
        aviso('erro', $ex->getMessage());
    }
    ir($destino);
}

// ---------- dados da página (GET) ----------
$pagina = in_array($_GET['p'] ?? '', ['livros', 'autores'], true) ? $_GET['p'] : 'livros';
$aviso  = $_SESSION['aviso'] ?? null;
unset($_SESSION['aviso']);

$listaLivros  = $livros->listar();
$listaAutores = $autores->listar();
$editando     = isset($_GET['editar']) ? $livros->buscar((int) $_GET['editar']) : null;
$idsEditando  = $editando ? array_map(fn(Autor $a) => $a->getId(), $editando->getAutores()) : [];

$usoPorAutor = [];
foreach ($listaLivros as $l) {
    foreach ($l->getAutores() as $a) {
        $usoPorAutor[$a->getId()] = ($usoPorAutor[$a->getId()] ?? 0) + 1;
    }
}
$lombadas = ['#8c2f39', '#1f4e79', '#4a6741', '#b07d2b', '#5b3a6e', '#2b6f77'];
$csrf = $_SESSION['csrf'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Biblioteca — acervo</title>
<style>
  :root {
    --tinta: #1b2433; --suave: #5a6577; --fundo: #eceff3; --cartao: #ffffff; --linha: #d3d9e2;
    --acento: #1f4e79; --acento-escuro: #163a5b; --ok-bg: #e3f1e6; --ok: #1d5a2b; --erro-bg: #fbe6e4; --erro: #9b1c14;
  }
  @media (prefers-color-scheme: dark) {
    :root { --tinta: #e6eaf1; --suave: #9aa5b8; --fundo: #12171f; --cartao: #1b222d; --linha: #2e3847;
            --acento: #6aa5da; --acento-escuro: #8bbbe8; --ok-bg: #173323; --ok: #8fd4a0; --erro-bg: #3a1a18; --erro: #f2a39d; }
  }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--fundo); color: var(--tinta); font: 16px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; }
  h1, h2, h3 { font-family: Georgia, "Times New Roman", serif; font-weight: 700; line-height: 1.2; margin: 0 0 .75rem; }
  header { background: var(--cartao); border-bottom: 1px solid var(--linha); }
  .topo { max-width: 72rem; margin: 0 auto; padding: 1rem 1.25rem 0; }
  .topo h1 { font-size: 1.6rem; margin-bottom: .25rem; }
  .topo p { margin: 0 0 .75rem; color: var(--suave); }
  nav { display: flex; gap: .25rem; }
  nav a { padding: .6rem 1rem; text-decoration: none; color: var(--suave); border-bottom: 3px solid transparent; font-weight: 600; }
  nav a[aria-current="page"] { color: var(--tinta); border-color: var(--acento); }
  nav a:hover { color: var(--tinta); }
  main { max-width: 72rem; margin: 0 auto; padding: 1.5rem 1.25rem 3rem; }
  .grade { display: grid; grid-template-columns: minmax(18rem, 22rem) 1fr; gap: 1.5rem; align-items: start; }
  @media (max-width: 52rem) { .grade { grid-template-columns: 1fr; } }
  .painel { background: var(--cartao); border: 1px solid var(--linha); border-radius: 6px; padding: 1.25rem; }
  .painel h2 { font-size: 1.15rem; }
  label { display: block; font-weight: 600; font-size: .9rem; margin: .85rem 0 .25rem; }
  input[type=text], input[type=number], select {
    width: 100%; padding: .55rem .65rem; font: inherit; color: inherit; background: var(--fundo);
    border: 1px solid var(--linha); border-radius: 4px;
  }
  select[multiple] { min-height: 8.5rem; }
  .dica { font-size: .82rem; color: var(--suave); margin: .25rem 0 0; }
  button, .botao { font: inherit; font-weight: 600; padding: .55rem 1rem; border-radius: 4px; border: 1px solid var(--acento);
    background: var(--acento); color: #fff; cursor: pointer; text-decoration: none; display: inline-block; }
  button:hover, .botao:hover { background: var(--acento-escuro); }
  @media (prefers-color-scheme: dark) { button, .botao { color: #0d1117; } }
  .secundario { background: transparent; color: var(--acento); }
  .secundario:hover { background: var(--fundo); }
  .perigo { background: transparent; color: var(--erro); border-color: var(--erro); padding: .3rem .65rem; font-size: .85rem; }
  .perigo:hover { background: var(--erro-bg); }
  :focus-visible { outline: 3px solid var(--acento); outline-offset: 2px; }
  .acoes { display: flex; gap: .5rem; margin-top: 1.1rem; flex-wrap: wrap; }
  .aviso { padding: .7rem 1rem; border-radius: 4px; margin-bottom: 1.25rem; font-weight: 500; }
  .aviso.ok { background: var(--ok-bg); color: var(--ok); }
  .aviso.erro { background: var(--erro-bg); color: var(--erro); }
  .busca { margin-bottom: 1rem; }
  .livro { display: flex; background: var(--cartao); border: 1px solid var(--linha); border-radius: 6px; margin-bottom: .75rem; overflow: hidden; }
  .lombada { width: 14px; flex: none; }
  .livro-corpo { padding: .9rem 1.1rem; flex: 1; min-width: 0; }
  .livro h3 { font-size: 1.1rem; margin-bottom: .2rem; }
  .meta { color: var(--suave); font-size: .88rem; }
  .autores { margin: .5rem 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: .35rem; }
  .autores li { background: var(--fundo); border: 1px solid var(--linha); border-radius: 999px; padding: .1rem .7rem; font-size: .85rem; }
  .livro-acoes { display: flex; gap: .5rem; align-items: center; margin-top: .75rem; }
  .livro-acoes form { margin: 0; }
  .vazio { padding: 2rem 1rem; text-align: center; color: var(--suave); border: 1px dashed var(--linha); border-radius: 6px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { text-align: left; padding: .6rem .5rem; border-bottom: 1px solid var(--linha); font-size: .95rem; }
  th { color: var(--suave); font-weight: 600; font-size: .85rem; }
  pre { background: var(--fundo); border: 1px solid var(--linha); border-radius: 4px; padding: 1rem; overflow-x: auto; white-space: pre-wrap; margin: 1rem 0 0; }
  @media (prefers-reduced-motion: no-preference) { button, .botao, nav a { transition: background-color .15s, color .15s; } }
</style>
</head>
<body>
<header>
  <div class="topo">
    <h1>Biblioteca da universidade</h1>
    <p><?= count($listaLivros) ?> livro(s) e <?= count($listaAutores) ?> autor(es) no acervo</p>
    <nav aria-label="Seções">
      <a href="?p=livros" <?= $pagina === 'livros' ? 'aria-current="page"' : '' ?>>Livros</a>
      <a href="?p=autores" <?= $pagina === 'autores' ? 'aria-current="page"' : '' ?>>Autores</a>
    </nav>
  </div>
</header>

<main>
<?php if ($aviso): ?>
  <div class="aviso <?= e($aviso[0]) ?>" role="status"><?= e($aviso[1]) ?></div>
<?php endif; ?>

<?php if ($pagina === 'livros'): ?>
  <div class="grade">
    <section class="painel" aria-labelledby="form-livro">
      <h2 id="form-livro"><?= $editando ? 'Editar livro' : 'Cadastrar livro' ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="acao" value="livro_salvar">
        <input type="hidden" name="id" value="<?= e($editando?->getId() ?? '') ?>">

        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" required maxlength="200" value="<?= e($editando?->getTitulo()) ?>">

        <label for="isbn">Código</label>
        <input type="text" id="isbn" name="isbn" required placeholder="978-85-359-0277-5" value="<?= e($editando?->getIsbn()) ?>">

        <label for="ano">Ano de publicação</label>
        <input type="number" id="ano" name="ano" required min="1000" max="<?= (int) date('Y') + 1 ?>" value="<?= e($editando?->getAnoPublicacao()) ?>">

        <label for="autores">Autores</label>
        <?php if ($listaAutores): ?>
          <select id="autores" name="autores[]" multiple>
            <?php foreach ($listaAutores as $a): ?>
              <option value="<?= e($a->getId()) ?>" <?= in_array($a->getId(), $idsEditando, true) ? 'selected' : '' ?>><?= e($a->getNome()) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="dica">Segure Ctrl para escolher mais de um autor.</p>
        <?php else: ?>
          <p class="dica">Nenhum autor ainda. <a href="?p=autores">Cadastre um autor</a> primeiro.</p>
        <?php endif; ?>

        <div class="acoes">
          <button type="submit"><?= $editando ? 'Salvar alterações' : 'Cadastrar livro' ?></button>
          <?php if ($editando): ?><a class="botao secundario" href="?p=livros">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section aria-labelledby="lista-livros">
      <h2 id="lista-livros">Acervo</h2>
      <input class="busca" type="text" id="filtro" placeholder="Filtrar por título, autor ou ISBN" aria-label="Filtrar livros"
             style="width:100%;padding:.6rem .75rem;border:1px solid var(--linha);border-radius:4px;background:var(--cartao);color:inherit;font:inherit">
      <?php if (!$listaLivros): ?>
        <div class="vazio">O acervo está vazio. Cadastre o primeiro livro no formulário ao lado.</div>
      <?php endif; ?>
      <?php foreach ($listaLivros as $l): ?>
        <article class="livro" data-busca="<?= e(mb_strtolower($l->getTitulo() . ' ' . $l->getIsbn() . ' ' . implode(' ', array_map(fn(Autor $a) => $a->getNome(), $l->getAutores())))) ?>">
          <div class="lombada" style="background:<?= e($lombadas[$l->getId() % count($lombadas)]) ?>"></div>
          <div class="livro-corpo">
            <h3><?= e($l->getTitulo()) ?></h3>
            <div class="meta"><?= e($l->getAnoPublicacao()) ?> · Código <?= e($l->getIsbn()) ?></div>
            <?php if ($l->getAutores()): ?>
              <ul class="autores">
                <?php foreach ($l->getAutores() as $a): ?><li><?= e($a->getNome()) ?></li><?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="meta">Sem autores vinculados.</div>
            <?php endif; ?>
            <div class="livro-acoes">
              <a class="botao secundario" href="?p=livros&editar=<?= e($l->getId()) ?>">Editar</a>
              <form method="post" onsubmit="return confirm('Excluir este livro? Os vínculos com autores também serão removidos.')">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="acao" value="livro_excluir">
                <input type="hidden" name="id" value="<?= e($l->getId()) ?>">
                <button type="submit" class="perigo">Excluir</button>
              </form>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  </div>
  <script>
    document.getElementById('filtro').addEventListener('input', function (ev) {
      var q = ev.target.value.toLowerCase().trim();
      document.querySelectorAll('.livro').forEach(function (el) {
        el.style.display = el.dataset.busca.indexOf(q) === -1 ? 'none' : '';
      });
    });
  </script>

<?php elseif ($pagina === 'autores'): ?>
  <div class="grade">
    <section class="painel" aria-labelledby="form-autor">
      <h2 id="form-autor">Cadastrar autor</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="acao" value="autor_salvar">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required maxlength="120">
        <label for="nac">Nacionalidade</label>
        <input type="text" id="nac" name="nacionalidade" maxlength="60">
        <div class="acoes"><button type="submit">Cadastrar autor</button></div>
      </form>
    </section>

    <section aria-labelledby="lista-autores">
      <h2 id="lista-autores">Autores</h2>
      <?php if (!$listaAutores): ?>
        <div class="vazio">Nenhum autor cadastrado.</div>
      <?php else: ?>
        <div class="painel" style="padding:.5rem 1rem">
          <table>
            <thead><tr><th>Nome</th><th>Nacionalidade</th><th>Livros</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($listaAutores as $a): $uso = $usoPorAutor[$a->getId()] ?? 0; ?>
              <tr>
                <td><?= e($a->getNome()) ?></td>
                <td><?= e($a->getNacionalidade() ?? '—') ?></td>
                <td><?= $uso ?></td>
                <td>
                  <?php if ($uso === 0): ?>
                    <form method="post" style="margin:0" onsubmit="return confirm('Excluir este autor?')">
                      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                      <input type="hidden" name="acao" value="autor_excluir">
                      <input type="hidden" name="id" value="<?= e($a->getId()) ?>">
                      <button type="submit" class="perigo">Excluir</button>
                    </form>
                  <?php else: ?>
                    <span class="meta">em uso</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>

<?php endif; ?>
</main>
</body>
</html>
