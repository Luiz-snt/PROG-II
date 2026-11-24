-- BibliotecaORM — modelo relacional (MySQL 8 / MariaDB 10.4+)
CREATE DATABASE IF NOT EXISTS biblioteca_orm
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca_orm;

CREATE TABLE IF NOT EXISTS autores (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(120) NOT NULL,
  nacionalidade VARCHAR(60)  NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS livros (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo         VARCHAR(200) NOT NULL,
  isbn           VARCHAR(13)  NOT NULL,
  ano_publicacao SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_livros_isbn (isbn)
) ENGINE=InnoDB;

-- Relacionamento N:N. Apagar um livro remove seus vínculos (CASCADE);
-- apagar um autor que ainda tem livros é BLOQUEADO (RESTRICT).
CREATE TABLE IF NOT EXISTS livros_autores (
  livro_id INT UNSIGNED NOT NULL,
  autor_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (livro_id, autor_id),
  CONSTRAINT fk_la_livro FOREIGN KEY (livro_id) REFERENCES livros (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_la_autor FOREIGN KEY (autor_id) REFERENCES autores (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
