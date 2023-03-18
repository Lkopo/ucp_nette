<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.02.2017
 * Time: 15:33
 */

namespace App\Model;

use Nette;
use App\Utils\TrinityCore;
use Nette\Utils\Strings;

class UserManager extends Nette\Object
{
    // temporary solution
    const
        CURRENT_REALMID = 2;

    const
        STATUS_LOCKED = 1,
        STATUS_UNLOCKED = 0;

    const
        EXPANSION_TBC = 1,
        EXPANSION_WOTLK = 2,
        EXPANSION_CATACLYSM = 3;

    const
        VIP_PLVL = 1;

    const
        ACC_TABLE_NAME = 'account',
        ACC_COLUMN_ID = 'id',
        ACC_COLUMN_NAME = 'username',
        ACC_COLUMN_EMAIL = 'email',
        ACC_COLUMN_PASSWORD_HASH = 'sha_pass_hash',
        ACC_COLUMN_ROLE = 'gmlevel',
        ACC_COLUMN_LAST_IP = 'last_ip',
        ACC_COLUMN_LAST_LOGIN = 'last_login',
        ACC_COLUMN_MUTETIME = 'mutetime',
        ACC_COLUMN_LOCKED = 'locked',
        ACC_COLUMN_EXPANSION = 'expansion',
        ACC_COLUMN_V = 'v',
        ACC_COLUMN_S = 's',
        ACC_COLUMN_SESSIONKEY = 'sessionkey';

    const
        ACT_TABLE_NAME = 'account_keys',
        ACT_COLUMN_ID = 'id',
        ACT_COLUMN_KEY = 'key',
        ACT_COLUMN_TIME = 'assign_time';

    const
        PC_TABLE_NAME = 'account_passchange_keys',
        PC_COLUMN_ID = 'id',
        PC_COLUMN_KEY = 'key',
        PC_COLUMN_TIME = 'assign_time';

    const
        LOCK_TABLE_NAME = 'account_lock_keys',
        LOCK_COLUMN_ID = 'id',
        LOCK_COLUMN_KEY = 'key',
        LOCK_COLUMN_TIME = 'assign_time',
        LOCK_COLUMN_TYPE = 'type';

    const
        BAN_TABLE_NAME = 'account_banned',
        BAN_COLUMN_ID = 'id',
        BAN_COLUMN_BANDATE = 'bandate',
        BAN_COLUMN_UNBANDATE = 'unbandate',
        BAN_COLUMN_BANNEDBY = 'bannedby',
        BAN_COLUMN_BANREASON = 'banreason',
        BAN_COLUMN_ACTIVE = 'active';

    const
        IPBAN_TABLE_NAME = 'ip_banned',
        IPBAN_COLUMN_IP = 'ip',
        IPBAN_COLUMN_BANDATE = 'bandate',
        IPBAN_COLUMN_UNBANDATE = 'unbandate',
        IPBAN_COLUMN_BANNEDBY = 'bannedby',
        IPBAN_COLUMN_BANREASON = 'banreason';

    const
        PLVL_TABLE_NAME = 'account_access',
        PLVL_COLUMN_ID = 'id',
        PLVL_COLUMN_GMLVL = 'gmlevel',
        PLVL_COLUMN_REALMID = 'RealmID';

    const
        VOTEPOINTS_TABLE_NAME = 'account_votepoints',
        VOTEPOINTS_COLUMN_ACCOUNT = 'account_id',
        VOTEPOINTS_COLUMN_POINTS = 'points',
        VOTEPOINTS_COLUMN_LAST_CHANGED = 'last_changed';

    const
        DONATEPOINTS_TABLE_NAME = 'account_donatepoints',
        DONATEPOINTS_COLUMN_ACCOUNT = 'account_id',
        DONATEPOINTS_COLUMN_POINTS = 'points',
        DONATEPOINTS_COLUMN_LAST_CHANGED = 'last_changed';

