CREATE TABLE livros
(
    id             SERIAL PRIMARY KEY,
    titulo         VARCHAR(255)   NOT NULL,
    editora        VARCHAR(255),
    ano_publicacao INT,
    valor          NUMERIC(10, 2) NOT NULL,
    ts_inserido    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ts_atualizado  TIMESTAMP,
    ts_cancelado   BOOLEAN   DEFAULT FALSE
);

CREATE TABLE autores
(
    id            SERIAL PRIMARY KEY,
    nome          VARCHAR(40),
    ts_inserido   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ts_atualizado TIMESTAMP,
    ts_cancelado  BOOLEAN   DEFAULT FALSE
);

CREATE TABLE assuntos
(
    id            SERIAL PRIMARY KEY,
    nome          VARCHAR(255) NOT NULL,
    ts_inserido   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ts_atualizado TIMESTAMP,
    ts_cancelado  BOOLEAN   DEFAULT FALSE
);

CREATE TABLE livro_autor
(
    livro_id      INT NOT NULL,
    autor_id      INT NOT NULL,
    PRIMARY KEY (livro_id, autor_id),
    FOREIGN KEY (livro_id) REFERENCES livros (id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES autores (id) ON DELETE CASCADE,
    ts_inserido   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ts_atualizado TIMESTAMP,
    ts_cancelado  BOOLEAN   DEFAULT FALSE
);

CREATE TABLE livro_assunto
(
    livro_id      INT NOT NULL,
    assunto_id    INT NOT NULL,
    PRIMARY KEY (livro_id, assunto_id),
    FOREIGN KEY (livro_id) REFERENCES livros (id) ON DELETE CASCADE,
    FOREIGN KEY (assunto_id) REFERENCES assuntos (id) ON DELETE CASCADE,
    ts_inserido   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ts_atualizado TIMESTAMP,
    ts_cancelado  BOOLEAN   DEFAULT FALSE
);

CREATE VIEW view_livros_autores_assuntos AS
SELECT l.id   AS livro_id,
       l.titulo,
       l.valor,
       a.nome AS autor,
       s.nome AS assunto
FROM livros l
         JOIN livro_autor la ON la.livro_id = l.id
         JOIN autores a ON a.id = la.autor_id
         JOIN livro_assunto ls ON ls.livro_id = l.id
         JOIN assuntos s ON s.id = ls.assunto_id
ORDER BY a.nome, l.titulo;


INSERT INTO livros (titulo, valor)
VALUES ('Livro Exemplo', 79.90)
    INSERT
INTO autores (nome)
VALUES ('José da Silva');
INSERT INTO livros (titulo, editora, ano_publicacao, valor)
VALUES ('Livro Exemplo', 'Editora XPTO', 2024, 79.90);
INSERT INTO assuntos (nome)
VALUES ('Tecnologia');

INSERT INTO livro_autor (livro_id, autor_id)
VALUES (1, 1);
INSERT INTO livro_assunto (livro_id, assunto_id)
VALUES (1, 1);

-- 1. Cadastrar Autor
INSERT INTO autores (nome)
VALUES ('José da Silva');

-- 2. Cadastrar Assunto
INSERT INTO assuntos (nome)
VALUES ('Tecnologia');

-- 3. Cadastrar Livro
INSERT INTO livros (titulo, editora, ano_publicacao, valor)
VALUES ('Livro Exemplo', 'Editora XPTO', 2024, 79.90);

-- 4. Relacionar Livro com Autor (supondo que ambos tenham ID = 1)
INSERT INTO livro_autor (livro_id, autor_id)
VALUES (1, 1);

-- 5. Relacionar Livro com Assunto (supondo ID = 1)
INSERT INTO livro_assunto (livro_id, assunto_id)
VALUES (1, 1);


SELECT *
FROM view_livros_autores_assuntos
SELECT *
FROM livros


cache clear  rm -rf data/cache/*


CREATE OR REPLACE VIEW view_livros_autores_assuntos AS
SELECT
    l.id             AS livro_id,
    l.titulo,
    l.editora,
    l.ano_publicacao,
    l.valor,
    a.nome           AS autor,
    s.nome           AS assunto
FROM livros l
    JOIN livro_autor la ON la.livro_id = l.id
    JOIN autores a ON a.id = la.autor_id
    JOIN livro_assunto ls ON ls.livro_id = l.id
    JOIN assuntos s ON s.id = ls.assunto_id
WHERE l.ts_cancelado = FALSE
ORDER BY a.nome, l.titulo;