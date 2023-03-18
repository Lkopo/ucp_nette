<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 11.03.2017
 * Time: 16:28
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\FilterLogsFormFactory;
use App\Model\LogRepository;
use Nette;
use Nette\Application\UI\Form;

class MyLogsPresenter extends BasePresenter
{
    /** @var FilterLogsFormFactory @inject */
    public $filterLogsFormFactory;

    /** @persistent */
    public $ip;

    /** @persistent */
    public $type;

    protected function startup()
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

    public function createComponentFilterLogsForm()
    {
        $form = $this->filterLogsFormFactory->create();

        $form->setAction($this->link('default', ['ip' => null, 'type' => null]));

        $form['ip']->setDefaultValue($this->getParameter('ip'));
        $form['type']->setDefaultValue($this->getParameter('type'));

        $form->onSuccess[] = [$this, 'filterLogsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function filterLogsFormSucceeded(Form $form, $values)
    {
        $this->redirect('this', ['ip' => $values->ip, 'type' => $values->type]);
    }

    public function renderDefault($page)
    {
        $ip = $this->getParameter('ip');
        $type = $this->getParameter('type');

        $params = array();
        $params[LogRepository::COLUMN_ACCOUNT] = $this->user->id;

        if($ip)
            $params[LogRepository::COLUMN_IP] = $ip;
        if($type) {
            if (in_array($this->getParameter('type'), LogRepository::USER_TYPES_LIST)) {
                $params[LogRepository::COLUMN_TYPE] = $type;
            } else {
                $this->type = null;
                $this->redirect('default');
            }
        }

        $logs = $this->logRepository->findAllByParams($params);

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($logs->count());
        $paginator->setItemsPerPage(10);
        $paginator->setPage($page);

        $this->template->param_ip = $ip;
        $this->template->param_type = $type;
        $this->template->logs = $logs->limit($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
    }
}