<?php
/**
 * BackendController.php - Main Controller
 *
 * Backend Controller Testing Module
 *
 * @category Controller
 * @package Testing
 * @author Verein onePlace
 * @copyright (C) 2021  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Testing\Controller;

use Application\Controller\CoreController;
use Application\Controller\CoreEntityController;
use Application\Model\CoreEntityModel;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Select;


class BackendController extends CoreEntityController
{
    /**
     * Weos Table Object
     *
     * @since 1.0.0
     */
    protected $oTableGateway;

    /**
     * Tables from other Modules
     *
     * @var $aPluginTbls
     * @since 1.0.'
     */
    protected $aPluginTbls;

    /**
     * WizardController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param WeosTable $oTableGateway
     * @param array $aPluginTbls
     * @param $oServiceManager
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, $oTableGateway, $aPluginTbls, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'testing-single';
        $this->aPluginTbls = $aPluginTbls;
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    /**
     * Testing Dashboard for User
     *
     * @return ViewModel
     * @since 1.0.0
     */
    public function indexAction()
    {
        $this->setThemeBasedLayout('testing');

        $aMyOpenTests = [];
        $iMyUserID = CoreEntityController::$oSession->oUser->getID();
        $oMyOpenTestsDB = $this->aPluginTbls['testing-user']->select([
            'user_idfs' => $iMyUserID,
            'state' => 'open',
            'role' => 'tester']);
        if(count($oMyOpenTestsDB) > 0) {
            foreach($oMyOpenTestsDB as $oOpen) {
                $oTest = $this->oTableGateway->select(['Test_ID' => $oOpen->test_idfs])->current();
                $aMyOpenTests[] = $oTest;
            }
        }

        return new ViewModel([
            'aMyOpenTests' => $aMyOpenTests,
        ]);
    }

    public function viewAction()
    {
        $this->setThemeBasedLayout('testing');

        $iTestID = $this->params()->fromRoute('id', 0);
        $oTest = $this->oTableGateway->select(['Test_ID' => $iTestID])->current();
        $iMyUserID = CoreEntityController::$oSession->oUser->getID();
        $oTestInfo = $this->aPluginTbls['testing-user']->select([
            'test_idfs' => $iTestID,
            'user_idfs' => $iMyUserID,
        ])->current();
        $oTest->state = $oTestInfo->state;

        $aSteps = [];
        $oStepSel = new Select($this->aPluginTbls['testing-step']->getTable());
        $oStepSel->where(['test_idfs' => $iTestID,'user_idfs' => $iMyUserID]);
        $oStepSel->order('sort_id ASC');
        $oStepsDB = $this->aPluginTbls['testing-step']->selectWith($oStepSel);
        if(count($oStepsDB) > 0) {
            foreach($oStepsDB as $oStep) {
                $aSteps[] = $oStep;
            }
        }
        return new ViewModel([
            'oTest' => $oTest,
            'aSteps' => $aSteps,
        ]);
    }

    public function startAction()
    {
        $this->setThemeBasedLayout('testing');

        $iTestID = $this->params()->fromRoute('id', 0);
        $oTest = $this->oTableGateway->select(['Test_ID' => $iTestID])->current();
        $iMyUserID = CoreEntityController::$oSession->oUser->getID();

        $oRequest = $this->getRequest();
        if($oRequest->isPost()) {
            $iStepID = $oRequest->getPost('step_id');
            $iResultType = $oRequest->getPost('result_type');
            $sResultDesc = $oRequest->getPost('result_description');
            $sResultComment = $oRequest->getPost('result_comment');

            $this->aPluginTbls['testing-step']->update([
                'is_done' => 1,
                'result_type_idfs' => (int)$iResultType,
                'result_description' => $sResultDesc,
                'result_comment' => $sResultComment,
                'date' => date('Y-m-d H:i:s', time()),
            ],'Task_ID = '.$iStepID);

            return $this->redirect()->toRoute('testing-backend', ['action' => 'start', 'id' => $iTestID]);
        }

        $aSteps = [];
        $oStepSel = new Select($this->aPluginTbls['testing-step']->getTable());
        $oStepSel->where(['test_idfs' => $iTestID,'user_idfs' => $iMyUserID,'is_done' => 0]);
        $oStepSel->order('sort_id ASC');
        $oStepsDB = $this->aPluginTbls['testing-step']->selectWith($oStepSel);
        $oNextStep = false;
        $iAllSteps = 0;
        $iStepsDone = 0;
        if(count($oStepsDB) > 0) {
            $iAllSteps = count($oStepsDB);
            $iStepsDone = count($this->aPluginTbls['testing-step']->select(['test_idfs' => $iTestID,'user_idfs' => $iMyUserID,'is_done' => 1]));
            foreach($oStepsDB as $oStep) {
                $aSteps[] = $oStep;
                $oNextStep = $oStep;
                break;
            }
        }

        if($iAllSteps == 0) {
            $this->aPluginTbls['testing-user']->update([
                'state' => 'done',
            ],[
                'test_idfs' => $iTestID,
                'user_idfs' => $iMyUserID,
            ]);
            return $this->redirect()->toRoute('testing-backend', ['action' => 'view', 'id' => $iTestID]);
        }

        # also count current step
        $iStepsDone++;
        return new ViewModel([
            'oTest' => $oTest,
            'oNextStep' => $oNextStep,
            'iStepsDone' => $iStepsDone,
            'iAllSteps' => $iAllSteps,
        ]);
    }
}