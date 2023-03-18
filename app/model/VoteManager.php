<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.08.2017
 * Time: 14:14
 */

namespace App\Model;

use Nette;

class VoteManager extends Nette\Object
{
    const
        STATE_NOTVOTED = 0,
        STATE_VOTING = 1,
        STATE_VOTED = 2;

    const
        VOTE_MULTIPLY_HOURS = 3600;

    const
        VOTES_TABLE_NAME = 'user_votes',
        VOTES_COLUMN_USER_ID = 'user_id',
        VOTES_COLUMN_SITE_ID = 'site_id',
        VOTES_COLUMN_STATE = 'state',
        VOTES_COLUMN_TIME = 'time',
        VOTES_COLUMN_IN_COUNT = 'in_count';

    const
        SITE_TABLE_NAME = 'vote_sites',
        SITE_COLUMN_ID = 'id',
        SITE_COLUMN_NAME = 'name',
        SITE_COLUMN_URL = 'url',
        SITE_COLUMN_CHECK_URL = 'check_url',
        SITE_COLUMN_IMAGE_PATH = 'image_path',
        SITE_COLUMN_IMAGE_WIDTH = 'image_width',
        SITE_COLUMN_IMAGE_HEIGHT = 'image_height',
        SITE_COLUMN_POINTS = 'points',
        SITE_COLUMN_COOLDOWN = 'cooldown',
        SITE_COLUMN_SCRIPT_ID = 'script_id';

    const
        ZERO_COUNT_FUNC = 'getZeroCount';

    const
        SCRIPT_TOP100ARENA = 1,
        SCRIPT_TOP100GAMING = 2,
        SCRIPT_TOP100GAMES = 3,
        SCRIPT_ARENATOP100 = 4,
        SCRIPT_TOPOFGAMES = 5,
        SCRIPT_MMTOP200 = 6,
        SCRIPT_XTREMETOP100 = 7;

    const
        COUNT_SCRIPTS_LIST = [
            self::SCRIPT_TOP100ARENA => 'getInCountForTop100Arena',
            self::SCRIPT_TOP100GAMING => 'getInCountForTop100Gaming',
            self::SCRIPT_TOP100GAMES => 'getInCountForTop100Games',
            self::SCRIPT_ARENATOP100 => 'getInCountForArenaTop100',
            self::SCRIPT_TOPOFGAMES => 'getInCountForTopOfGames',
            self::SCRIPT_MMTOP200 => self::ZERO_COUNT_FUNC,
            self::SCRIPT_XTREMETOP100 => self::ZERO_COUNT_FUNC,
        ];

    const
        VOTED_SCRIPTS_LIST = [
            self::SCRIPT_TOP100ARENA => 'hasVotedForTop100Arena',
            self::SCRIPT_TOP100GAMING => 'hasVotedForTop100Gaming',
            self::SCRIPT_TOP100GAMES => 'hasVotedForTop100Games',
            self::SCRIPT_ARENATOP100 => 'hasVotedForArenaTop100',
            self::SCRIPT_TOPOFGAMES => 'hasVotedForTopOfGames',
            self::SCRIPT_MMTOP200 => 'hasVotedForMmTop200',
            self::SCRIPT_XTREMETOP100 => 'hasVotedForXtremeTop100',
        ];

    /** @var Nette\Database\Context */
    private $database;

    /** @var Nette\Http\Request */
    private $httpRequest;

    /** @var UserManager */
    private $userManager;

    public function __construct(Nette\Database\Context $database, Nette\Http\Request $httpRequest, UserManager $userManager)
    {
        $this->database = $database;
        $this->httpRequest = $httpRequest;
        $this->userManager = $userManager;
    }

    /**
     * @param $user_id
     * @param $site_id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneUserVoteByUserIdAndSiteId($user_id, $site_id)
    {
        return $this->database->table(self::VOTES_TABLE_NAME)
            ->where(self::VOTES_COLUMN_USER_ID, $user_id)
            ->where(self::VOTES_COLUMN_SITE_ID, $site_id)
            ->fetch();
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneSiteById($id)
    {
        return $this->database->table(self::SITE_TABLE_NAME)->get($id);
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function findAllSites()
    {
        return $this->database->table(self::SITE_TABLE_NAME)
            ->order(self::SITE_COLUMN_POINTS . ' DESC')
            ->order(self::SITE_COLUMN_ID);
    }

    /**
     * @param $user
     * @param $site_id
     * @param $in_count
     * @return bool
     */
    public function startVote($user, $site_id, $in_count)
    {
        $user_vote = $this->findOneUserVoteByUserIdAndSiteId($user->id, $site_id);

        if(!$user_vote) {
            $this->database->table(self::VOTES_TABLE_NAME)->insert([
                self::VOTES_COLUMN_USER_ID => $user->id,
                self::VOTES_COLUMN_SITE_ID => $site_id,
                self::VOTES_COLUMN_STATE => self::STATE_VOTING,
                self::VOTES_COLUMN_TIME => time(),
                self::VOTES_COLUMN_IN_COUNT => $in_count
            ]);
        } else {
            $user_vote->update([
                self::VOTES_COLUMN_STATE => self::STATE_VOTING,
                self::VOTES_COLUMN_TIME => time(),
                self::VOTES_COLUMN_IN_COUNT => $in_count
            ]);
        }

        return true;
    }

