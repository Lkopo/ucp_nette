<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 16:36
 */

namespace App\Forms;

use App\Model\CharacterRepository;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Security\User;

class SelectCharacterFormFactory extends BaseFormFactory
{
    /** @var CharacterRepository */
    private $characterRepository;

    /** @var UserManager */
    private $userManager;

    /** @var User */
    private $user;

    // temporary store character objects
    private $tmpCharData;

    public function __construct(CharacterRepository $characterRepository, UserManager $userManager, User $user)
    {
        $this->characterRepository = $characterRepository;
        $this->userManager = $userManager;
        $this->user = $user;
        $this->tmpCharData = array();
    }

    private function characterExists($guid)
    {
        $row = isset($this->tmpCharData[$guid]) ? $this->tmpCharData[$guid] : $this->tmpCharData[$guid] = $this->characterRepository->findOneById($guid);

        if(!$row)
            return false;

        return true;
    }

    /**
     * @param $guid
     * @return bool
     */
    private function isOwner($guid)
    {
        $row = isset($this->tmpCharData[$guid]) ? $this->tmpCharData[$guid] : $this->tmpCharData[$guid] = $this->characterRepository->findOneById($guid);

        if(!$row)
            return false;

        return $row->account == $this->user->id;
    }

    /**
     * @param $guid
     * @return bool
     */
    private function hasFlags($guid)
    {
        $row = isset($this->tmpCharData[$guid]) ? $this->tmpCharData[$guid] : $this->tmpCharData[$guid] = $this->characterRepository->findOneById($guid);

        if(!$row)
            return false;

        return $row->at_login != 0;
    }

    /**
     * @param $guid
     * @return bool
     */
    private function hasBan($guid)
    {
        return $this->characterRepository->hasBan($guid);
    }

    /**
     * @return bool
     */
    private function hasMute()
    {
        // get actual mute status
        $user = $this->userManager->findOneById($this->user->id);

        return $user->mutetime > 0;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $t = $this->translator;

        $characters = $this->characterRepository->findByAccountForSelection($this->user->id);

        $form->addGroup();
        // disable translator because of options are tried
        // to be translated when translator is set ON
        //
        // translate only labels instead
        $form->addSelect('my_character', $t->trans('forms.global_character.select_character'), $characters)
            ->setPrompt($t->trans('forms.global_character.choose_character'))
            ->setTranslator(NULL)
            ->setRequired('forms.global_character.select_empty')
            ->addRule(function ($control) {
                return $this->characterExists($control->value);
            }, 'forms.global_character.not_exists')
            ->addRule(function ($control) {
                return $this->isOwner($control->value);
            }, 'forms.global_character.not_owner')
            ->addRule(function ($control) {
                return !$this->hasFlags($control->value);
            }, 'forms.global_character.already_flagged')
            ->addRule(function ($control) {
                return !$this->hasBan($control->value);
            }, 'forms.global_character.banned')
            ->addRule(function () {
                return !$this->hasMute();
            }, 'forms.global_character.muted');

        $form->addSubmit('submit', 'forms.global_character.submit')
            ->setAttribute('class', 'btn-success')
            ->setAttribute('onclick', 'return confirm("' . $t->trans('forms.global.confirm_continue') . '")');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}