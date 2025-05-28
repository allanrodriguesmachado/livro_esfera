<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\LivroModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\{JsonModel, ViewModel};

class LivroController extends AbstractActionController
{
    public function __construct(
        private readonly LivroModel $livroModel
    )
    {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel();
    }

    public function fetchAction(): JsonModel
    {
        return $this->respondWithJson(function () {
            return $this->livroModel->fetchLivros();
        });
    }

    public function createAction(): JsonModel
    {
        $params = $this->getRequest()->getPost();


        try {
            return new JsonModel([
                'status' => 'success',
                'data' => $this->livroModel->criar($params),
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao criar o livro: ' . $e->getMessage(),
            ]);
        }
    }

    public function updateAction(): JsonModel
    {
        $params = $this->getRequest()->getPost();

        return $this->respondWithJson(function () use ($params) {
            return $this->livroModel->update($params);
        });
    }

    public function deleteAction(): JsonModel
    {
        $params = $this->getRequest()->getPost();

        return $this->respondWithJson(function () use ($params) {
            return $this->livroModel->delete($params);
        });
    }
    private function respondWithJson(callable $callback): JsonModel
    {
        try {
            $data = $callback();
            return new JsonModel([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
