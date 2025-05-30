<?php

namespace Application\Controller;

use Application\Model\AutorModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class AutorController extends AbstractActionController
{
    public function __construct(
        private readonly AutorModel $autorModel
    ) {
    }

    public function autoresAction(): JsonModel
    {
        try {
            $autores = $this->autorModel->listar();

            return new JsonModel([
                'status' => 'success',
                'data' => $autores
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function criarAction(): JsonModel
    {
        try {
            $nome = trim($this->params()->fromPost('nome'));
            if (! $nome) {
                return new JsonModel([
                    'status' => 'error',
                    'message' => 'Nome do assunto é obrigatório.'
                ]);
            }

            $id = $this->autorModel->create($nome);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id, 'nome' => $nome]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function editarAction(): JsonModel
    {
        try {
            $params = $this->getRequest()->getPost();

            $nome = trim($this->params()->fromPost('nome'));
            if (! $nome) {
                return new JsonModel([
                    'status' => 'error',
                    'message' => 'Nome do assunto é obrigatório.'
                ]);
            }

            $id = $this->autorModel->update($params);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id, 'nome' => $nome]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function excluirAction(): JsonModel
    {
        try {
            $params = $this->getRequest()->getPost();

            $id = $this->autorModel->delete($params);
            return new JsonModel([
                'status' => 'success',
                'data' => ['id' => $id]
            ]);
        } catch (\Throwable $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
