<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.04.2017
 * Time: 18:24
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use Kdyby\Translation\Translator;
use Nette\Utils\Strings;

class LockUnlockMail extends BaseMessage
{
    const
        TYPE_LOCK = 1,
        TYPE_UNLOCK = 2;

    public function __construct(Translator $translator, SettingsCache $settingsCache, $username, $email, $confirm_link, $type)
    {
        parent::__construct($translator, $settingsCache);

        $translate_type = ($type == self::TYPE_LOCK ? 'lock' : 'unlock');

        $subject = $this->translator->translate('emails.lockunlock.' . $translate_type . '.subject');

        $this->setFrom($this->from)
            ->addTo($email)
            ->setSubject(
                $subject
            )
            ->setBody(
                $this->translator->translate('emails.lockunlock.' . $translate_type . '.body', NULL, array(
                    'confirm_link' => $confirm_link,
                    'acc_name' => Strings::upper($username),
                    'site_name' => $this->site_name,
                    'site_email_sign' => $this->site_email_sign
                ))
            );
    }
}