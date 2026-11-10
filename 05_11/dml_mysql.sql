-- ============================================================================
-- TRABALHO PRÁTICO: LINGUAGEM SQL E COMANDOS DML (PROJETO BIBLIOTECA)
-- Ambiente: MySQL 8.x com InnoDB
-- ============================================================================

USE biblioteca;

-- ----------------------------------------------------------------------------
-- 1. INSERÇÃO INDIVIDUAL
-- Objetivo: Cadastrar um novo autor e demonstrar o identificador atribuído.
-- ----------------------------------------------------------------------------
INSERT INTO autor (nome, nacionalidade) 
VALUES ('Machado de Assis', 'Brasileira'),
('Clarice Lispector', 'Brasileira'),
('George Orwell', 'Britânica'),
('Gabriel García Márquez', 'Colombiana'),
('José Saramago', 'Portuguesa'),
('Agatha Christie', 'Britânica');


-- Verificação do identificador gerado automaticamente por AUTO_INCREMENT:
SELECT LAST_INSERT_ID() AS autor_id_gerado;
SELECT id, nome, nacionalidade FROM autor WHERE id = LAST_INSERT_ID();


-- ============================================================================
-- TRABALHO PRÁTICO: LINGUAGEM SQL E COMANDOS DML (PROJETO BIBLIOTECA)
-- Ambiente: MySQL 8.x com InnoDB
-- ============================================================================

USE biblioteca;

-- Configurações de ambiente para execução sem bloqueios
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_SAFE_UPDATES = 0;

-- Limpeza preventiva de tabelas e reset de auto_increment
DROP TABLE IF EXISTS livro_relatorio;
TRUNCATE TABLE livro;
TRUNCATE TABLE autor;

ALTER TABLE autor AUTO_INCREMENT = 1;
ALTER TABLE livro AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. INSERÇÃO DE AUTORES (Individual + Carga Completa)
-- ----------------------------------------------------------------------------
-- Autor 1 (Original)
INSERT INTO autor (nome, nacionalidade) VALUES ('Machado de Assis', 'Brasileira');

-- Demais Autores (Expandido)
INSERT INTO autor (nome, nacionalidade) VALUES 
('Clarice Lispector', 'Brasileira'),        -- ID 2
('George Orwell', 'Britânica'),             -- ID 3
('Gabriel García Márquez', 'Colombiana'),    -- ID 4
('José Saramago', 'Portuguesa'),            -- ID 5
('Agatha Christie', 'Britânica');           -- ID 6

-- Verificação do primeiro autor inserido
SELECT id, nome, nacionalidade FROM autor WHERE id = 1;


-- ----------------------------------------------------------------------------
-- 2. INSERÇÃO MÚLTIPLA DE LIVROS (Original + Carga Completa)
-- ----------------------------------------------------------------------------
-- Obras de Machado de Assis (ID 1)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('Dom Casmurro', '9788535914841', 1899, 39.90, 5, 1),
('Memórias Póstumas de Brás Cubas', '9788535914842', 1881, 45.00, 8, 1),
('Quincas Borba', '9788535914843', 1891, 42.50, 4, 1),
('O Alienista', '9788535914844', 1882, 29.90, 12, 1),
('Esaú e Jacó', '9788535914845', 1904, 38.00, 6, 1);

-- Obras de Clarice Lispector (ID 2)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('A Hora da Estrela', '9788532512349', 1977, 39.90, 10, 2),
('Laços de Família', '9788532512350', 1960, 44.50, 7, 2),
('Perto do Coração Selvagem', '9788532512351', 1943, 42.00, 4, 2);

-- Obras de George Orwell (ID 3)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('1984', '9788535902770', 1949, 54.90, 15, 3),
('A Revolução dos Bichos', '9788535909557', 1945, 34.90, 20, 3);

-- Obras de Gabriel García Márquez (ID 4)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('Cem Anos de Solidão', '9788501012074', 1967, 69.90, 9, 4),
('O Amor nos Tempos do Cólera', '9788501012081', 1985, 59.90, 8, 4);

-- Obras de José Saramago (ID 5)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('Ensaio sobre a Cegueira', '9788535902848', 1995, 58.00, 11, 5),
('O Homem Duplicado', '9788535902855', 2002, 49.90, 5, 5);

-- Obras de Agatha Christie (ID 6)
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) VALUES
('E Não Sobrou Nenhum', '9788520938454', 1939, 39.90, 14, 6),
('Assassinato no Expresso do Oriente', '9788520938461', 1934, 42.90, 10, 6);


-- ----------------------------------------------------------------------------
-- 3. ATUALIZAÇÕES PONTUAIS E CONDICIONAIS
-- ----------------------------------------------------------------------------
-- Reajuste pontual no livro ID 1
UPDATE livro 
SET preco = 49.90 
WHERE id = 1;

-- Reajuste condicional de 10% para livros com preço inferior a 45.00
UPDATE livro 
SET preco = ROUND(preco * 1.10, 2) 
WHERE preco < 45.00 AND quantidade > 0;


-- ----------------------------------------------------------------------------
-- 4. INSERÇÃO DERIVADA (TABELA DE RELATÓRIO)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS livro_relatorio (
    id_livro INT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    valor_estoque DECIMAL(14,2) NOT NULL
) ENGINE=InnoDB;

INSERT INTO livro_relatorio (id_livro, titulo, valor_estoque)
SELECT id, titulo, (preco * quantidade)
FROM livro 
WHERE quantidade > 0;

SELECT * FROM livro_relatorio;


-- ----------------------------------------------------------------------------
-- 5. EXCLUSÃO CONTROLADA (TESTE)
-- ----------------------------------------------------------------------------
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) 
VALUES ('Livro Temporário', '9789999999999', 2024, 10.00, 1, 1);

