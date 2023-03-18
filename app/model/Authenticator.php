<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 12:41
 */

namespace App\Model;

use Nette;
use Nette\Security\IAuthenticator;
use App\Utils\TrinityCore;
use Nette\Security\Passwords;
use Nette\Utils\Strings;

class Authenticator implements IAuthenticator
{
    /** @var Nette\Database\Context */
    private $database;

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    /** @var UserManager */
    private $userManager;

    /** @var Authorizator */
    private $authorizator;

    public function __construct(Nette\Database\Context $database, \Kdyby\Translation\Translator $translator, UserManager $userManager, Authorizator $authorizator)
    {
        $this->database = $database;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->authorizator = $authorizator;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;


        try {
            $row = $this->database->table(UserManager::ACC_TABLE_NAME)->where(UserManager::ACC_COLUMN_NAME, Strings::upper($username))->fetch();
        } catch (Nette\Database\ConnectionException $e) {
            // enabling safemode user
            if($username == AccountRepository::SAFEMODE_ADMIN_NAME && Passwords::verify($password, AccountRepository::SAFEMODE_ADMIN_PASS_HASH)) {
                $roles = [
                    AccountRepository::ROLE_WEBMASTER,
                    AccountRepository::ROLE_ADMIN,
                    AccountRepository::ROLE_HGM,
                    AccountRepository::ROLE_HEM,
                    AccountRepository::ROLE_DEV,
                    AccountRepository::ROLE_GM,
                    AccountRepository::ROLE_EM,
                    AccountRepository::ROLE_GM_ZD,
                    AccountRepository::ROLE_EM_ZD,
                    AccountRepository::ROLE_PLAYER_ID
                ];
                return new Nette\Security\Identity(-1, $roles, ['username' => 'SAFEMODE_ADMIN']);
            } else {
                throw new Nette\Security\AuthenticationException('forms.login.connection_failed', self::FAILURE);
            }
        }

        if (!$row) {
            throw new Nette\Security\AuthenticationException('forms.login.username_incorrect', self::IDENTITY_NOT_FOUND);
        } elseif (!TrinityCore::verify($username, $password, $row[UserManager::ACC_COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('forms.login.password_incorrect', self::INVALID_CREDENTIAL);
        } elseif (!$this->userManager->isActivated($row->id)) {
            throw new Nette\Security\AuthenticationException('messages.user.not_activated', self::NOT_APPROVED);
        }

        $arr = $row->toArray();
        unset($arr[UserManager::ACC_COLUMN_PASSWORD_HASH]);

        // get roles
        $roles = $this->authorizator->getRolesForUser($row[UserManager::ACC_COLUMN_ID]);

        return new Nette\Security\Identity($row[UserManager::ACC_COLUMN_ID], $roles, $arr);
    }
}