<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 18.03.2017
 * Time: 16:11
 */

namespace App\Forms;

use App\Model\LogRepository;
use App\Model\UserManager;
use Nette\Application\UI\Form;

class SearchLogsFormFactory extends BaseFormFactory
{
    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

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
     * @param $username_id
     * @return bool
     */
    public function usernameOrIdExists($username_id)
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
            ->setRequired(FALSE)
            ->addRule(function ($control) {
                return $this->usernameOrIdExists($control->value);
            }, 'forms.logs.username_id_notfound');

        $form->addText('ip', 'forms.logs.ip')
            ->setOption('description', 'forms.logs.ip_description');

        $form->addSelect('type', 'forms.logs.type', $this->getTypesArray())
            ->setPrompt('forms.logs.type_choose');

        $form->addSubmit('submit', 'forms.logs.search')
            ->setAttribute('class', 'btn-primary');

        return $form;
    }
}