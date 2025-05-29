<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Stdlib\Parameters;

class LivroModel
{
    public function __construct(public readonly Adapter $adapter)
    {
    }

    public function fetchLivros(): array
    {
        try {
            $result = $this->adapter
                ->createStatement('SELECT * FROM view_livros_autores_assuntos')
                ->execute();

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao buscar livros: ' . $e->getMessage(), 0, $e);
        }
    }

    public function criar(Parameters $dados): array
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $titulo = trim($dados['titulo'] ?? '');
            $editora = trim($dados['editora'] ?? '');
            $anoPublicado = trim($dados['ano_publicacao'] ?? '');
            $valor = trim($dados['valor'] ?? '');
            $autorId = $dados['autor_id'] ?? null;
            $assuntoId = $dados['assunto_id'] ?? null;

            if (!$titulo || !$editora || !$anoPublicado || !$valor || !$autorId || !$assuntoId) {
                throw new \InvalidArgumentException('Todos os campos obrigatórios devem ser preenchidos.');
            }

            $this->validarTextoSemEspeciais($titulo, 'título');
            $this->validarTextoSemEspeciais($editora, 'editora');

            $anoPublicado = filter_var($anoPublicado, FILTER_SANITIZE_NUMBER_INT);
            $valor = filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            $livroSql = '
            INSERT INTO livros (titulo, editora, ano_publicacao, valor)
            VALUES (:titulo, :editora, :ano_publicacao, :valor)
            RETURNING id
        ';

            $result = $this->adapter
                ->createStatement($livroSql, [
                    ':titulo' => $titulo,
                    ':editora' => $editora,
                    ':ano_publicacao' => $anoPublicado,
                    ':valor' => $valor
                ])
                ->execute();

            $livroId = $result->current()['id'];

            $this->adapter->createStatement(
                'INSERT INTO livro_autor (livro_id, autor_id) VALUES (:livro_id, :autor_id)',
                [':livro_id' => $livroId, ':autor_id' => $autorId]
            )->execute();

            $this->adapter->createStatement(
                'INSERT INTO livro_assunto (livro_id, assunto_id) VALUES (:livro_id, :assunto_id)',
                [':livro_id' => $livroId, ':assunto_id' => $assuntoId]
            )->execute();

            $connection->commit();

            return [
                'livro_id' => $livroId,
                'autor_id' => $autorId,
                'assunto_id' => $assuntoId
            ];

        } catch (\Throwable $e) {
            $connection->rollback();
            throw new \RuntimeException('Erro ao cadastrar livro: ' . $e->getMessage(), 0, $e);
        }
    }

    public function update(Parameters $data): void
    {
        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $livroId = $data['id'] ?? null;
            $params = $data['params'] ?? [];

            if (!$livroId || empty($params)) {
                throw new \InvalidArgumentException('ID e dados para atualização são obrigatórios.');
            }

            $result = $this->adapter->createStatement(
                'SELECT l.titulo, l.editora, l.valor, a.nome AS autor, s.nome AS assunto
             FROM livros l
             JOIN livro_autor la ON la.livro_id = l.id
             JOIN autores a ON a.id = la.autor_id
             JOIN livro_assunto ls ON ls.livro_id = l.id
             JOIN assuntos s ON s.id = ls.assunto_id
             WHERE l.id = :id
             LIMIT 1',
                [':id' => $livroId]
            )->execute()->current();

            if (!$result) {
                throw new \RuntimeException("Livro não encontrado.");
            }

            $fieldsToUpdate = [];
            $paramsUpdate = [':id' => $livroId];

            if (isset($params['titulo']) && $params['titulo'] !== $result['titulo']) {
                $this->validarTextoSemEspeciais($params['titulo'], 'título');
                $fieldsToUpdate[] = 'titulo = :titulo';
                $paramsUpdate[':titulo'] = trim($params['titulo']);
            }

            if (isset($params['editora']) && $params['editora'] !== $result['editora']) {
                $this->validarTextoSemEspeciais($params['editora'], 'editora');
                $fieldsToUpdate[] = 'editora = :editora';
                $paramsUpdate[':editora'] = trim($params['editora']);
            }

            if (isset($params['valor']) && $params['valor'] !== $result['valor']) {
                if (!is_numeric($params['valor'])) {
                    throw new \InvalidArgumentException("O campo 'valor' deve ser numérico.");
                }
                $fieldsToUpdate[] = 'valor = :valor';
                $paramsUpdate[':valor'] = $params['valor'];
            }

            if (!empty($fieldsToUpdate)) {
                $fieldsToUpdate[] = 'ts_atualizado = CURRENT_TIMESTAMP';
                $sqlUpdateLivro = 'UPDATE livros SET ' . implode(', ', $fieldsToUpdate) . ' WHERE id = :id';
                $this->adapter->createStatement($sqlUpdateLivro, $paramsUpdate)->execute();
            }

            if (isset($params['autor']) && $params['autor'] !== $result['autor']) {
                $this->validarTextoSemEspeciais($params['autor'], 'autor');

                $autorId = $this->adapter
                    ->createStatement('SELECT autor_id FROM livro_autor WHERE livro_id = :id LIMIT 1', [':id' => $livroId])
                    ->execute()
                    ->current()['autor_id'];

                $this->adapter->createStatement(
                    'UPDATE autores SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id',
                    [':nome' => trim($params['autor']), ':id' => $autorId]
                )->execute();
            }

            if (isset($params['assunto']) && $params['assunto'] !== $result['assunto']) {
                $this->validarTextoSemEspeciais($params['assunto'], 'assunto');

                $assuntoId = $this->adapter
                    ->createStatement('SELECT assunto_id FROM livro_assunto WHERE livro_id = :id LIMIT 1', [':id' => $livroId])
                    ->execute()
                    ->current()['assunto_id'];

                $this->adapter->createStatement(
                    'UPDATE assuntos SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id',
                    [':nome' => trim($params['assunto']), ':id' => $assuntoId]
                )->execute();
            }

            $connection->commit();

        } catch (\Throwable $e) {
            $connection->rollback();
            throw new \RuntimeException('Erro ao atualizar livro: ' . $e->getMessage(), 0, $e);
        }
    }


    public function delete(Parameters $id): void
    {
        try {
            $livroId = $id['id'] ?? null;

            if (!$livroId) {
                throw new \InvalidArgumentException('ID do livro é obrigatório para exclusão.');
            }

            $this->adapter->createStatement(
                'UPDATE livros SET ts_cancelado = TRUE WHERE id = :id',
                [':id' => $livroId]
            )->execute();

        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao excluir livro: ' . $e->getMessage(), 0, $e);
        }
    }

    public function buscarDadosRelatorio(): array
    {
        try {
            $sql = 'SELECT * FROM view_livros_autores_assuntos ORDER BY autor, titulo';
            $statement = $this->adapter->createStatement($sql);
            $result = $statement->execute();

            $dados = [];
            foreach ($result as $row) {
                $dados[] = $row;
            }

            return $dados;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao buscar dados do relatório: ' . $e->getMessage(), 0, $e);
        }
    }

    private function validarTextoSemEspeciais(string $valor, string $nomeCampo): void
    {
        $valor = trim($valor);

        $regex = '/^[\p{L}\p{N}\s\.,\-\'"]+$/u';

        if (!preg_match($regex, $valor)) {
            throw new \InvalidArgumentException("O campo '$nomeCampo' contém caracteres inválidos.");
        }
    }

}
