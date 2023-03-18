<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 18:14
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\CharinfoSearchFormFactory;
use Nette\Application\UI\Form;

class CharinfoPresenter extends BasePresenter
{
    /** @var CharinfoSearchFormFactory @inject */
    public $charinfoSearchFormFactory;

    /** @persistent */
    public $char;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentCharinfoSearchForm()
    {
        $form = $this->charinfoSearchFormFactory->create();

        $form->setAction($this->link('default', ['char' => null]));

        $form['charname_id']->setDefaultValue($this->getParameter('char'));

        $form->onSuccess[] = [$this, 'charinfoSearchFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function charinfoSearchFormSucceeded(Form $form, $values)
    {
        $this->redirect('this', ['char' => $values->charname_id]);
    }

    public function renderDefault()
    {
        $char = $this->getParameter('char');

        if($char) {
            $character = $this->characterRepository->findOneByNameOrId($char);
            if($character) {
                $this->template->character = $character;
            } else {
                $this->flashMessage('messages.charinfo.charname_id_notfound', 'danger');
                $this->redirect('this', ['char' => null]);
            }
        } else {
            $this->template->character = null;
        }
    }
}