    /**
     * @param $user
     * @param $site_id
     * @param $in_count
     * @param $points
     * @return bool
     */
    public function finishVote($user, $site_id, $in_count, $points)
    {
        $user_vote = $this->findOneUserVoteByUserIdAndSiteId($user->id, $site_id);

        if(!$user_vote) {
            // should not be evaulated, but "just in case"
            $this->database->table(self::VOTES_TABLE_NAME)->insert([
                self::VOTES_COLUMN_USER_ID => $user->id,
                self::VOTES_COLUMN_SITE_ID => $site_id,
                self::VOTES_COLUMN_STATE => self::STATE_VOTED,
                self::VOTES_COLUMN_TIME => time(),
                self::VOTES_COLUMN_IN_COUNT => $in_count
            ]);
        } else {
            $user_vote->update([
                self::VOTES_COLUMN_STATE => self::STATE_VOTED,
                self::VOTES_COLUMN_TIME => time(),
                self::VOTES_COLUMN_IN_COUNT => $in_count
            ]);
        }

        return $this->userManager->addVotePoints($user, $points);
    }

    /**
     * @param $name
     * @param $url
     * @param $check_url
     * @param $image_path
     * @param $image_width
     * @param $image_height
     * @param $points
     * @param $cooldown
     * @param $script_id
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function addVoteSite($name, $url, $check_url, $image_path, $image_width, $image_height, $points, $cooldown, $script_id)
    {
        return $this->database->table(self::SITE_TABLE_NAME)->insert([
            self::SITE_COLUMN_NAME => $name,
            self::SITE_COLUMN_URL => $url,
            self::SITE_COLUMN_CHECK_URL => $check_url,
            self::SITE_COLUMN_IMAGE_PATH => $image_path,
            self::SITE_COLUMN_IMAGE_WIDTH => $image_width,
            self::SITE_COLUMN_IMAGE_HEIGHT => $image_height,
            self::SITE_COLUMN_POINTS => $points,
            self::SITE_COLUMN_COOLDOWN => $cooldown,
            self::SITE_COLUMN_SCRIPT_ID => $script_id
        ]);
    }

    /**
     * @param $id
     * @param $name
     * @param $url
     * @param $check_url
     * @param $image_path
     * @param $image_width
     * @param $image_height
     * @param $points
     * @param $cooldown
     * @param $script_id
     * @return bool
     */
    public function updateVoteSite($id, $name, $url, $check_url, $image_path, $image_width, $image_height, $points, $cooldown, $script_id)
    {
        $site = $this->findOneSiteById($id);

        if(!$site)
            return false;

        $site->update([
            self::SITE_COLUMN_NAME => $name,
            self::SITE_COLUMN_URL => $url,
            self::SITE_COLUMN_CHECK_URL => $check_url,
            self::SITE_COLUMN_IMAGE_PATH => $image_path,
            self::SITE_COLUMN_IMAGE_WIDTH => $image_width,
            self::SITE_COLUMN_IMAGE_HEIGHT => $image_height,
            self::SITE_COLUMN_POINTS => $points,
            self::SITE_COLUMN_COOLDOWN => $cooldown,
            self::SITE_COLUMN_SCRIPT_ID => $script_id
        ]);

        return true;
    }

    /**
     * @param $site_id
     * @return bool|mixed
     */
    public function getInCount($site_id)
    {
        $site = $this->findOneSiteById($site_id);

        if(!$site)
            return false;

        if(!array_key_exists($site->{self::SITE_COLUMN_SCRIPT_ID}, self::COUNT_SCRIPTS_LIST))
            return false;

        return call_user_func(
            [
                $this,
                self::COUNT_SCRIPTS_LIST[$site->{self::SITE_COLUMN_SCRIPT_ID}]
            ],
            $site->{self::SITE_COLUMN_CHECK_URL});
    }

    /**
     * @param $site_id
     * @param $user_vote
     * @return bool|mixed
     */
    public function hasVoted($site_id, $user_vote)
    {
        $site = $this->findOneSiteById($site_id);

        if(!$site)
            return false;

        if(!array_key_exists($site->{self::SITE_COLUMN_SCRIPT_ID}, self::COUNT_SCRIPTS_LIST))
            return false;

        return call_user_func(
            [
                $this,
                self::VOTED_SCRIPTS_LIST[$site->{self::SITE_COLUMN_SCRIPT_ID}]
            ],
            $site->{self::SITE_COLUMN_CHECK_URL},
            $user_vote
            );
    }

    /**
     * @param $url
     * @return int
     */
    public function getInCountForTop100Arena($url)
    {
        $body = file_get_contents($url);

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($body);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);

        // find node containing number of votes
        $node = $xpath->query('/html/body/div/div[1]/div[1]/div[3]/div[2]/div[2]/div[2]/table/tr[2]/td[3]');

