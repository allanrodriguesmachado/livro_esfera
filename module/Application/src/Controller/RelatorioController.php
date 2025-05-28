<?php

namespace Application\Controller;

use Dompdf\Dompdf;
use Laminas\Mvc\Controller\AbstractActionController;
use Application\Model\LivroModel;

class RelatorioController extends AbstractActionController
{
    public function __construct(private readonly LivroModel $livroModel)
    {
    }

    public function gerarRelatorioAction(): \Laminas\Http\PhpEnvironment\Response|\Laminas\Stdlib\ResponseInterface
    {
        $dados = $this->livroModel->buscarDadosRelatorio();
        $dataGeracao = (new \DateTime())->format('d/m/Y H:i');

        $html = '
                    <style>
                        body {
                            font-family: sans-serif;
                            font-size: 12px;
                        }
                        h2 {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .data-geracao {
                            text-align: right;
                            font-size: 10px;
                            margin-bottom: 10px;
                            color: #555;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                        }
                        th {
                            background-color: #f0f0f0;
                            text-align: center;
                        }
                        td {
                            border: 1px solid #ccc;
                            padding: 6px 8px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        td.valor {
                            text-align: right;
                        }
                    </style>
                    
                    
                    <div class="data-geracao">Gerado em: ' . $dataGeracao . '</div>
                    <h2>Relatório de Livros</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Editora</th>
                                <th>Autor</th>
                                <th>Assunto</th>
                                <th>Ano</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                    ';

        foreach ($dados as $livro) {
            $html .= '<tr>
        <td>' . htmlspecialchars($livro['titulo']) . '</td>
        <td>' . htmlspecialchars($livro['editora']) . '</td>
        <td>' . htmlspecialchars($livro['autor']) . '</td>
        <td>' . htmlspecialchars($livro['assunto']) . '</td>
        <td>' . $livro['ano_publicacao'] . '</td>
        <td class="valor">R$ ' . number_format($livro['valor'], 2, ',', '.') . '</td>
    </tr>';
        }

        $html .= '</tbody></table>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');
        $response->setContent($dompdf->output());

        return $response;
    }
}
