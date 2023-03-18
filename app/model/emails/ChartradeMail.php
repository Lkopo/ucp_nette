<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.04.2017
 * Time: 17:48
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use Kdyby\Translation\Translator;
use Nette\Utils\Strings;

class ChartradeMail extends BaseMessage
{
    const
        TYPE_VERIFY_OFFERER = 1,
        TYPE_VERIFY_REQUESTED = 2;

    public function __construct(Translator $translator, SettingsCache $settingsCache, $username, $email, $offerer_name, $requested_name, $confirm_link, $type)
    {
        parent::__construct($translator, $settingsCache);

        $translate_type = ($type == self::TYPE_VERIFY_OFFERER ? 'offerer' : 'requested');

        $subject = $this->translator->translate('emails.chartrade.' . $translate_type . '.subject');

        $this->setFrom($this->from)
            ->addTo($email)
            ->setSubject(
                $subject
            )
            ->setBody(
                $this->translator->translate('emails.chartrade.' . $translate_type . '.body', NULL, array(
                    'confirm_link' => $confirm_link,
                    'acc_name' => Strings::upper($username),
                    'site_name' => $this->site_name,
                    'offerer_name' => $offerer_name,
                    'requested_name' => $requested_name,
                    'site_email_sign' => $this->site_email_sign
                ))
            );
    }
}