<?php
/**
 * Module.php - Module Class
 *
 * Module Class File for Testing Module
 *
 * @category Config
 * @package Testing
 * @author Verein onePlace
 * @copyright (C) 2021  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.5
 * @since 1.0.0
 */

namespace OnePlace\Testing;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Session\Config\StandardConfig;

class Module {
    /**
     * Module Version
     *
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Load module config file
     *
     * @since 1.0.0
     * @return array
     */
    public function getConfig() : array {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Load Models
     */
    public function getServiceConfig() : array {
        return [
            'factories' => [
            ],
        ];
    }

    /**
     * Load Controllers
     */
    public function getControllerConfig() : array {
        return [
            'factories' => [
                # Web Main Controller
                Controller\BackendController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $oBuildTbl = new TableGateway('testing_test', $oDbAdapter);
                    $aPluginTbls = [];
                    $aPluginTbls['testing-user'] = new TableGateway('testing_test_user', $oDbAdapter);
                    $aPluginTbls['testing-step'] = new TableGateway('testing_test_task', $oDbAdapter);

                    return new Controller\BackendController(
                        $oDbAdapter,
                        $oBuildTbl,
                        $aPluginTbls,
                        $container
                    );
                },
                # Installer
                Controller\InstallController::class => function($container) {
                    $oDbAdapter = $container->get(AdapterInterface::class);
                    $oBuildTbl = new TableGateway('testing_test', $oDbAdapter);
                    return new Controller\InstallController(
                        $oDbAdapter,
                        $oBuildTbl,
                        $container
                    );
                },
            ],
        ];
    }
}