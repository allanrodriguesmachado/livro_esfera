<?php

namespace Application\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Stdlib\Parameters;

class AssuntoModel
{
    public function __construct(private readonly Adapter $adapter)
    {
    }

    public function listar(): array
    {
        try {
            $result = $this->adapter
                ->createStatement('SELECT * FROM assuntos WHERE ts_cancelado = FALSE')
                ->execute();

            $resultSet = new ResultSet();
            $resultSet->initialize($result);

            return $resultSet->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao buscar assuntos: ' . $e->getMessage(), 0, $e);
        }
    }

    public function create(Parameters|string $params): int
    {
        try {
            $nome = is_string($params) ? $params : $params['nome'] ?? null;

            if (!$nome) {
                throw new \InvalidArgumentException('Nome do assunto é obrigatório.');
            }

            $this->validarTexto($nome, 'Nome do assunto');

            $sql = 'INSERT INTO assuntos (nome) VALUES (:nome) RETURNING id';
            $stmt = $this->adapter->createStatement($sql, [':nome' => trim($nome)]);
            $result = $stmt->execute();

            $row = $result->current();

            if (!$row || !isset($row['id'])) {
                throw new \RuntimeException('Falha ao obter o ID do assunto após inserção.');
            }

            return (int) $row['id'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao criar assunto: ' . $e->getMessage(), 0, $e);
        }
    }

    public function update(Parameters $params): void
    {
        try {
            $id = $params->get('id');
            $nome = $params->get('nome');

            if (!$id || !$nome) {
                throw new \InvalidArgumentException('ID e nome são obrigatórios.');
            }

            $this->validarTexto($nome, 'Nome do assunto');

            $sql = 'UPDATE assuntos SET nome = :nome, ts_atualizado = CURRENT_TIMESTAMP WHERE id = :id';
            $this->adapter->createStatement($sql, [
                ':nome' => trim($nome),
                ':id' => $id
            ])->execute();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao atualizar assunto: ' . $e->getMessage(), 0, $e);
        }
    }

    public function delete(Parameters $params): void
    {
        try {
            $id = $params->get('id');

            if (!$id) {
                throw new \InvalidArgumentException('ID é obrigatório para exclusão.');
            }

            $sql = 'UPDATE assuntos SET ts_cancelado = TRUE WHERE id = :id';
            $this->adapter->createStatement($sql, [':id' => $id])->execute();

        } catch (\Throwable $e) {
            throw new \RuntimeException('Erro ao excluir assunto: ' . $e->getMessage(), 0, $e);
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
