<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 18.03.2017
 * Time: 16:05
 */

namespace App\Presenters;

use Nette;
use App\Forms\BaseFormFactory;
use App\Forms\SearchLogsFormFactory;
use App\Model\LogRepository;
use Nette\Application\UI\Form;

class PlayerLogsPresenter extends BasePresenter
{
    /** @var SearchLogsFormFactory @inject */
    public $searchLogsFormFactory;

    /** @persistent */
    public $acc;

    /** @persistent */
    public $ip;

    /** @persistent */
    public $type;

    public function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->chartradeRepository = $this->chartradeRepository;
    }

    public function createComponentSearchLogsForm()
    {
        $form = $this->searchLogsFormFactory->create();

        $form->setAction($this->link('default', ['acc' => null, 'ip' => null, 'type' => null]));

        $form['username_id']->setDefaultValue($this->getParameter('acc'));
        $form['ip']->setDefaultValue($this->getParameter('ip'));
        $form['type']->setDefaultValue($this->getParameter('type'));


        $form->onSuccess[] = [$this, 'searchLogsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function searchLogsFormSucceeded(Form $form, $values)
    {
        $this->redirect('this', ['acc' => $values->username_id, 'ip' => $values->ip, 'type' => $values->type]);
    }

    public function renderDefault($page)
    {
        $acc = $this->getParameter('acc');
        $ip = $this->getParameter('ip');
        $type = $this->getParameter('type');

        $paginator = null;

        if ($acc) {
            $user = $this->userManager->findOneByUsernameOrId($acc);
            if (!$user) {
                $this->flashMessage('messages.player_logs.username_id_notfound', 'danger');
                $logs = null;

                $paginator = new Nette\Utils\Paginator;
            }
        } else {
            $user = null;
        }

        $params = array();

        if($user)
            $params[LogRepository::COLUMN_ACCOUNT] = $user->id;
        if ($ip)
            $params[LogRepository::COLUMN_IP] = $ip;
        if ($type) {
            if (in_array($this->getParameter('type'), LogRepository::USER_TYPES_LIST)) {
                $params[LogRepository::COLUMN_TYPE] = $type;
            } else {
                $this->type = null;
                $this->redirect('default');
            }
        }

        $logs = $this->logRepository->findAllByParams($params);

        if(!$paginator) {
            $paginator = new Nette\Utils\Paginator;
            $paginator->setItemCount($logs->count());
            $paginator->setItemsPerPage(10);
            $paginator->setPage($page);
        }

        $this->template->searched_user = $user;
        $this->template->logs = $logs == null ? null : $logs->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;

        $this->template->param_acc = $acc;
        $this->template->param_ip = $ip;
        $this->template->param_type = $type;
    }
}