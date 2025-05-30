<?php

namespace ApplicationTest\ApplicationTest\Model;

use Application\Model\AssuntoModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\{ResultInterface, StatementInterface};
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

class AssuntoModelTest extends TestCase
{
    public function testListarAssuntosRetornaArray()
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->method('rewind')->willReturnCallback(function () {
        });
        $resultMock->method('valid')->willReturnOnConsecutiveCalls(true, false);
        $resultMock->method('current')->willReturn(['id' => 1, 'nome' => 'Assunto A']);

        $statementMock = $this->createMock(StatementInterface::class);
        $statementMock->method('execute')->willReturn($resultMock);

        $adapterMock = $this->createMock(Adapter::class);
        $adapterMock->method('createStatement')->willReturn($statementMock);

        $model = new AssuntoModel($adapterMock);

        $resultado = $model->listar();

        $this->assertIsArray($resultado);
        $this->assertCount(1, $resultado);
        $this->assertEquals('Assunto A', $resultado[0]['nome']);
    }

    public function testCreateAssuntoLancaRuntimeExceptionQuandoNomeNaoInformado()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AssuntoModel($adapterMock);

        $params = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao criar assunto: Nome do assunto é obrigatório.');

        $model->create($params);
    }

    public function testUpdateAssuntoLancaRuntimeExceptionQuandoIdOuNomeAusente()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AssuntoModel($adapterMock);

        $params = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao atualizar assunto: ID e nome são obrigatórios.');

        $model->update($params);
    }

    public function testDeleteAssuntoLancaRuntimeExceptionQuandoIdAusente()
    {
        $adapterMock = $this->createMock(Adapter::class);
        $model = new AssuntoModel($adapterMock);

        $params = new Parameters([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Erro ao excluir assunto: ID é obrigatório para exclusão.');

        $model->delete($params);
    }
}
