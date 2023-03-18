<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 11:13
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\SearchChartradeLogsFormFactory;
use App\Model\SettingsManager;
use Nette;
use Nette\Application\UI\Form;

class ChartradeLogsPresenter extends BasePresenter
{
    /** @var SearchChartradeLogsFormFactory @inject */
    public $searchChartradeLogsFormFactory;

    /** @persistent */
    public $acc;

    /** @persistent */
    public $char;

    /** @persistent */
    public $ip;

    /** @persistent */
    public $cancelled;

    public function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_CHARTRADE);
    }

    public function createComponentSearchChartradeLogsForm()
    {
        $form = $this->searchChartradeLogsFormFactory->create();

        $form->setAction($this->link('default', ['acc' => null, 'char' => null, 'ip' => null, 'cancelled' => null]));

        $form['username_id']->setDefaultValue($this->getParameter('acc'));
        $form['charname_id']->setDefaultValue($this->getParameter('char'));
        $form['ip']->setDefaultValue($this->getParameter('ip'));
        $form['search_cancelled']->setDefaultValue($this->getParameter('cancelled'));

        $form->onSuccess[] = [$this, 'searchChartradeLogsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function searchChartradeLogsFormSucceeded(Form $form, $values)
    {
        $this->redirect('this', ['acc' => $values->username_id, 'char' => $values->charname_id, 'ip' => $values->ip, 'cancelled' => ($values->search_cancelled == 0 ? null : 1)]);
    }

    public function renderDefault($page)
    {
        $acc = $this->getParameter('acc');
        $char = $this->getParameter('char');
        $ip = $this->getParameter('ip');
        $cancelled = $this->getParameter('cancelled');

        $params = array();
        if($acc) {
            $user = $this->userManager->findOneByUsernameOrId($acc);

            if(!$user)
                $params['acc'] = 0;
            else
                $params['acc'] = $user->id;
        }
        if($char)
            $params['char'] = $char;
        if($ip)
            $params['ip'] = $ip;

        $params['cancelled'] = $cancelled;

        $logs = $this->chartradeRepository->findAllWithParams($params);

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($logs->count());
        $paginator->setItemsPerPage(10);
        $paginator->setPage($page);

        $this->template->param_acc = $acc;
        $this->template->param_char = $char;
        $this->template->param_ip = $ip;
        $this->template->param_cancelled = $cancelled;
        $this->template->logs = $logs->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
    }
}