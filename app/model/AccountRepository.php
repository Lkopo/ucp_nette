<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 05.03.2017
 * Time: 17:12
 */

namespace App\Model;

use App\Utils\TrinityCore;
use Nette;

class AccountRepository extends Nette\Object
{
    // safemode user data, if auth is down
    const
        SAFEMODE_ADMIN_ID = -1,
        SAFEMODE_ADMIN_NAME = 'admin',
        SAFEMODE_ADMIN_PASS_HASH = '$2y$10$CwnzKcZlrml94fcuXqK7a.sfYoki4IPIC8CnqDEzVKASSxgvT5KRu';

    // enabled modules for safemode user
    const
        SAFEMODE_ENABLED_MODULES = [
            'Homepage' => [
              'default',
            ],
            'VoteSites' => [
                'default', 'add', 'edit'
            ],
            'DonateProducts' => [
                'default', 'add', 'edit'
            ],
            'ServiceSettings' => [
                'default'
            ],
            'ModuleSettings' => [
                'default'
            ],
            'PageSettings' => [
                'default'
            ]
        ];

    // default ROLE_PLAYER id
    const
        ROLE_PLAYER_ID = 1;

    // role levels
    const
        ROLE_PLAYER = 1,
        ROLE_EM_ZD = 2,
        ROLE_GM_ZD = 3,
        ROLE_EM = 4,
        ROLE_GM = 5,
        ROLE_DEV = 6,
        ROLE_HEM = 7,
        ROLE_HGM = 8,
        ROLE_ADMIN = 9,
        ROLE_WEBMASTER = 10;

    const
        PC_TABLE_NAME = 'pc_request_keys',
        PC_COLUMN_ID = 'id',
        PC_COLUMN_KEY = 'key',
        PC_COLUMN_TIME = 'assign_time';

    const
        ACL_TABLE_NAME = 'role',
        ACL_COLUMN_ID = 'id',
        ACL_COLUMN_NAME = 'name',
        ACL_COLUMN_LEVEL = 'level';

    const
        PRIV_TABLE_NAME = 'role_privilege',
        PRIV_COLUMN_ROLE = 'role_id',
        PRIV_COLUMN_RESOURCE = 'resource',
        PRIV_COLUMN_PRIVILEGE = 'privilege';

    const
        UR_TABLE_NAME = 'user_role',
        UR_COLUMN_USER = 'user_id',
        UR_COLUMN_ROLE = 'role_id';

    /** @var Nette\Database\Context */
    private $database;

    /** @var UserManager */
    private $userManager;

