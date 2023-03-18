<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.02.2017
 * Time: 17:45
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class SignInFormFactory extends BaseFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('username')
            ->setAttribute('autofocus')
            ->setAttribute('placeholder', 'forms.login.username')
            ->setRequired('forms.login.username_empty')
            ->addRule(Form::MIN_LENGTH, 'forms.register.username_min_length', 3)
            ->getLabelPrototype()->setName(NULL);

        $form->addPassword('password')
            ->setAttribute('placeholder', 'forms.login.password')
            ->setAttribute('maxlength', '16')
            ->setRequired('forms.login.password_empty')
            ->getLabelPrototype()->setName(NULL);

        $form->addSubmit('submit', 'forms.login.sign_in')
            ->setAttribute('class', 'btn-lg btn-success btn-block');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}