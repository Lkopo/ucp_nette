<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.02.2017
 * Time: 18:10
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class LostPassConfirmFormFactory extends BaseFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addPassword('password')
            ->setAttribute('autofocus')
            ->setAttribute('placeholder', 'forms.login.password')
            ->setAttribute('maxlength', '16')
            ->setRequired('forms.login.password_empty')
            ->addRule(Form::MAX_LENGTH, 'forms.register.password_max_length', 16)
            ->getLabelPrototype()->setName(NULL);

        $form->addPassword('passwordVerify')
            ->setAttribute('placeholder', 'forms.register.password_again')
            ->setAttribute('maxlength', '16')
            ->setRequired('forms.login.password_empty')
            ->addRule(Form::EQUAL, 'forms.register.password_verify', $form['password'])
            ->getLabelPrototype()->setName(NULL);

        $form->addSubmit('submit', 'forms.lostpass.confirm.submit')
            ->setAttribute('class', 'btn-lg btn-primary btn-block');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}