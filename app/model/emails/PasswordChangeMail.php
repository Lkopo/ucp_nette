<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 13.04.2017
 * Time: 17:02
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use Kdyby\Translation\Translator;
use Nette\Utils\Strings;

class PasswordChangeMail extends BaseMessage
{
    public function __construct(Translator $translator, SettingsCache $settingsCache, $username, $email, $confirm_link)
    {
        parent::__construct($translator, $settingsCache);
        $subject = $this->translator->translate('emails.passchange.subject');

        $this->setFrom($this->from)
            ->addTo($email)
            ->setSubject(
                $subject
            )
            ->setBody(
                $this->translator->translate('emails.passchange.body', NULL, array(
                    'confirm_link' => $confirm_link,
                    'acc_name' => Strings::upper($username),
                    'site_name' => $this->site_name,
                    'site_email_sign' => $this->site_email_sign
                ))
            );
    }
}