    public function __construct(Nette\Database\Context $database, UserManager $userManager)
    {
        $this->database = $database;
        $this->userManager = $userManager;
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOnePCRequestById($id)
    {
        return $this->database->table(self::PC_TABLE_NAME)->get($id);
    }

    /**
     * @param $id
     * @param $key
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOnePCRequestByIdAndKey($id, $key)
    {
        return $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $id)
            ->where(self::PC_COLUMN_KEY, $key)
            ->fetch();
    }

    /**
     * Find all roles associated to the user's id
     *
     * @param $id
     * @return \Nette\Database\Table\Selection
     */
    public function findAllUserRolesById($id)
    {
        return $this->database->table(self::UR_TABLE_NAME)
            ->where(self::UR_COLUMN_USER, $id)
            ->order(self::ACL_TABLE_NAME . '.' . self::ACL_COLUMN_LEVEL . ' DESC');
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function findAllPrivilegedUsers()
    {
        return $this->database->table(self::UR_TABLE_NAME)
            ->group(self::UR_COLUMN_USER)
            ->order(self::UR_COLUMN_ROLE . ' DESC');
    }

    /**
     * @param $user_id
     * @return \Nette\Database\Table\Selection
     */
    public function findAllRolesForUser($user_id)
    {
        return $this->database->table(self::UR_TABLE_NAME)
            ->select(self::UR_COLUMN_ROLE)
            ->where(self::UR_COLUMN_USER, $user_id)
            ->order(self::ACL_TABLE_NAME . '.' . self::ACL_COLUMN_LEVEL . ' DESC');
    }

    /**
     * @param $level
     * @return \Nette\Database\Table\Selection
     */
    public function findAllRolesLowerByLevel($level)
    {
        return $this->database->table(self::ACL_TABLE_NAME)
            ->where(self::ACL_COLUMN_LEVEL . ' <', $level)
            ->order(self::ACL_COLUMN_LEVEL . ' DESC');
    }

    /**
     * @param $level
     * @return array|null
     */
    public function findAllRolesLowerByLevelForSelection($level)
    {
        $rows = $this->database->table(self::ACL_TABLE_NAME)
            ->where(self::ACL_COLUMN_LEVEL . ' >', self::ROLE_PLAYER)
            ->where(self::ACL_COLUMN_LEVEL . ' <', $level)
            ->order(self::ACL_COLUMN_LEVEL . ' ASC')
            ->fetchPairs(self::ACL_COLUMN_ID, self::ACL_COLUMN_NAME);

        return ($rows ? $rows : NULL);
    }

    /**
     * @param $role
     * @return \Nette\Database\Table\Selection
     */
    public function findAllPrivilegesByRole($role)
    {
        return $this->database->table(self::PRIV_TABLE_NAME)
            ->where(self::PRIV_COLUMN_ROLE, $role);
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneRoleById($id)
    {
        return $this->database->table(self::ACL_TABLE_NAME)->get($id);
    }

    /**
     * @param $user_id
     * @return bool|int|mixed|Nette\Database\Table\IRow
     */
    public function getHighestRoleForUser($user_id)
    {
        $special_roles = $this->findAllRolesForUser($user_id);
        if($special_roles->count() == 0)
            return self::ROLE_PLAYER;

        return $special_roles->fetch()->{self::UR_COLUMN_ROLE};
    }

    /**
     * Create & store a key for password change request
     *
     * @param $user
     * @return string
     */
    public function addPCRequest($user)
    {
        // firsly delete keys for that user if there are any
        $this->deletePCKeysForUser($user);

        $key = TrinityCore::createKey(array(
            $user->id, $user->identity->username, $user->identity->last_ip
        ));

        $this->database->table(self::PC_TABLE_NAME)->insert(array(
            self::PC_COLUMN_ID => $user->id,
            self::PC_COLUMN_KEY => $key,
            self::PC_COLUMN_TIME => time()
        ));

        return $key;
    }

    /**
     * Change user's password via User settings menu
     *
     * @param $id
     * @param $password
     * @param $key
     * @return bool
     */
    public function changePassword($id, $password, $key)
    {
        $user = $this->userManager->findOneById($id);

        if(!$user)
            return false;

        $user->update(array(
            UserManager::ACC_COLUMN_PASSWORD_HASH => TrinityCore::createHash($user->username, $password),
            UserManager::ACC_COLUMN_V => '0',
            UserManager::ACC_COLUMN_S => '0',
            UserManager::ACC_COLUMN_SESSIONKEY => '0'
        ));

        // remove key
        $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $id)
            ->where(self::PC_COLUMN_KEY, $key)
            ->delete();

        return true;
    }

    /**
     * Delete all keys for selected user
     *
     * @param $user
     */
    public function deletePCKeysForUser($user)
    {
        $this->database->table(self::PC_TABLE_NAME)
            ->where(self::PC_COLUMN_ID, $user->id)
            ->delete();
    }

    /**
     * @param $user
     * @param $role_id
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function addRoleToUser($user, $role_id)
    {
        return $this->database->table(self::UR_TABLE_NAME)->insert(array(
            self::UR_COLUMN_USER => $user->id,
            self::UR_COLUMN_ROLE => $role_id
        ));
    }

    /**
     * @param $user_id
     * @param $role_id
     * @return mixed
     */
    public function changeRoleOfUser($user_id, $role_id)
    {
        $user_role = $this->findAllUserRolesById($user_id)->fetch();

        $user_role->update([
            self::UR_COLUMN_ROLE => $role_id
        ]);

        return true;
    }
}