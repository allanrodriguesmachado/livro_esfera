<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Sql;
use Laminas\Stdlib\Parameters;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

class LivroModel
{
    public function __construct(public readonly Adapter $adapter)
    {
    }

    public function fetchLivros(): array
    {
        try {
            $result = ($this->adapter->createStatement('SELECT * FROM view_livros_autores_assuntos')->execute());

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao buscar livros: ' . $e->getMessage());
        }
    }

    public function criar(Parameters $dados): array
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
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

            $livroAutorSql = '
            INSERT INTO livro_autor (livro_id, autor_id)
            VALUES (:livro_id, :autor_id)
        ';
            $this->adapter->createStatement($livroAutorSql, [
                ':livro_id' => $livroId,
                ':autor_id' => $dados['autor_id']
            ])->execute();

            $livroAssuntoSql = '
            INSERT INTO livro_assunto (livro_id, assunto_id)
            VALUES (:livro_id, :assunto_id)
        ';
            $this->adapter->createStatement($livroAssuntoSql, [
                ':livro_id' => $livroId,
                ':assunto_id' => $dados['assunto_id']
            ])->execute();

            $connection->commit();

            return [
                'livro_id' => $livroId,
                'autor_id' => $dados['autor_id'],
                'assunto_id' => $dados['assunto_id']
            ];
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \RuntimeException('Erro ao cadastrar livro: ' . $e->getMessage());
        }
    }


//    public function update(Parameters $data): void
//    {
//        $livroId = $data['id'] ?? null;
//        $params = $data['params'] ?? [];
//
//        if (!$livroId) {
//            throw new \InvalidArgumentException('ID do livro é obrigatório.');
//        }
//
//        if (empty($params)) {
//            return;
//        }
//
//        $sql = '
//        SELECT l.titulo, l.valor, a.nome AS autor, s.nome AS assunto
//        FROM livros l
//        JOIN livro_autor la ON la.livro_id = l.id
//        JOIN autores a ON a.id = la.autor_id
//        JOIN livro_assunto ls ON ls.livro_id = l.id
//        JOIN assuntos s ON s.id = ls.assunto_id
//        WHERE l.id = :id
//        LIMIT 1
//    ';
//        $statement = $this->adapter->createStatement($sql, [':id' => $livroId]);
//        $result = $statement->execute()->current();
//
//        if (!$result) {
//            throw new \RuntimeException("Livro não encontrado.");
//        }
//
//        $connection = $this->adapter->getDriver()->getConnection();
//        $connection->beginTransaction();
//
//        try {
//            $fieldsToUpdate = [];
//            $paramsUpdate = [':id' => $livroId];
//
//            if (isset($params['titulo']) && $params['titulo'] !== $result['titulo']) {
//                $fieldsToUpdate[] = 'titulo = :titulo';
//                $paramsUpdate[':titulo'] = $params['titulo'];
//            }
//
//            if (isset($params['valor']) && $params['valor'] !== $result['valor']) {
//                $fieldsToUpdate[] = 'valor = :valor';
//                $paramsUpdate[':valor'] = $params['valor'];
//            }
//
//            if (!empty($fieldsToUpdate)) {
//                $fieldsToUpdate[] = 'ts_atualizado = CURRENT_TIMESTAMP';
//                $sqlUpdateLivro = 'UPDATE livros SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = :id';
//                $stmtLivro = $this->adapter->createStatement($sqlUpdateLivro, $paramsUpdate);
//                $stmtLivro->execute();
//            }
//
//            if (isset($params['autor']) && $params['autor'] !== $result['autor']) {
//                $sqlAutorId = 'SELECT autor_id FROM livro_autor WHERE livro_id = :id LIMIT 1';
//                $stmtAutorId = $this->adapter->createStatement($sqlAutorId, [':id' => $livroId]);
//                $autorId = $stmtAutorId->execute()->current()['autor_id'];
//
//                $sqlUpdateAutor = 'UPDATE autores SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
//                $stmtAutor = $this->adapter->createStatement($sqlUpdateAutor, [
//                    ':nome' => $params['autor'],
//                    ':id' => $autorId,
//                ]);
//                $stmtAutor->execute();
//            }
//
//            if (isset($params['assunto']) && $params['assunto'] !== $result['assunto']) {
//                $sqlAssuntoId = 'SELECT assunto_id FROM livro_assunto WHERE livro_id = :id LIMIT 1';
//                $stmtAssuntoId = $this->adapter->createStatement($sqlAssuntoId, [':id' => $livroId]);
//                $assuntoId = $stmtAssuntoId->execute()->current()['assunto_id'];
//
//                $sqlUpdateAssunto = 'UPDATE assuntos SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
//                $stmtAssunto = $this->adapter->createStatement($sqlUpdateAssunto, [
//                    ':nome' => $params['assunto'],
//                    ':id' => $assuntoId,
//                ]);
//                $stmtAssunto->execute();
//            }
//
//            $connection->commit();
//        } catch (\Exception $e) {
//            $connection->rollback();
//            throw new \RuntimeException('Erro ao atualizar livro: ' . $e->getMessage());
//        }
//    }

