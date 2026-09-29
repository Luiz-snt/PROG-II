<?php
// LIGA A EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega os ficheiros
require_once "../classes/atividade01.php";
require_once "../classes/atividade02.php";
require_once "../classes/atividade03.php";
require_once "../classes/atividade04.php";
require_once "../classes/atividade05.php";
require_once "../classes/atividade06.php";
require_once "../classes/atividade07.php";
require_once "../classes/atividade08.php";
require_once "../classes/atividade09.php";
require_once "../classes/atividade10.php";
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Atividades - PROG II</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --border: #e2e8f0;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2rem;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .card h3 {
            margin-top: 0;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.75rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .badge {
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .result {
            background: var(--bg);
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Consolas', monospace;
            font-size: 0.95rem;
            color: #334155;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Lista de Atividades Resolvidas</h1>
        
        <div class="grid">
            <!-- Atividade 01 -->
            <div class="card">
                <h3><span class="badge">01</span> Atributos Públicos</h3>
                <div class="result">
                    <?php
                        $prod1 = new Produto01("Caderno", 15.50);
                        echo "Produto: {$prod1->nome}<br>Preço: R$ " . number_format($prod1->preco, 2, ',', '.');
                    ?>
                </div>
            </div>

            <!-- Atividade 02 -->
            <div class="card">
                <h3><span class="badge">02</span> Encapsulamento Preço</h3>
                <div class="result">
                    <?php
                        $prod2 = new Produto02("Mochila", 120.00);
                        $prod2->setPreco(110.00);
                        echo "Produto: {$prod2->nome}<br>Preço: R$ " . number_format($prod2->getPreco(), 2, ',', '.');
                    ?>
                </div>
            </div>

            <!-- Atividade 03 -->
            <div class="card">
                <h3><span class="badge">03</span> Conta Sem Validação</h3>
                <div class="result">
                    <?php
                        $conta1 = new ContaBancaria03(100);
                        $conta1->sacar(200);
                        echo "Tentativa de sacar R$ 200.<br>Saldo final: R$ " . number_format($conta1->getSaldo(), 2, ',', '.');
                    ?>
                </div>
            </div>

            <!-- Atividade 04 -->
            <div class="card">
                <h3><span class="badge">04</span> Protected (Gerente)</h3>
                <div class="result">
                    <?php
                        $gerente = new Gerente(3000);
                        $gerente->setSalario(4500);
                        echo "Salário do Gerente acessado via método:<br>R$ " . number_format($gerente->getSalario(), 2, ',', '.');
                    ?>
                </div>
            </div>

            <!-- Atividade 05 -->
            <div class="card">
                <h3><span class="badge">05</span> Private (Senha)</h3>
                <div class="result">
                    <?php
                        $user = new Usuario("secreta");
                        echo "Senha 'secreta' é válida? <strong>" . ($user->verificarSenha("secreta") ? "Sim" : "Não") . "</strong><br>";
                        echo "Senha '123' é válida? <strong>" . ($user->verificarSenha("123") ? "Sim" : "Não") . "</strong>";
                    ?>
                </div>
            </div>

            <!-- Atividade 06 -->
            <div class="card">
                <h3><span class="badge">06</span> Protected (Config)</h3>
                <div class="result">
                    <?php
                        $config = new AppConfig();
                        $config->setParametro("cor", "Azul");
                        echo "Parâmetro salvo e lido pela subclasse:<br>Cor = <strong>" . $config->getParametro("cor") . "</strong>";
                    ?>
                </div>
            </div>

            <!-- Atividade 07 -->
            <div class="card">
                <h3><span class="badge">07</span> Private Array (Pedido)</h3>
                <div class="result">
                    <?php
                        $pedido = new Pedido();
                        $pedido->inserirItem("Caneta");
                        $pedido->inserirItem("Borracha");
                        echo "Itens no pedido:<br>• " . implode("<br>• ", $pedido->listarItens());
                    ?>
                </div>
            </div>

            <!-- Atividade 08 -->
            <div class="card">
                <h3><span class="badge">08</span> Visibilidade Cliente</h3>
                <div class="result">
                    <?php
                        $cliente = new Cliente("Carlos", "111.222.333-44", "9999-9999");
                        echo "<strong>Interno:</strong><br>" . $cliente->testarAcessos() . "<br><br>";
                        echo "<strong>Externo (apenas público):</strong><br>Nome = " . $cliente->nome;
                    ?>
                </div>
            </div>

            <!-- Atividade 09 -->
            <div class="card">
                <h3><span class="badge">09</span> Conta Com Validação</h3>
                <div class="result">
                    <?php
                        $conta2 = new ContaBancaria09(100);
                        echo "Sacar R$ 200: " . ($conta2->sacar(200) ? "✅ Sucesso" : "❌ Negado") . "<br>";
                        echo "Sacar R$ 50: " . ($conta2->sacar(50) ? "✅ Sucesso" : "❌ Negado") . "<br><br>";
                        echo "Saldo final: R$ " . number_format($conta2->getSaldo(), 2, ',', '.');
                    ?>
                </div>
            </div>

            <!-- Atividade 10 -->
            <div class="card">
                <h3><span class="badge">10</span> Método Privado (BD)</h3>
                <div class="result">
                    <?php
                        $bd = new ConexaoBD();
                        echo "Status da ligação:<br><strong>" . $bd->getConexao() . "</strong>";
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>