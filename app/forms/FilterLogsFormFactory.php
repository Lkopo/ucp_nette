<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 12.03.2017
 * Time: 16:55
 */

namespace App\Forms;

use App\Model\LogRepository;
use Nette\Application\UI\Form;

class FilterLogsFormFactory extends BaseFormFactory
{
    /**
     * @return array
     */
    private function getTypesArray()
    {
        $types = [];

        foreach(LogRepository::USER_TYPES_LIST as $key => $value) {
            $types[$value] = 'pages.logs.types.' . $value;
        }

        return $types;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('ip', 'forms.logs.ip')
            ->setOption('description', 'forms.logs.ip_description');

        $form->addSelect('type', 'forms.logs.type', $this->getTypesArray())
            ->setPrompt('forms.logs.type_choose');

        $form->addSubmit('submit', 'forms.logs.filter')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}