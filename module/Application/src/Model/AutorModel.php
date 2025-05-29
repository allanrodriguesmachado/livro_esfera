<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Stdlib\Parameters;

class AutorModel
{
    public function __construct(private readonly Adapter $adapter)
    {
    }

    public function fetchAll(): array
    {
        try {
            $result = $this->adapter
                ->createStatement('SELECT * FROM autores WHERE ts_cancelado = FALSE')
                ->execute();

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao buscar autores: ' . $e->getMessage(), 0, $e);
        }
    }

    public function create(Parameters|string $params): int
    {
        try {
            $nome = is_string($params) ? $params : $params['nome'] ?? null;

            if (!$nome) {
                throw new \InvalidArgumentException('Nome do autor é obrigatório.');
            }

            $this->validarTexto($nome, 'Nome do autor');

            $sql = 'INSERT INTO autores (nome) VALUES (:nome) RETURNING id';
            $stmt = $this->adapter->createStatement($sql, [':nome' => $nome]);
            $result = $stmt->execute();

            $row = $result->current();

            if (! $row || ! isset($row['id'])) {
                throw new \RuntimeException('Falha ao obter o ID do autor após inserção.');
            }

            return (int) $row['id'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao criar autor: ' . $e->getMessage(), 0, $e);
        }
    }

    public function update(Parameters $params): void
    {
        try {
            $id = $params->get('id');
            $nome = $params->get('nome');

            if (! $id || ! $nome) {
                throw new \InvalidArgumentException('ID e nome são obrigatórios.');
            }

            $nome = trim($nome);

            if (
                ! filter_var($nome, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => '/^[\p{L}\p{N}\s\'\-]+$/u']
                ])
            ) {
                throw new \InvalidArgumentException('Nome do autor contém caracteres inválidos.');
            }

            $sql = 'UPDATE autores SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
            $this->adapter->createStatement($sql, [
                ':nome' => $nome,
                ':id' => $id
            ])->execute();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao atualizar autor: ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(Parameters $params): void
    {
        try {
            $id = $params->get('id');

            if (! $id) {
                throw new \InvalidArgumentException('ID é obrigatório para exclusão.');
            }

            $sql = 'UPDATE autores SET ts_cancelado = TRUE WHERE id = :id';
            $this->adapter->createStatement($sql, [':id' => $id])->execute();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao excluir autor: ' . $e->getMessage(), 0, $e);
        }
    }

    private function validarTexto(string $texto, string $campo): void
    {
        $texto = trim($texto);
        if ($texto === '') {
            throw new \InvalidArgumentException("O campo {$campo} é obrigatório.");
        }
        if (!filter_var($texto, FILTER_VALIDATE_REGEXP, [
            'options' => ['regexp' => '/^[\p{L}\p{N}\s\'\-]+$/u']
        ])) {
            throw new \InvalidArgumentException("O campo {$campo} contém caracteres inválidos.");
        }
    }
}
