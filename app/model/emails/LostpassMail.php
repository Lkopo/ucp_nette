<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.02.2017
 * Time: 17:59
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Utils\Strings;

class LostpassMail extends BaseMessage
{

    public function __construct(Translator $translator, SettingsCache $settingsCache, $username, $email, $confirm_link)
    {
        parent::__construct($translator, $settingsCache);
        $subject = $this->translator->translate('emails.lostpass.subject');

        $this->setFrom($this->from)
            ->addTo($email)
            ->setSubject(
                $subject
            )
            ->setBody(
                $this->translator->translate('emails.lostpass.body', NULL, array(
                    'confirm_link' => $confirm_link,
                    'acc_name' => Strings::upper($username),
                    'site_name' => $this->site_name,
                    'site_email_sign' => $this->site_email_sign
                ))
            );
    }
}