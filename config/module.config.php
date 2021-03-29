<?php
/**
 * module.config.php - Testing Config
 *
 * Main Config File for Testing Module
 *
 * @category Config
 * @package Testing
 * @author Verein onePlace
 * @copyright (C) 2021  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Testing;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    # Weos Module - Routes
    'router' => [
        'routes' => [
            'testing-backend' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/testing[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\BackendController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'testing-setup' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/testing/setup[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\InstallController::class,
                        'action'     => 'checkdb',
                    ],
                ],
            ],
        ],
    ],

    # View Settings
    'view_manager' => [
        'template_path_stack' => [
            'testing' => __DIR__ . '/../view',
        ],
    ],

    # Translator
    'translator' => [
        'locale' => 'de_DE',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];