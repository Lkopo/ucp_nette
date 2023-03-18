<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.03.2017
 * Time: 21:16
 */

namespace App\Forms;

use App\Model\UserManager;
use Nette\Application\UI\Form;

class AccinfoSearchFormFactory extends BaseFormFactory
{
    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    private function accountExists($username_id)
    {
        $user = $this->userManager->findOneByUsernameOrId($username_id);

        if(!$user)
            return false;

        return true;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('username_id', 'forms.logs.username_id')
                //->setRequired('forms.logs.username_id_empty')
            ->setAttribute('autofocus')
            //->setAttribute('class', 'input-sm')
            ->addCondition(Form::FILLED)
            ->addRule(function ($control) {
                return $this->accountExists($control->value);
            }, 'forms.logs.username_id_notfound');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}