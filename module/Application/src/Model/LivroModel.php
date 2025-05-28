<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Sql;

class LivroModel
{
    public function __construct(public readonly Adapter $adapter)
    {
    }

    public function fetchLivros(): array
    {
        try {
            $sql = 'SELECT * FROM view_livros_autores_assuntos';
            $statement = $this->adapter->createStatement($sql);
            $result = $statement->execute();

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();

        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao buscar livros: ' . $e->getMessage());
        }
    }


    public function criar(array $dados)
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            // 1. Inserir Livro
            $livroSql = '
            INSERT INTO livros (titulo, editora, ano_publicacao, valor)
            VALUES (:titulo, :editora, :ano_publicacao, :valor)
            RETURNING id
        ';
            $statement = $this->adapter->createStatement($livroSql, [
                ':titulo' => trim($dados['titulo']),
                ':editora' => trim($dados['editora']),
                ':ano_publicacao' => $dados['ano_publicacao'],
                ':valor' => $dados['valor']
            ]);
            $result = $statement->execute();
            $livroId = $result->current()['id'];

            // 2. Inserir Autor
            $autorSql = '
            INSERT INTO autores (nome)
            VALUES (:nome)
            RETURNING id
        ';
            $statement = $this->adapter->createStatement($autorSql, [
                ':nome' => trim($dados['autor'])
            ]);
            $result = $statement->execute();
            $autorId = $result->current()['id'];

            // 3. Inserir Assunto
            $assuntoSql = '
            INSERT INTO assuntos (nome)
            VALUES (:nome)
            RETURNING id
        ';
            $statement = $this->adapter->createStatement($assuntoSql, [
                ':nome' => trim($dados['assunto'])
            ]);
            $result = $statement->execute();
            $assuntoId = $result->current()['id'];

            // 4. Relacionar Livro com Autor
            $livroAutorSql = '
            INSERT INTO livro_autor (livro_id, autor_id)
            VALUES (:livro_id, :autor_id)
        ';
            $statement = $this->adapter->createStatement($livroAutorSql, [
                ':livro_id' => $livroId,
                ':autor_id' => $autorId
            ]);
            $statement->execute();

            // 5. Relacionar Livro com Assunto
            $livroAssuntoSql = '
            INSERT INTO livro_assunto (livro_id, assunto_id)
            VALUES (:livro_id, :assunto_id)
        ';
            $statement = $this->adapter->createStatement($livroAssuntoSql, [
                ':livro_id' => $livroId,
                ':assunto_id' => $assuntoId
            ]);
            $statement->execute();

            $connection->commit();

            return [
                'livro_id' => $livroId,
                'autor_id' => $autorId,
                'assunto_id' => $assuntoId
            ];

        } catch (\Exception $e) {
            $connection->rollback();
            throw new \RuntimeException('Erro ao cadastrar livro: ' . $e->getMessage());
        }
    }


}
