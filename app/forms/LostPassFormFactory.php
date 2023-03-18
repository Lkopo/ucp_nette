<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.02.2017
 * Time: 18:08
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class LostPassFormFactory extends BaseFormFactory
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

        $form->addText('email')
            ->setAttribute('placeholder', 'forms.register.email')
            ->setRequired('forms.register.email_empty')
            ->addRule(Form::EMAIL, 'forms.register.email_invalid')
            ->getLabelPrototype()->setName(NULL);

        $form->addReCaptcha('captcha', NULL)
            ->setRequired('forms.register.not_robot');

        $form->addSubmit('submit', 'forms.lostpass.submit')
            ->setAttribute('class', 'btn-lg btn-primary btn-block');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}