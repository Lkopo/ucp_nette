<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.02.2017
 * Time: 14:12
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use Kdyby\Translation\Translator;
use Nette\Utils\Strings;

class RegistrationMail extends BaseMessage
{
    public function __construct(Translator $translator, SettingsCache $settingsCache, $username, $email, $act_link)
    {
        parent::__construct($translator, $settingsCache);
        $subject = $this->translator->translate('emails.register.subject');

        $this->setFrom($this->from)
            ->addTo($email)
            ->setSubject(
                $subject
            )
            ->setBody(
                $this->translator->translate('emails.register.body', NULL, array(
                    'act_link' => $act_link,
                    'acc_name' => Strings::upper($username),
                    'site_name' => $this->site_name,
                    'site_email_sign' => $this->site_email_sign
                ))
            );
    }
}
