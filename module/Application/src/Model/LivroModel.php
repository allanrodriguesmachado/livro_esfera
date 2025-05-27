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

//    public function fetchLivros(): array
//    {
//        $sql = 'SELECT titulo, valor, autor, assunto FROM view_livros_autores_assuntos';
//
//        try {
//            $statement = $this->adapter->createStatement($sql);
//            $result = $statement->execute();
//
//            $livros = [];
//            foreach ($result as $row) {
//                $livros[] = $row;
//            }
//
//            return $livros;
//        } catch (\Exception $e) {
//            throw new \RuntimeException('Erro ao buscar livros: ' . $e->getMessage());
//        }
//    }

    public function fetchLivros(): array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['v' => 'view_livros_autores_assuntos']);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);

        return $resultSet->toArray();
    }
}