DELETE FROM livro WHERE isbn = '9789999999999';


-- ----------------------------------------------------------------------------
-- 6. CONSULTAS RELACIONAIS E RELATÓRIOS
-- ----------------------------------------------------------------------------
-- Listagem com LEFT JOIN
SELECT 
    l.id, 
    l.titulo, 
    a.nome AS autor, 
    l.preco, 
    l.quantidade
FROM livro AS l
LEFT JOIN autor AS a ON a.id = l.id_autor
ORDER BY l.titulo ASC;

-- Relatório consolidado por autor
SELECT 
    a.id,
    a.nome AS autor,
    COUNT(l.id) AS titulos,
    COALESCE(SUM(l.quantidade), 0) AS exemplares,
    COALESCE(SUM(l.preco * l.quantidade), 0) AS valor_estoque
FROM autor AS a
LEFT JOIN livro AS l ON l.id_autor = a.id
GROUP BY a.id, a.nome
ORDER BY valor_estoque DESC;


-- ----------------------------------------------------------------------------
-- 7. TRANSAÇÃO DE ESTOQUE
-- ----------------------------------------------------------------------------
START TRANSACTION;

UPDATE livro 
SET quantidade = quantidade - 1 
WHERE id = 1 AND quantidade > 0;

COMMIT;


-- ----------------------------------------------------------------------------
-- 3. INSERÇÃO DERIVADA
-- Objetivo: Criar tabela auxiliar de relatórios e popular com INSERT INTO ... SELECT
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS livro_relatorio (
    id_livro INT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    valor_estoque DECIMAL(14,2) NOT NULL
) ENGINE=InnoDB;

-- Populando a tabela auxiliar com dados calculados da base principal:
INSERT INTO livro_relatorio (id_livro, titulo, valor_estoque)
SELECT id, titulo, (preco * quantidade)
FROM livro 
WHERE quantidade > 0;

-- Conferência da tabela derivada:
SELECT * FROM livro_relatorio;


-- ----------------------------------------------------------------------------
-- 4. ATUALIZAÇÃO PONTUAL
-- Objetivo: Reajustar o preço de um livro identificado pela chave primária
-- ----------------------------------------------------------------------------
-- Pré-condição: Consultar estado atual
SELECT id, titulo, preco FROM livro WHERE id = 1;

-- Execução da atualização pontual com WHERE restrito à chave primária:
UPDATE livro 
SET preco = 49.90 
WHERE id = 1;

-- Pós-confirmação: Conferir se o valor foi alterado corretamente
SELECT id, titulo, preco FROM livro WHERE id = 1;


-- ----------------------------------------------------------------------------
-- 5. ATUALIZAÇÃO CONDICIONAL
-- Objetivo: Aplicar reajuste a um subconjunto selecionado e validar efeitos
-- ----------------------------------------------------------------------------
-- Aplicar reajuste de 10% em livros com preço inferior a 45.00 e quantidade disponível:
UPDATE livro 
SET preco = ROUND(preco * 1.10, 2) 
WHERE preco < 45.00 AND quantidade > 0;

-- Verificação imediata do número de linhas afetadas pela instrução anterior:
SELECT ROW_COUNT() AS linhas_afetadas;


-- ----------------------------------------------------------------------------
-- 6. EXCLUSÃO CONTROLADA
-- Objetivo: Excluir registro de teste após verificar dependências
-- ----------------------------------------------------------------------------
-- Inserir temporariamente um livro de teste para exclusão:
INSERT INTO livro (titulo, isbn, ano_publicacao, preco, quantidade, id_autor) 
VALUES ('Livro Temporário', '9789999999999', 2024, 10.00, 1, 1);

-- Identificar a chave primária gerada para exclusão segura:
SELECT id, titulo FROM livro WHERE isbn = '9789999999999';

-- Exclusão condicionada por chave primária (após conferir dependências):
DELETE FROM livro WHERE isbn = '9789999999999';


-- ----------------------------------------------------------------------------
-- 7. CONSULTA RELACIONAL
-- Objetivo: Listar livros e autores com LEFT JOIN e ordenação explícita
-- ----------------------------------------------------------------------------
SELECT 
    l.id, 
    l.titulo, 
    a.nome AS autor, 
    l.preco, 
    l.quantidade
FROM livro AS l
LEFT JOIN autor AS a ON a.id = l.id_autor
ORDER BY l.titulo ASC;


-- ----------------------------------------------------------------------------
-- 8. RELATÓRIO
-- Objetivo: Calcular títulos, exemplares e valor de estoque por autor
-- ----------------------------------------------------------------------------
SELECT 
    a.id,
    a.nome AS autor,
    COUNT(l.id) AS titulos,
    COALESCE(SUM(l.quantidade), 0) AS exemplares,
    COALESCE(SUM(l.preco * l.quantidade), 0) AS valor_estoque
FROM autor AS a
LEFT JOIN livro AS l ON l.id_autor = a.id
GROUP BY a.id, a.nome
ORDER BY valor_estoque DESC;


-- ----------------------------------------------------------------------------
-- 9. TRANSAÇÃO
-- Objetivo: Demonstrar COMMIT e ROLLBACK com alteração controlada de estoque
-- ----------------------------------------------------------------------------
START TRANSACTION;

-- Tentativa de baixa de estoque controlada:
UPDATE livro 
SET quantidade = quantidade - 1 
WHERE id = 1 AND quantidade > 0;

-- Verificação das linhas afetadas:
SELECT ROW_COUNT() AS linhas_afetadas;

-- Se linhas_afetadas == 1, confirmamos a operação com COMMIT. 
-- Caso contrário, revertemos com ROLLBACK.
COMMIT; 
-- (ou ROLLBACK; em caso de falha lógica)