    /** @var Nette\Database\Context */
    private $database;

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    public function __construct(Nette\Database\Context $database, \Kdyby\Translation\Translator $translator)
    {
        $this->database = $database;
        $this->translator = $translator;
    }

    /**
     * Get user by ID
     *
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneById($id)
    {
        return $this->database->table(self::ACC_TABLE_NAME)->get($id);
    }

    /**
     * Get user by Username or ID
     *
     * @param $username_id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByUsernameOrId($username_id)
    {
        return $this->database->table(self::ACC_TABLE_NAME)
            ->whereOr(array(
                self::ACC_COLUMN_ID => $username_id,
                self::ACC_COLUMN_NAME => Strings::upper($username_id)
            ))
            ->fetch();
    }

    /**
     * Get user by username
     *
     * @param $username
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByUsername($username)
    {
        return $this->database->table(self::ACC_TABLE_NAME)
            ->where(self::ACC_COLUMN_NAME, Strings::upper($username))
            ->fetch();
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneLockRequestById($id)
    {
        return $this->database->table(self::LOCK_TABLE_NAME)->get($id);
    }

    /**
     * @param $id
     * @param $key
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneLockRequestByIdAndKey($id, $key)
    {
        return $this->database->table(self::LOCK_TABLE_NAME)
            ->where(self::LOCK_COLUMN_ID, $id)
            ->where(self::LOCK_COLUMN_KEY, $key)
            ->fetch();
    }

    /**
     * @param $user_id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneUserVotePointsByUserId($user_id)
    {
        return $this->database->table(self::VOTEPOINTS_TABLE_NAME)
            ->where(self::VOTEPOINTS_COLUMN_ACCOUNT, $user_id)
            ->fetch();
    }

    /**
     * @param $user_id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneUserDonatePointsByUserId($user_id)
    {
        return $this->database->table(self::DONATEPOINTS_TABLE_NAME)
            ->where(self::DONATEPOINTS_COLUMN_ACCOUNT, $user_id)
            ->fetch();
    }

    /**
     * Check if user exists via username
     *
     * @param $username
     * @return bool
     */
    public function userExists($username)
    {
        $row = $this->database->table(self::ACC_TABLE_NAME)->where(self::ACC_COLUMN_NAME, Strings::upper($username))->fetch();

        if($row)
            return true;

        return false;
    }

    /**
     * Check if users exists via username & email
     *
     * @param $username
     * @param $email
     * @return bool
     */
    public function userAndEmailExists($username, $email)
    {
        $row = $this->database->table(self::ACC_TABLE_NAME)
            ->where(self::ACC_COLUMN_NAME, Strings::upper($username))
            ->where(self::ACC_COLUMN_EMAIL, Strings::lower($email))
            ->fetch();

        if($row)
            return true;

        return false;
    }

    /**
     * Check if username with password change key exists
     *
     * @param $username
     * @param $key
     * @return bool
     */
    public function userAndPCKeyExists($username, $key)
    {
        $user = $this->database->table(self::ACC_TABLE_NAME)
            ->where(self::ACC_COLUMN_NAME, Strings::upper($username))
            ->fetch();

        if(!$user)
            return false;

        $row = $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $user->id)
            ->where(self::PC_COLUMN_KEY, $key)
            ->fetch();

        if($row)
            return true;

