<?php

namespace ApplicationTest\ApplicationTest\Model;

use Application\Model\AutorModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\{ResultInterface, StatementInterface};
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

class AutorModelTest extends TestCase
{
    public function testFetchAllAutoresRetornaArray()
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->method('rewind')->willReturnCallback(function () {
        });
        $resultMock->method('valid')->willReturnOnConsecutiveCalls(true, false);
        $resultMock->method('current')->willReturn(['id' => 1, 'nome' => 'Autor A']);

        $statementMock = $this->createMock(StatementInterface::class);
        $statementMock->method('execute')->willReturn($resultMock);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('createStatement')->willReturn($statementMock);

        $model = new AutorModel($adapterMock);

        $resultado = $model->listar();

        $this->assertIsArray($resultado);
        $this->assertCount(1, $resultado);
        $this->assertEquals('Autor A', $resultado[0]['nome']);
    }

    public function testCreateAutorLancaRuntimeExceptionQuandoNomeNaoInformado()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AutorModel($adapterMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao criar autor: Nome do autor é obrigatório.');

        $params = new Parameters([]);
        $model->create($params);
    }

    public function testUpdateAutorLancaRuntimeExceptionQuandoIdOuNomeAusente()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AutorModel($adapterMock);

        $params = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao atualizar autor: ID e nome são obrigatórios.');

        $model->update($params);
    }

    public function testDeleteAutorLancaRuntimeExceptionQuandoIdAusente()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AutorModel($adapterMock);

        $params = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao excluir autor: ID é obrigatório para exclusão.');

        $model->delete($params);
    }
}