        // get its value
        $in_count = $node->item(0)->nodeValue;

        return (int)$in_count;
    }

    /**
     * @param $url
     * @return int
     */
    public function getInCountForTop100Gaming($url)
    {
        $body = file_get_contents($url);

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($body);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);

        // find node containing number of votes
        $node = $xpath->query('/html/body/table/tr[2]/td[2]/section/div[2]/div/div/div[2]/div[3]/div[2]/div[1]/div[1]/span');

        // get its value
        $in_count = $node->item(0)->nodeValue;

        return (int)$in_count;
    }

    /**
     * @param $url
     * @return int
     */
    public function getInCountForTop100Games($url)
    {
        $body = file_get_contents($url);

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($body);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);

        // find node containing number of votes
        $node = $xpath->query('/html/body/div[4]/div[5]/div/div[1]/div[2]/div[1]/div[1]/div[1]/div/div/div[3]/a');
        if($node->item(0) == null) // with banner version
            $node = $xpath->query('/html/body/div[4]/div[5]/div/div[1]/div[2]/div[1]/div[1]/div[2]/div[2]/div/div[3]/a');

        // get its value
        $in_count = $node->item(0)->nodeValue;

        return (int)$in_count;
    }

    /**
     * @param $url
     * @return int
     */
    public function getInCountForArenaTop100($url)
    {
        $body = file_get_contents($url);

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($body);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);

        // find node containing number of votes
        $node = $xpath->query('/html/body/div[2]/div/section/div/div/div/div[3]/div[2]/div[2]/table/tbody/tr[2]/td[2]');

        // get its value
        $in_count = $node->item(0)->nodeValue;

        return (int)$in_count;
    }

    /**
     * @param $url
     * @return int
     */
    public function getInCountForTopOfGames($url)
    {
        $body = file_get_contents($url);

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        $dom->loadHTML($body);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);

        // find node containing number of votes
        $node = $xpath->query('/html/body/center/center/div/table[2]/tr/td[4]/div/table/tr/td[1]/fieldset[3]/table[2]/tr[2]/td[2]');

        // get its value
        $in_count = $node->item(0)->nodeValue;

        return intval(preg_replace('/[^\d.]/', '', $in_count));
    }

    /**
     * For compatibility, if number of votes does not matter
     *
     * @param $url
     * @return int
     */
    public function getZeroCount($url)
    {
        return 0;
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForTop100Arena($url, $user_vote)
    {
        if(!$user_vote)
            return false;

        return $this->getInCountForTop100Arena($url) > $user_vote->{self::VOTES_COLUMN_IN_COUNT};
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForTop100Gaming($url, $user_vote)
    {
        if(!$user_vote)
            return false;

        // check if user vote's month is lower than current
        // if so, reset votes count
        if(date("m", $user_vote->{self::VOTES_COLUMN_TIME}) < date("m", time())
            || date("Y", $user_vote->{self::VOTES_COLUMN_TIME}) < date("Y", time())
        ) {
            $user_vote->update([
                self::VOTES_COLUMN_IN_COUNT => 0
            ]);
        }

        return $this->getInCountForTop100Gaming($url) > $user_vote->{self::VOTES_COLUMN_IN_COUNT};
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForTop100Games($url, $user_vote)
    {
        if(!$user_vote)
            return false;

        // check if user vote's month is lower than current
        // if so, reset votes count
        if(date("m", $user_vote->{self::VOTES_COLUMN_TIME}) < date("m", time())
            || date("Y", $user_vote->{self::VOTES_COLUMN_TIME}) < date("Y", time())
        ) {
            $user_vote->update([
                self::VOTES_COLUMN_IN_COUNT => 0
            ]);
        }

        return $this->getInCountForTop100Games($url) > $user_vote->{self::VOTES_COLUMN_IN_COUNT};
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForArenaTop100($url, $user_vote)
    {
        if(!$user_vote)
            return false;

        // check if user vote's month is lower than current
        // if so, reset votes count
        if(date("m", $user_vote->{self::VOTES_COLUMN_TIME}) < date("m", time())
            || date("Y", $user_vote->{self::VOTES_COLUMN_TIME}) < date("Y", time())
        ) {
            $user_vote->update([
                self::VOTES_COLUMN_IN_COUNT => 0
            ]);
        }

        return $this->getInCountForArenaTop100($url) > $user_vote->{self::VOTES_COLUMN_IN_COUNT};
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForTopOfGames($url, $user_vote)
    {
        if(!$user_vote)
            return false;

        return $this->getInCountForTopOfGames($url) > $user_vote->{self::VOTES_COLUMN_IN_COUNT};
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForMmTop200($url, $user_vote)
    {
        $url = str_replace('_USR_IP_', $this->httpRequest->getRemoteAddress(), $url);

        $body = file_get_contents($url);

        return (bool)$body;
    }

    /**
     * @param $url
     * @param $user_vote
     * @return bool
     */
    public function hasVotedForXtremeTop100($url, $user_vote)
    {
        return true;
    }
}