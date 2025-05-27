<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\LivroModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\{JsonModel, ViewModel};

class IndexController extends AbstractActionController
{
    public function __construct(private readonly LivroModel $livroModel)
    {
    }

    public function indexAction(): ViewModel
    {
        return new ViewModel();
    }

    public function fetchAction(): JsonModel
    {
        try {
            $livros = $this->livroModel->fetchLivros();

            return new JsonModel([
                'status' => 'success',
                'data' => $livros,
            ]);
        } catch (\Exception $e) {
            return new JsonModel([
                'status' => 'error',
                'message' => 'Erro ao buscar os livros: ' . $e->getMessage(),
            ]);
        }
    }
}
