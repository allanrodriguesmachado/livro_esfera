<?php

namespace Application\Controller;

use Application\Model\AssuntoModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class AssuntoController extends AbstractActionController
{
    public function __construct(
        private readonly AssuntoModel $assuntoModel
    )
    {
    }

    public function assuntosAction(): JsonModel
    {
        try {
            $autores = $this->assuntoModel->fetchAll();

            return new JsonModel([
                'status' => 'success',
                'data' => $autores
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao listar autores: ' . $e->getMessage()
            ]);
        }
    }

    public function criarAction(): JsonModel
    {
        try {
            $nome = trim($this->params()->fromPost('nome'));
            if (!$nome) {
                return new JsonModel([
                    'status' => 'error',
                    'message' => 'Nome do assunto é obrigatório.'
                ]);
            }

            $id = $this->assuntoModel->create($nome);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id, 'nome' => $nome]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao cadastrar assunto: ' . $e->getMessage()
            ]);
        }
    }

      public function editarAction(): JsonModel
    {
        try {
            $params = $this->getRequest()->getPost();

            $nome = trim($this->params()->fromPost('nome'));
            if (!$nome) {
                return new JsonModel([
                    'status' => 'error',
                    'message' => 'Nome do assunto é obrigatório.'
                ]);
            }

            $id = $this->assuntoModel->update($params);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id, 'nome' => $nome]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao cadastrar assunto: ' . $e->getMessage()
            ]);
        }
    }

       public function excluirAction(): JsonModel
    {
        try {
            $params = $this->getRequest()->getPost();

            $id = $this->assuntoModel->delete($params);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao cadastrar assunto: ' . $e->getMessage()
            ]);
        }
    }
}