<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 30.03.2017
 * Time: 13:02
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class AccSearchByEmailFormFactory extends BaseFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('email', 'forms.logs.email');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}