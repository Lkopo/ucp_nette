<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 17:31
 */

namespace App\Forms;

use Nette\Application\UI\Form;

class SearchChartradeLogsFormFactory extends BaseFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('username_id', 'forms.logs.username_id');

        $form->addText('charname_id', 'forms.logs.charname_id');

        $form->addText('ip', 'forms.logs.chartrade.ip')
            ->setOption('description', 'forms.logs.ip_description');

        $form->addCheckbox('search_cancelled', 'forms.logs.chartrade.search_cancelled');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}