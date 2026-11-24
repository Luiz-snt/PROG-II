USE biblioteca_orm;

INSERT INTO autores (nome, nacionalidade) VALUES
  ('Machado de Assis', 'Brasileira'),
  ('Clarice Lispector', 'Brasileira'),
  ('Gabriel García Márquez', 'Colombiana'),
  ('Andrew Hunt', 'Americana'),
  ('David Thomas', 'Britânica');

INSERT INTO livros (titulo, isbn, ano_publicacao) VALUES
  ('Dom Casmurro', '9788535911664', 1899),
  ('A Hora da Estrela', '9788532508119', 1977),
  ('Cem Anos de Solidão', '9788501012074', 1967),
  ('O Programador Pragmático', '9788577807000', 1999);

INSERT INTO livros_autores (livro_id, autor_id) VALUES (1,1), (2,2), (3,3), (4,4), (4,5);