public function update(Parameters $data): void
{
    $livroId = $data['id'] ?? null;
    $params = $data['params'] ?? [];

    if (!$livroId) {
        throw new \InvalidArgumentException('ID do livro é obrigatório.');
    }

    if (empty($params)) {
        return;
    }

    $sql = '
        SELECT l.titulo, l.editora, l.valor, a.nome AS autor, s.nome AS assunto
        FROM livros l
        JOIN livro_autor la ON la.livro_id = l.id
        JOIN autores a ON a.id = la.autor_id
        JOIN livro_assunto ls ON ls.livro_id = l.id
        JOIN assuntos s ON s.id = ls.assunto_id
        WHERE l.id = :id
        LIMIT 1
    ';
    $statement = $this->adapter->createStatement($sql, [':id' => $livroId]);
    $result = $statement->execute()->current();

    if (!$result) {
        throw new \RuntimeException("Livro não encontrado.");
    }

    $connection = $this->adapter->getDriver()->getConnection();
    $connection->beginTransaction();

    try {
        $fieldsToUpdate = [];
        $paramsUpdate = [':id' => $livroId];

        // Atualiza título
        if (isset($params['titulo']) && $params['titulo'] !== $result['titulo']) {
            $fieldsToUpdate[] = 'titulo = :titulo';
            $paramsUpdate[':titulo'] = $params['titulo'];
        }

        // Atualiza editora
        if (isset($params['editora']) && $params['editora'] !== $result['editora']) {
            $fieldsToUpdate[] = 'editora = :editora';
            $paramsUpdate[':editora'] = $params['editora'];
        }

        // Atualiza valor
        if (isset($params['valor']) && $params['valor'] !== $result['valor']) {
            $fieldsToUpdate[] = 'valor = :valor';
            $paramsUpdate[':valor'] = $params['valor'];
        }

        // Se houver campos do livro a atualizar
        if (!empty($fieldsToUpdate)) {
            $fieldsToUpdate[] = 'ts_atualizado = CURRENT_TIMESTAMP';
            $sqlUpdateLivro = 'UPDATE livros SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = :id';
            $stmtLivro = $this->adapter->createStatement($sqlUpdateLivro, $paramsUpdate);
            $stmtLivro->execute();
        }

        // Atualiza autor se necessário
        if (isset($params['autor']) && $params['autor'] !== $result['autor']) {
            $sqlAutorId = 'SELECT autor_id FROM livro_autor WHERE livro_id = :id LIMIT 1';
            $stmtAutorId = $this->adapter->createStatement($sqlAutorId, [':id' => $livroId]);
            $autorId = $stmtAutorId->execute()->current()['autor_id'];

            $sqlUpdateAutor = 'UPDATE autores SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
            $stmtAutor = $this->adapter->createStatement($sqlUpdateAutor, [
                ':nome' => $params['autor'],
                ':id' => $autorId,
            ]);
            $stmtAutor->execute();
        }

        // Atualiza assunto se necessário
        if (isset($params['assunto']) && $params['assunto'] !== $result['assunto']) {
            $sqlAssuntoId = 'SELECT assunto_id FROM livro_assunto WHERE livro_id = :id LIMIT 1';
            $stmtAssuntoId = $this->adapter->createStatement($sqlAssuntoId, [':id' => $livroId]);
            $assuntoId = $stmtAssuntoId->execute()->current()['assunto_id'];

            $sqlUpdateAssunto = 'UPDATE assuntos SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
            $stmtAssunto = $this->adapter->createStatement($sqlUpdateAssunto, [
                ':nome' => $params['assunto'],
                ':id' => $assuntoId,
            ]);
            $stmtAssunto->execute();
        }

        $connection->commit();
    } catch (\Exception $e) {
        $connection->rollback();
        throw new \RuntimeException('Erro ao atualizar livro: ' . $e->getMessage());
    }
}

    public function delete(Parameters $id): void
    {
        $this->adapter->createStatement(
            'UPDATE livros SET ts_cancelado = TRUE WHERE id = :id',
            initialParameters: [
                ':id' => $id['id']
            ]
        )->execute();
    }

   public function buscarDadosRelatorio(): array
   {
    $sql = 'SELECT * FROM view_livros_autores_assuntos ORDER BY autor, titulo';

    $statement = $this->adapter->createStatement($sql);
    $result = $statement->execute();

    $dados = [];

    foreach ($result as $row) {
        $dados[] = $row;
    }

    return $dados;
}

}
