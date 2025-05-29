<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\LivroModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\{JsonModel, ViewModel};

class IndexController extends AbstractActionController
{
    public function __construct(
        private readonly LivroModel $livroModel
    ) {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel();
    }
    public function fetchAction(): JsonModel
    {
        try {
            return new JsonModel([
                'status' => 'success',
                'data' => $this->livroModel->fetchLivros(),
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao criar o livro: ' . $e->getMessage(),
            ]);
        }
    }

    public function createAction(): JsonModel
    {
        try {
            return new JsonModel([
                'status' => 'success',
                'data' => $this->livroModel->criar($this->getRequest()->getPost()),
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
        try {
            return new JsonModel([
                'status' => 'success',
                'data' => $this->livroModel->update($this->getRequest()->getPost()),
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao criar o livro: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteAction(): JsonModel
    {
        try {
            return new JsonModel([
                'status' => 'success',
                'data' => $this->livroModel->delete($this->getRequest()->getPost()),
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao criar o livro: ' . $e->getMessage(),
            ]);
        }
    }
}
