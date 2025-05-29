<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\{AssuntoController, AutorController, IndexController, RelatorioController};
use Application\Model\{AssuntoModel, AutorModel, LivroModel};
use Laminas\Router\Http\{Literal, Segment};
use Laminas\Db\Adapter\{Adapter, AdapterServiceFactory};
use Psr\Container\ContainerInterface;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'application' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/application[/:action]',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'fetch',
                    ],
                ],
            ],
            'autor' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/autor[/:action]',
                    'defaults' => [
                        'controller' => AutorController::class,
                        'action' => 'listar',
                    ],
                ],
            ],

            'assunto' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/assunto[/:action]',
                    'defaults' => [
                        'controller' => AssuntoController::class,
                        'action' => 'listar',
                    ],
                ],
            ],
            'relatorio' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/relatorio/livros',
                    'defaults' => [
                        'controller' => RelatorioController::class,
                        'action' => 'gerarRelatorio',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            Adapter::class => AdapterServiceFactory::class,
            LivroModel::class => function (ContainerInterface $container) {
                return new LivroModel($container->get(Adapter::class));
            },
            AssuntoModel::class => function (ContainerInterface $container) {
                return new AssuntoModel($container->get(Adapter::class));
            },
            AutorModel::class => function (ContainerInterface $container) {
                return new AutorModel($container->get(Adapter::class));
            }
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => function (ContainerInterface $container) {
                return new IndexController($container->get(LivroModel::class));
            },

            AssuntoController::class => function (ContainerInterface $container) {
                return new AssuntoController($container->get(AssuntoModel::class));
            },
            AutorController::class => function (ContainerInterface $container) {
                return new AutorController($container->get(AutorModel::class));
            },
             RelatorioController::class => function (ContainerInterface $container) {
                return new RelatorioController($container->get(LivroModel::class));
             },
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