        return false;
    }

    /**
     * Get gmlevel of account
     * First check gm level of the current realm id, if not present, check for global
     *
     * @param $id
     * @return int
     */
    public function getGmLevel($id)
    {
        $row = $this->database->table(self::PLVL_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $id)
            ->where(self::PLVL_COLUMN_REALMID, self::CURRENT_REALMID)
            ->fetch();

        if(!$row) {
            $row = $this->database->table(self::PLVL_TABLE_NAME)
                ->where(self::PC_COLUMN_ID, $id)
                ->where(self::PLVL_COLUMN_REALMID, -1)
                ->fetch();

            if(!$row)
                return 0;
        }

        return $row->{self::ACC_COLUMN_ROLE};
    }

    /**
     * @param $id
     * @return Nette\Database\Table\Selection
     */
    public function findAllBansForId($id)
    {
        return $this->database->table(self::BAN_TABLE_NAME)
            ->where(self::BAN_COLUMN_ID, $id)
            ->order(self::BAN_COLUMN_BANDATE . ' DESC');
    }

    /**
     * @param $email
     * @return Nette\Database\Table\Selection
     */
    public function findAllByEmail($email)
    {
        return $this->database->table(self::ACC_TABLE_NAME)
            ->where(self::ACC_COLUMN_EMAIL, $email)
            ->order(self::ACC_COLUMN_LAST_LOGIN . ' DESC');
    }

    /**
     * @param $ip
     * @return Nette\Database\Table\Selection
     */
    public function findAllByIp($ip)
    {
        return $this->database->table(self::ACC_TABLE_NAME)
            ->where(self::ACC_COLUMN_LAST_IP, $ip)
            ->order(self::ACC_COLUMN_LAST_LOGIN . ' DESC');
    }

    /**
     * Get user's ban info, if any
     *
     * @param $id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getBanInfo($id)
    {
        return $this->database->table(self::BAN_TABLE_NAME)
            ->where(self::BAN_COLUMN_ID, $id)
            ->where(self::BAN_COLUMN_ACTIVE, 1)
            ->fetch();
    }

    /**
     * Get IP ban info
     *
     * @param $ip
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getIPBanInfo($ip)
    {
        return $this->database->table(self::IPBAN_TABLE_NAME)
            ->where(self::IPBAN_COLUMN_IP, $ip)
            ->where(self::IPBAN_COLUMN_UNBANDATE . '>', time())
            ->fetch();
    }

    /**
     * Check if user is activated
     *
     * @param $id
     * @return bool
     */
    public function isActivated($id)
    {
        $row = $this->database->table(self::ACT_TABLE_NAME)
            ->where(self::ACT_COLUMN_ID, $id)
            ->fetch();

        if(!$row)
            return true;

        return false;
    }

    /**
     * Check if user is banned
     *
     * @param $id
     * @return bool
     */
    public function hasBan($id)
    {
        $row = $this->database->table(self::BAN_TABLE_NAME)
            ->where(self::BAN_COLUMN_ID, $id)
            ->where(self::BAN_COLUMN_ACTIVE, 1)
            ->fetch();

        if($row)
            return true;

        return false;
    }

    /**
     * Check if IP is banned
     *
     * @param $ip
     * @return bool
     */
    public function hasIPBan($ip)
    {
        $row = $this->database->table(self::IPBAN_TABLE_NAME)
            ->where(self::IPBAN_COLUMN_IP, $ip)
            ->where(self::IPBAN_COLUMN_UNBANDATE . ' >', time())
            ->fetch();

        if($row)
            return true;

        return false;
    }

    /**
     * Check if account has mute
     *
     * @param $id
     * @return bool
     */
    public function hasMute($id)
    {
        $user = $this->findOneById($id);

        return $user->{self::ACC_COLUMN_MUTETIME} > 0;
    }

    /**
     * Delete keys for lock/unlock for user
     *
     * @param $user
     */
    public function deleteLockUnlockKeysForUser($user)
    {
        $this->database->table(self::LOCK_TABLE_NAME)
            ->where(self::LOCK_COLUMN_ID, $user->id)
            ->delete();
    }

    /**
     * Delete keys for password change request for user ID
     *
     * @param $id
     */
    public function deletePCKeysForId($id)
    {
        $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $id)
            ->delete();
    }

    /**
     * Activate user's account, removes key after successful activation
     *
     * @param $username
     * @param $key
     * @return bool
     */
    public function activate($username, $key)
    {
        $user = $this->database->table(self::ACC_TABLE_NAME)->where(self::ACC_COLUMN_NAME, Strings::upper($username))->fetch();

        if(!$user)
            return false;

        $id = $user->id;

        $row = $this->database->table(self::ACT_TABLE_NAME)
            ->where(self::ACT_COLUMN_ID, $id)
            ->where(self::ACT_COLUMN_KEY, $key)
            ->fetch();

        if(!$row)
            return false;

        $user->update(array(
            self::ACC_COLUMN_LOCKED => self::STATUS_UNLOCKED
        ));

        $this->database->table(self::ACT_TABLE_NAME)
            ->where(self::ACT_COLUMN_ID, $id)
            ->where(self::ACT_COLUMN_KEY, $key)
            ->delete();

        return true;
    }

    /**
     * Deactivate user's account, returns key for a re-activation
     *
     * @param $id
     * @return bool|string
     */
    public function deactivate($id)
    {
        $user = $this->database->table(self::ACC_TABLE_NAME)->get($id);

        if(!$user)
            return false;

        $key = TrinityCore::createKey(array($id, $user->{UserManager::ACC_COLUMN_NAME}, $user->{UserManager::ACC_COLUMN_EMAIL}));

        $this->database->table(self::ACT_TABLE_NAME)->insert(array(
            self::ACT_COLUMN_ID => $user->{self::ACC_COLUMN_ID},
            self::ACT_COLUMN_KEY => $key,
            self::ACT_COLUMN_TIME => time()
        ));

        $user->update([
            self::ACC_COLUMN_LOCKED => self::STATUS_LOCKED,
            self::ACC_COLUMN_LAST_IP => '127.0.0.1'
        ]);

        return $key;
    }

    /**
     * Store new user & create an activation key which is returned
     *
     * @param $username
     * @param $password
     * @param $email
     * @return string
     */
    public function add($username, $password, $email)
    {
        $row = $this->database->table(self::ACC_TABLE_NAME)->insert(array(
            self::ACC_COLUMN_NAME => Strings::upper($username),
            self::ACC_COLUMN_PASSWORD_HASH => TrinityCore::createHash($username, $password),
            self::ACC_COLUMN_EMAIL => Strings::lower($email),
            self::ACC_COLUMN_LAST_IP => '127.0.0.1',
            self::ACC_COLUMN_LOCKED => self::STATUS_LOCKED,
            self::ACC_COLUMN_EXPANSION => self::EXPANSION_CATACLYSM
        ));

        $key = TrinityCore::createKey(array($username, $password, $email));

        $this->database->table(self::ACT_TABLE_NAME)->insert(array(
            self::ACT_COLUMN_ID => $row->{self::ACC_COLUMN_ID},
            self::ACT_COLUMN_KEY => $key,
            self::ACT_COLUMN_TIME => time()
        ));

        return $key;
    }

    /**
     * Create request for pass change, returns a confirmation key
     *
     * @param $username
     * @return string
     */
    public function addPasswordRequest($username)
    {
        $row = $this->database->table(self::ACC_TABLE_NAME)->where(self::ACC_COLUMN_NAME, Strings::upper($username))->fetch();
        $id = $row->{self::ACC_COLUMN_ID};

        // delete old keys
        $this->deletePCKeysForId($id);

        $key = TrinityCore::createKey(array($username, $id));

        $this->database->table(self::PC_TABLE_NAME)->insert(array(
            self::PC_COLUMN_ID => $id,
            self::PC_COLUMN_KEY => $key,
            self::PC_COLUMN_TIME => time()
        ));

        return $key;
    }

    /**
     * Change user's password using lost password action
     *
     * @param $username
     * @param $password
     * @param $key
     * @return bool
     */
    public function changePasswordByRecovery($username, $password, $key)
    {
        $user = $this->database->table(self::ACC_TABLE_NAME)->where(self::ACC_COLUMN_NAME, Strings::upper($username))->fetch();

        if(!$user)
            return false;

        $id = $user->id;

        $user->update(array(
            self::ACC_COLUMN_PASSWORD_HASH => TrinityCore::createHash($username, $password),
            self::ACC_COLUMN_V => '0',
            self::ACC_COLUMN_S => '0',
            self::ACC_COLUMN_SESSIONKEY => '0'
        ));

        $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $id)
            ->where(self::PC_COLUMN_KEY, $key)
            ->delete();

        return true;
    }

    /**
     * Create & store a key for account lock/unlock
     *
     * @param $user
     * @return string
     */
    public function addLockUnlockRequest($user)
    {
        // firsly delete keys for that user if there are any
        $this->deleteLockUnlockKeysForUser($user);

        $key = TrinityCore::createKey(array(
            $user->id, $user->identity->username, $user->identity->last_ip
        ));

        // get type
        $type = ($user->identity->locked == self::STATUS_LOCKED ? self::STATUS_UNLOCKED : self::STATUS_LOCKED);

        $this->database->table(self::LOCK_TABLE_NAME)->insert(array(
            self::LOCK_COLUMN_ID => $user->id,
            self::LOCK_COLUMN_KEY => $key,
            self::LOCK_COLUMN_TIME => time(),
            self::LOCK_COLUMN_TYPE => $type
        ));

        return $key;
    }

    /**
     * Change account lock status based on lock/unlock request
     *
     * @param $user
     * @param Nette\Database\Table\IRow $request
     * @return bool
     */
    public function lockUnlockUser($user, Nette\Database\Table\IRow $request)
    {
        // change account lock status
        $account = $this->database->table(self::ACC_TABLE_NAME)->get($user->id);

        if(!$account)
            return false;

        $account->update(array(
            self::ACC_COLUMN_LOCKED => $request->{self::LOCK_COLUMN_TYPE}
        ));

        // update stored identity
        $user->identity->locked = $request->{self::LOCK_COLUMN_TYPE};

        // delete keys after all
        $this->deleteLockUnlockKeysForUser($user);

        return true;
    }

    /**
     * @param $user
     * @param $points
     * @return bool
     */
    public function addVotePoints($user, $points)
    {
        $user_points = $this->findOneUserVotePointsByUserId($user->id);

        if(!$user_points) {
            $this->database->table(self::VOTEPOINTS_TABLE_NAME)->insert([
                self::VOTEPOINTS_COLUMN_ACCOUNT => $user->id,
                self::VOTEPOINTS_COLUMN_POINTS => $points,
                self::VOTEPOINTS_COLUMN_LAST_CHANGED => time()
            ]);
        } else {
            $user_points->update([
                self::VOTEPOINTS_COLUMN_POINTS => $user_points->{self::VOTEPOINTS_COLUMN_POINTS} + $points,
                self::VOTEPOINTS_COLUMN_LAST_CHANGED => time()
            ]);
        }

        return true;
    }

    /**
     * @param $user
     * @param $points
     * @return bool
     */
    public function addDonatePoints($user, $points)
    {
        $user_points = $this->findOneUserDonatePointsByUserId($user->id);

        if(!$user_points) {
            $this->database->table(self::DONATEPOINTS_TABLE_NAME)->insert([
                self::DONATEPOINTS_COLUMN_ACCOUNT => $user->id,
                self::DONATEPOINTS_COLUMN_POINTS => $points,
                self::DONATEPOINTS_COLUMN_LAST_CHANGED => time(),
            ]);
        } else {
            $user_points->update([
                self::DONATEPOINTS_COLUMN_POINTS => $user_points->{self::DONATEPOINTS_COLUMN_POINTS} + $points,
                self::DONATEPOINTS_COLUMN_LAST_CHANGED => time()
            ]);
        }

        return true;
    }
}