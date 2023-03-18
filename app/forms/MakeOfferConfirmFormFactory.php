<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 04.03.2017
 * Time: 12:41
 */

namespace App\Forms;

use App\Model\AccountRepository;
use App\Model\ChartradeRepository;
use App\Model\UserManager;
use Nette\Security\User;
use Nette\Application\UI\Form;
use App\Model\CharacterRepository;

class MakeOfferConfirmFormFactory extends BaseFormFactory
{
    /** @var ChartradeRepository */
    private $chartradeRepository;

    /** @var CharacterRepository */
    private $characterRepository;

    /** @var AccountRepository */
    private $accountRepository;

    /** @var UserManager */
    private $userManager;

    /** @var User */
    private $user;

    // temporary store character objects
    private $tmpCharData;

    // temporary store user objects
    private $tmpAccData;

    public function __construct(ChartradeRepository $chartradeRepository, CharacterRepository $characterRepository, AccountRepository $accountRepository, UserManager $userManager, User $user)
    {
        $this->chartradeRepository = $chartradeRepository;
        $this->characterRepository = $characterRepository;
        $this->accountRepository = $accountRepository;
        $this->userManager = $userManager;
        $this->user = $user;

        $this->tmpCharData = array();
        $this->tmpAccData = array();
    }

    /**
     * @param $name_id
     * @return bool
     */
    private function characterExists($name_id)
    {
        $row = isset($this->tmpCharData[$name_id]) ? $this->tmpCharData[$name_id] : $this->tmpCharData[$name_id] = $this->characterRepository->findOneByNameOrId($name_id);

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
     * @param $name
     * @return bool
     */
    private function belongsToAnother($name)
    {
        $row = isset($this->tmpCharData[$name]) ? $this->tmpCharData[$name] : $this->tmpCharData[$name] = $this->characterRepository->findOneByName($name);

        if(!$row)
            return false;

        return !($row->account == $this->user->id);
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasAccBan($name)
    {
        $row = isset($this->tmpCharData[$name]) ? $this->tmpCharData[$name] : $this->tmpCharData[$name] = $this->characterRepository->findOneByName($name);
        if(!$row)
            return false;

        return $this->userManager->hasBan($row->account);
    }

    /**
     * @param $data
     * @return bool
     */
    private function hasBan($data)
    {
        if((int) $data == 0) {
            $row = isset($this->tmpCharData[$data]) ? $this->tmpCharData[$data] : $this->tmpCharData[$data] = $this->characterRepository->findOneByName($data);
            if(!$row)
                return false;
            $guid = $row->guid;
        } else {
            $guid = $data;
        }
        return $this->characterRepository->hasBan($guid);
    }

    /**
     * @param null $id
     * @return bool
     */
    private function hasMute($id = NULL)
    {
        if($id == NULL) {
            $user = isset($this->tmpAccData[$id]) ? $this->tmpAccData[$id] : $this->tmpAccData[$id] = $this->userManager->findOneById($this->user->id);
        } else {
            $row = isset($this->tmpCharData[$id]) ? $this->tmpCharData[$id] : $this->tmpCharData[$id] = $this->characterRepository->findOneByName($id);
            if(!$row)
                return false;

            $user =  isset($this->tmpAccData[$id]) ? $this->tmpAccData[$id] : $this->tmpAccData[$id] = $this->userManager->findOneById($row->account);
        }

        return $user->mutetime > 0;
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasAlreadyOffers($name)
    {
        $row = isset($this->tmpCharData[$name]) ? $this->tmpCharData[$name] : $this->tmpCharData[$name] = $this->characterRepository->findOneByName($name);
        if(!$row)
            return false;

        return $this->chartradeRepository->findVerifiedByAccount($row->account)->count() > 0;
    }

    /**
     * @param $name
     * @return bool
     */
    private function isPrivileged($name)
    {
        $row = isset($this->tmpCharData[$name]) ? $this->tmpCharData[$name] : $this->tmpCharData[$name] = $this->characterRepository->findOneByName($name);
        if(!$row)
            return false;

        return $this->userManager->getGmLevel($row->account) > 0 || $this->accountRepository->findAllUserRolesById($row->account)->count() > 0;
    }

    /**
     * @param $my_char
     * @param $dest_char
     * @return Form
     */
    public function create($my_char, $dest_char)
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addHidden('my_character', $my_char)
            ->setRequired('forms.chartrade.select_empty')
            ->addRule(function ($control) {
                return $this->characterExists($control->value);
            }, 'forms.global_character.not_exists')
            ->addRule(function ($control) {
                return $this->isOwner($control->value);
            }, 'forms.chartrade.not_owner')
            ->addRule(function ($control) {
                return !$this->hasBan($control->value);
            }, 'forms.chartrade.select_banned')
            ->addRule(function () {
                return !$this->hasMute();
            }, 'forms.chartrade.muted');

        $form->addHidden('dest_character', $dest_char)
            ->setRequired('forms.chartrade.dest_empty')
            ->addRule(function ($control) {
                return $this->characterExists($control->value);
            }, 'forms.chartrade.dest_not_exists')
            ->addRule(function ($control) {
                return $this->belongsToAnother($control->value);
            }, 'forms.chartrade.dest_same_owner')
            ->addRule(function ($control) {
                return !$this->hasAlreadyOffers($control->value);
            }, 'forms.chartrade.dest_already_offer')
            ->addRule(function ($control) {
                return !$this->hasBan($control->value);
            }, 'forms.chartrade.dest_banned')
            ->addRule(function ($control) {
                return !$this->hasMute($control->value);
            }, 'forms.chartrade.dest_muted')
            ->addRule(function ($control) {
                return !$this->hasAccBan($control->value);
            }, 'forms.chartrade.banned_dest_acc')
            ->addRule(function ($control) {
                return !$this->isPrivileged($control->value);
            }, 'forms.chartrade.dest_privileged');

        $form->addSubmit('submit', 'forms.chartrade.confirm')
            ->setAttribute("class", "btn-success");

        $form->addSubmit('cancel', 'forms.chartrade.cancel')
            ->setAttribute('class', 'btn-default');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}