📚 Biblioteca ORM — PHP 8 Nativo & Repository Pattern

Implementação educacional de um Mapeador Objeto-Relacional (ORM) e do padrão Repository construída em PHP 8 nativo, sem dependências externas ou gerenciadores de pacotes (zero Composer).

O projeto demonstra na prática o desacoplamento entre regras de negócio e persistência de dados utilizando padrões como Data Mapper, Repository Pattern, Singleton, Reflection API e carregamento automático de classes compatível com PSR-4.

📋 Índice

Sobre o Projeto

Estrutura de Diretórios

Pré-requisitos

Instalação e Configuração

Como Executar

Exemplos de Uso

Limitações Conhecidas

Contexto Acadêmico

🎯 Sobre o Projeto

O sistema gerencia entidades de domínio (Autor e Livro) em um banco de dados relacional MySQL. Entre os principais recursos implementados estão:

Hidratação e Extração Automática: A classe AbstractRepository utiliza a Reflection API do PHP para converter registros do banco em objetos de domínio (e vice-versa) sem necessidade de mapeamento manual repetitivo.

Segurança contra SQL Injection: Todas as operações de escrita e consulta utilizam Prepared Statements via PDO.

Conexão Única (Singleton): A classe Database gerencia uma única instância de conexão PDO durante o ciclo de vida da aplicação.

Autoloader PSR-4 Nativo: Carregamento dinâmico de classes através de src/autoload.php.

Dupla Interface de Execução: Suporte para testes automatizados via terminal (CLI) e painel de demonstração via navegador (Web).

📂 Estrutura de Diretórios

biblioteca-orm/
├── .env                  # Configurações locais de ambiente (credenciais do banco)
├── .env.example          # Modelo de configuração de ambiente
├── .gitignore            # Arquivos ignorados pelo controle de versão
├── README.md             # Documentação e instruções do projeto
├── public/
│   └── index.php         # Interface Web interativa de demonstração
├── sql/
│   ├── schema.sql        # Script DDL para criação das tabelas (autores e livros)
│   └── seed.sql          # Script DML com dados iniciais para testes
├── src/
│   ├── autoload.php      # Autoloader nativo padrão PSR-4
│   ├── Contracts/
│   │   └── RepositoryInterface.php   # Contrato padrão para operações CRUD
│   ├── Domain/
│   │   ├── Entidade.php  # Classe base abstrata das entidades
│   │   ├── Autor.php     # Entidade de domínio Autor
│   │   └── Livro.php     # Entidade de domínio Livro
│   ├── Infrastructure/
│   │   ├── Config.php             # Leitor e parser do arquivo .env
│   │   ├── Database.php           # Gerenciador Singleton de conexão PDO
│   │   ├── AbstractRepository.php # Núcleo do ORM (hidratação/extração via Reflection)
│   │   ├── AutorRepository.php    # Repositório específico de Autores
│   │   └── LivroRepository.php    # Repositório específico de Livros
│   └── Service/
│       └── Demonstracao.php       # Serviço orquestrador dos fluxos de demonstração
└── tests/
    └── demo.php          # Script de validação e testes via linha de comando (CLI)


🛠️ Pré-requisitos

Antes de iniciar, certifique-se de ter instalado em seu ambiente:

PHP: Versão 8.0 ou superior.

Extensões PHP habilitadas: pdo e pdo_mysql.

Banco de Dados: MySQL 5.7+.



⚙️ Instalação e Configuração

Siga os passos abaixo para preparar o ambiente localmente:

1. Acessar a pasta do projeto

Extraia os arquivos ou acesse o diretório raiz do projeto no terminal:

cd biblioteca-orm


2. Configurar as variáveis de ambiente (.env)

Copie o arquivo de exemplo .env.example para criar o seu arquivo .env:

cp .env.example .env


Abra o arquivo .env e ajuste as credenciais conforme o seu servidor MySQL:

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=biblioteca
DB_USER=root
DB_PASS=sua_senha_aqui


3. Criar o banco de dados e importar as tabelas

Crie o banco de dados biblioteca e execute os scripts SQL localizados na pasta sql/:

mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p biblioteca < sql/schema.sql
mysql -u root -p biblioteca < sql/seed.sql


🚀 Como Executar

O projeto pode ser executado de duas maneiras:

Opção 1: Via Linha de Comando (CLI)

Para rodar o script de demonstração que executa todo o ciclo CRUD (inserção, consulta, atualização e remoção) diretamente no terminal:

php tests/demo.php


Opção 2: Via Navegador (Servidor Embutido do PHP)

Para visualizar a interface gráfica de demonstração e relatórios no navegador, inicie o servidor embutido apontando para a pasta public/:

php -S localhost:8000 -t public


Em seguida, abra o navegador e acesse:
👉 http://localhost:8000

💻 Exemplos de Uso

Abaixo está um exemplo prático de como utilizar os repositórios e entidades no código PHP:

<?php

require_once __DIR__ . '/src/autoload.php';

use Domain\Autor;
use Domain\Livro;
use Infrastructure\AutorRepository;
use Infrastructure\LivroRepository;

// Instanciação dos repositórios
$autorRepo = new AutorRepository();
$livroRepo = new LivroRepository();

// 1. Criar e salvar um novo Autor (INSERT)
$autor = new Autor();
$autor->setNome('Machado de Assis');
$autor->setNacionalidade('Brasileira');
$autorRepo->save($autor);

// 2. Criar e salvar um Livro associado ao Autor (INSERT)
$livro = new Livro();
$livro->setTitulo('Dom Casmurro');
$livro->setPreco(39.90);
$livro->setAutorId($autor->getId());
$livroRepo->save($livro);

// 3. Buscar registro pelo ID (SELECT)
$livroEncontrado = $livroRepo->find($livro->getId());
echo "Livro: " . $livroEncontrado->getTitulo() . PHP_EOL;

// 4. Atualizar um registro existente (UPDATE)
$livroEncontrado->setPreco(45.00);
$livroRepo->save($livroEncontrado);

// 5. Remover um registro (DELETE)
$livroRepo->delete($livroEncontrado->getId());


⚠️ Limitações Conhecidas

Por ser um projeto com foco didático em fundamentos de orientação a objetos e persistência nativa, o ORM possui as seguintes limitações:

Ausência de Unit of Work: Cada chamada a save() executa imediatamente uma instrução SQL no banco, sem suporte a transações em lote automáticas.

Sem Lazy Loading: Relacionamentos não utilizam objetos Proxy para carregamento sob demanda; associações precisam ser buscadas explicitamente pelos repositórios.

Relacionamentos N:M: Suporta nativamente apenas chaves estrangeiras diretas (1:N). Relações de muitos-para-muitos exigem entidades intermediárias manuais.

Sem Migrations Automáticas: A estrutura do banco deve ser criada manualmente por meio do arquivo sql/schema.sql.

🎓 Contexto Acadêmico

Instituição: Universidade do Oeste de Santa Catarina – UNOESC

Curso: Ciência da Computação

Disciplina: Programação II

Professor: Leandro Otavio Cordova Vieira

Aluno: Luiz Henrique dos Santos