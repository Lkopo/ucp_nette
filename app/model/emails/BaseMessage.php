<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.02.2017
 * Time: 14:00
 */

namespace App\Model\Emails;

use App\Caching\SettingsCache;
use App\Model\SettingsManager;
use Nette\Mail\Message;
use Kdyby\Translation\Translator;

abstract class BaseMessage extends Message
{
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator */
    protected $translator;

    protected $settings;
    protected $site_name;
    protected $site_email_sign;
    protected $from;

    public function __construct(Translator $translator, SettingsCache $settingsCache) {
        parent::__construct();
        $this->translator = $translator;

        $this->settings = $settingsCache->getAll();

        // Load data from Settings
        $this->site_name = $this->settings->{SettingsManager::SETT_COLUMN_PAGE_NAME};
        $this->site_email_sign = $this->settings->{SettingsManager::SETT_COLUMN_PAGE_EMAIL_SIGN};
        $this->from = $this->settings->{SettingsManager::SETT_COLUMN_PAGE_EMAIL};
    }
}