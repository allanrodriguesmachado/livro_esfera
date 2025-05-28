<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Stdlib\Parameters;
use PhpParser\Node\Param;

class AssuntoModel
{
    public function __construct(private readonly Adapter $adapter)
    {
    }

    public function fetchAll(): array
    {
        try {
            $result = ($this->adapter->createStatement('SELECT * FROM assuntos WHERE ts_cancelado = FALSE')->execute());

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erro ao buscar livros: ' . $e->getMessage());
        }
    }

    public function create(Parameters|string $params): int
    {
        $nome = is_string($params) ? $params : $params['nome'] ?? null;

        if (! $nome) {
            throw new \InvalidArgumentException('Nome do assunto é obrigatório.');
        }

        $sql = 'INSERT INTO assuntos (nome) VALUES (:nome) RETURNING id';
        $stmt = $this->adapter->createStatement($sql, [':nome' => trim($nome)]);
        $result = $stmt->execute();

        $row = $result->current();
        if (! $row || ! isset($row['id'])) {
            throw new \RuntimeException('Falha ao obter o ID do assunto após inserção.');
        }

        return (int)$row['id'];
    }


    public function update(Parameters $params): void
    {
        $id = $params->get('id');
        $nome = $params->get('nome');

        if (! $id || ! $nome) {
            throw new \InvalidArgumentException('ID e nome são obrigatórios.');
        }

        $sql = 'UPDATE assuntos SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
        $this->adapter->createStatement($sql, [
            ':nome' => trim($nome),
            ':id' => $id
        ])->execute();
    }


    public function delete(Parameters $params): void
    {
        $sql = 'UPDATE assuntos SET ts_cancelado = TRUE WHERE id = :id';
        $this->adapter->createStatement($sql, [':id' => $params['id']])->execute();
    }
}
