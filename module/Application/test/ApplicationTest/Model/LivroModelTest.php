<?php

namespace ApplicationTest\Model;

use Application\Model\LivroModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\{ConnectionInterface, DriverInterface, ResultInterface};
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

class LivroModelTest extends TestCase
{
    public function testFetchLivros()
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->method('rewind')->willReturnCallback(function () {}); // <- aqui está o fix
        $resultMock->method('valid')->willReturnOnConsecutiveCalls(true, false);
        $resultMock->method('current')->willReturn(['id' => 1, 'titulo' => 'Livro A']);

        $statementMock = $this->createMock(\Laminas\Db\Adapter\Driver\StatementInterface::class);
        $statementMock->method('execute')->willReturn($resultMock);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('createStatement')->willReturn($statementMock);

        $model = new LivroModel($adapterMock);

        $resultado = $model->listar();

        $this->assertIsArray($resultado);
        $this->assertCount(1, $resultado);
        $this->assertEquals('Livro A', $resultado[0]['titulo']);
    }


    public function testCriarLancaRuntimeExceptionSeCamposObrigatoriosEstiveremAusentes()
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('beginTransaction')->willReturn(null);
        $connectionMock->method('rollback')->willReturn(null);

        $driverMock = $this->createMock(DriverInterface::class);
        $driverMock->method('getConnection')->willReturn($connectionMock);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('getDriver')->willReturn($driverMock);

        $model = new LivroModel($adapterMock);

        $dadosIncompletos = new Parameters([
            'titulo' => '',
            'editora' => '',
            'ano_publicacao' => '',
            'valor' => '',
            'autor_id' => null,
            'assunto_id' => null
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao cadastrar livro: Todos os campos obrigatórios devem ser preenchidos.');

        $model->criar($dadosIncompletos);
    }


    public function testUpdateLancaRuntimeExceptionQuandoDadosSaoInvalidos()
    {
        $connectionMock = $this->createMock(ConnectionInterface::class);
        $connectionMock->method('beginTransaction')->willReturn(null);
        $connectionMock->method('rollback')->willReturn(null);

        $driverMock = $this->createMock(DriverInterface::class);
        $driverMock->method('getConnection')->willReturn($connectionMock);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('getDriver')->willReturn($driverMock);

        $model = new LivroModel($adapterMock);

        $dadosInvalidos = new Parameters([
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao atualizar livro: ID e dados para atualização são obrigatórios.');

        $model->update($dadosInvalidos);
    }


    public function testDeleteLancaRuntimeExceptionQuandoIdNaoInformado()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new LivroModel($adapterMock);

        $idVazio = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao excluir livro: ID do livro é obrigatório para exclusão.');

        $model->delete($idVazio);
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

    public function testBuscarDadosRelatorioRetornaArrayDeDados()
    {
        $fakeResult = new \ArrayIterator([
            ['id' => 1, 'titulo' => 'Livro A', 'autor' => 'Autor A'],
            ['id' => 2, 'titulo' => 'Livro B', 'autor' => 'Autor B']
        ]);

        $statementMock = $this->createMock(\Laminas\Db\Adapter\Driver\StatementInterface::class);
        $statementMock->method('execute')->willReturn($fakeResult);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('createStatement')->willReturn($statementMock);

        $model = new LivroModel($adapterMock);
        $dados = $model->buscarDadosRelatorio();

        $this->assertIsArray($dados);
        $this->assertCount(2, $dados);
        $this->assertEquals('Livro A', $dados[0]['titulo']);
    }
}