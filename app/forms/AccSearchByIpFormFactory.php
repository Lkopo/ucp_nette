<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 30.03.2017
 * Time: 13:39
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class AccSearchByIpFormFactory extends BaseFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('ip', 'forms.logs.chartrade.ip');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}