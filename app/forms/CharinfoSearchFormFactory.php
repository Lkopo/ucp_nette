<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 18:44
 */

namespace App\Forms;

use App\Model\CharacterRepository;
use Nette\Application\UI\Form;

class CharinfoSearchFormFactory extends BaseFormFactory
{
    /** @var CharacterRepository */
    private $characterRepository;

    public function __construct(CharacterRepository $characterRepository)
    {
        $this->characterRepository = $characterRepository;
    }

    /**
     * @param $charname_id
     * @return bool
     */
    private function characterExists($charname_id)
    {
        $character = $this->characterRepository->findOneByNameOrId($charname_id);

        if(!$character)
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
        $form->addText('charname_id', 'forms.logs.charname_id')
            ->setRequired('forms.logs.charname_id_empty')
            ->setAttribute('autofocus')
            ->setAttribute('class', 'input-lg')
            ->addRule(function ($control) {
                return $this->characterExists($control->value);
            }, 'forms.logs.charname_id_notfound');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-lg btn-primary');

        return $form;
    }
}