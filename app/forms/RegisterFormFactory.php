<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.02.2017
 * Time: 18:05
 */

namespace App\Forms;

use Nette\Application\UI\Form;
use App\Model\UserManager;

class RegisterFormFactory extends BaseFormFactory
{
    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Chceck availability of username
     *
     * @param $username
     * @return bool
     */
    public function isUsernameAvailable($username)
    {
        return !$this->userManager->userExists($username);
    }

    /**
     * Get domain name from e-mail address
     *
     * @param $email
     * @return string
     */
    private function getDomainFromEmail($email)
    {
        return trim(substr(strrchr(strtolower($email), "@"), 1));
    }

    /**
     * Check if an e-mail address is not blacklisted
     *
     * @param $email
     * @return bool
     */
    public function isWhitelistedEmail($email)
    {
        //@TODO store emails to DB
        $blacklist = array('trbvn.com', '10mail.org', '10minutemail.co.uk', '20mail.it', '163.com', 'aol.co.uk', 'aol.com',
                           'aseyreirtiruyewire.co.tv', 'bovinaisd.net', 'dolphinmail.org', 'forumoxy.com', 'gawab.com', 'cuisine-recette.biz',
                           'gmx.us', 'iaoss.com', 'kismail.com', 'list.ru', 'mail.ru', 'mailcatch.com', 'mailinator.com', 'my10minutemail.com',
                           'o2.pl', 'phomail.com', 'rmqkr.net', 'rooseveltmail.com', 'tylerexpress.com', 'yahoo.co.uk', 'yandex.com', 'yandex.ru',
                           'dropmail.me', 'projectmy.in');

        return !in_array($this->getDomainFromEmail($email), $blacklist);
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addGroup();
        $form->addText('username', 'forms.login.username')
            ->setAttribute('autofocus')
            ->setAttribute('placeholder', 'forms.login.username')
            ->setRequired('forms.login.username_empty')
            ->addRule(function ($control) {
                return $this->isUsernameAvailable($control->value);
            }, 'forms.register.username_used')
            ->addRule(Form::MIN_LENGTH, 'forms.register.username_min_length', 3);

        $form->addText('email', 'forms.register.email')
            ->setAttribute('placeholder', 'forms.register.email')
            ->setRequired('forms.register.email_empty')
            ->addRule(Form::EMAIL, 'forms.register.email_invalid')
            ->addRule(function ($control) {
                return $this->isWhitelistedEmail($control->value);
            }, 'forms.register.email_blacklisted');

        $form->addPassword('password', 'forms.login.password')
            ->setAttribute('placeholder', 'forms.login.password')
            ->setAttribute('maxlength', '16')
            ->setRequired('forms.login.password_empty')
            ->addRule(Form::MAX_LENGTH, 'forms.register.password_max_length', 16);

        $form->addPassword('passwordVerify', 'forms.register.password_again')
            ->setAttribute('placeholder', 'forms.register.password_again')
            ->setAttribute('maxlength', '16')
            ->setRequired('forms.login.password_empty')
            ->addRule(Form::EQUAL, 'forms.register.password_verify', $form['password']);

        $form->addReCaptcha('captcha', NULL, 'forms.register.not_robot');

        $form->addSubmit('submit', 'forms.register.register')
            ->setAttribute('class', 'btn-lg btn-success btn-block');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}