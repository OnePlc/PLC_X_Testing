<?php
/**
 * InstallController.php - Main Controller
 *
 * Installer for Plugin
 *
 * @category Controller
 * @package Testing
 * @author Verein onePlace
 * @copyright (C) 2021  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.1
 */

declare(strict_types=1);

namespace OnePlace\Testing\Controller;

use Application\Controller\CoreUpdateController;
use Application\Model\CoreEntityModel;
use OnePlace\Contact\Address\Model\AddressTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;

class InstallController extends CoreUpdateController {
    /**
     * InstallController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param AddressTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, TableGateway $oTableGateway, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'testing-single';
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);

        if ($oTableGateway) {
            # Attach TableGateway to Entity Models
            if (! isset(CoreEntityModel::$aEntityTables[$this->sSingleForm])) {
                CoreEntityModel::$aEntityTables[$this->sSingleForm] = $oTableGateway;
            }
        }
    }

    public function checkdbAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('testing');

        $oRequest = $this->getRequest();

        if(! $oRequest->isPost()) {

            $bTableExists = false;

            try {
                $this->oTableGateway->select();
                $bTableExists = true;
            } catch (\RuntimeException $e) {

            }

            return new ViewModel([
                'bTableExists' => $bTableExists,
                'sVendor' => 'oneplace',
                'sModule' => 'oneplace-testing',
            ]);
        } else {
            $sSetupConfig = $oRequest->getPost('plc_module_setup_config');

            $sSetupFile = 'vendor/oneplace/oneplace-testing/data/install.sql';
            if(file_exists($sSetupFile)) {
                echo 'got install file..';
                $this->parseSQLInstallFile($sSetupFile,CoreUpdateController::$oDbAdapter);
            }

            if($sSetupConfig != '') {
                $sConfigStruct = 'vendor/oneplace/oneplace-testing/data/structure_'.$sSetupConfig.'.sql';
                if(file_exists($sConfigStruct)) {
                    echo 'got struct file for config '.$sSetupConfig;
                    $this->parseSQLInstallFile($sConfigStruct,CoreUpdateController::$oDbAdapter);
                }
                $sConfigData = 'vendor/oneplace/oneplace-testing/data/data_'.$sSetupConfig.'.sql';
                if(file_exists($sConfigData)) {
                    echo 'got data file for config '.$sSetupConfig;
                    $this->parseSQLInstallFile($sConfigData,CoreUpdateController::$oDbAdapter);
                }
            }

            $oModTbl = new TableGateway('core_module', CoreUpdateController::$oDbAdapter);
            $oModTbl->insert([
                'module_key' => 'oneplace-testing',
                'type' => 'plugin',
                'version' => \OnePlace\Testing\Module::VERSION,
                'label' => 'onePlace Testing',
                'vendor' => 'oneplace',
            ]);

            try {
                $this->oTableGateway->select();
                $bTableExists = true;
            } catch (\RuntimeException $e) {

            }
            $bTableExists = false;

            $this->flashMessenger()->addSuccessMessage('Testing DB Update successful');
            $this->redirect()->toRoute('application', ['action' => 'checkforupdates']);
        }
    }
}
