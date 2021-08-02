<?php declare (strict_types = 1);

namespace GuildCP;

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/db.class.php";
require_once __DIR__ . "/blizzard/wowcharacter.class.php";
require_once __DIR__ . "/blizzard/guildpermission.class.php";


use GuildCP\Blizzard\WowCharacter;
use GuildCP\Blizzard\GuildPermission;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use GuildCP\Blizzard\Token;
use GuildCP\Blizzard\Guild;
/**
 * Represents a WOW Player (Wow account)
 */
class Player
{
    protected $id = null;

    private $username;
    private $email;
    private $registerIp;
    private $lastIp;
    private $joinDate;
    private $lastLoginDate;

    // WoW associations
    private $battletag;
    private $subId;
    private $lastSyncDate;
    private $region;
    private $wowCharacters;
    private $guildPermissions;

    // User settings
    private $visible;
    private $showEmail;
    private $showBattletag;
    private $manageGuild;

    /**
     * Construct a Player object using the user ID
     * @param int $user_id The user ID of the user
     * @return void
     */
    public function __construct($user_id)
    {
        $user_id = (int) $user_id;

        // TODO: Extend upon this when we sort out Oauth2 and Blizzard API access
        $get_player_q = "
        SELECT 
            a.`id`,
            a.`username`,
            a.`email`,
            a.`battletag`,
            a.`sub_id`,
            a.`region`,
            INET_NTOA(a.`register_ip`) AS `register_ip`,
            INET_NTOA(a.`last_ip`) AS `last_ip`,
            a.`joined_date`,
            a.`last_login_date`,
            a.`last_sync_date`,
			acs.`visible`,
			acs.`show_email`,
            acs.`show_battletag`,
            acs.`manage_guild`
            FROM 
                `accounts` a
			LEFT JOIN `accounts_settings` acs 
			ON (acs.`user_id` = a.`id`)
            WHERE a.`id` = :id
        ";

        $stmt = Db::getPdo()->prepare($get_player_q);
        $stmt->execute([":id" => $user_id]);

        if ($stmt->rowCount()) {
            $player = $stmt->fetchObject();
            $this->id = $user_id;

            $this->username = htmlspecialchars($player->username, ENT_QUOTES, 'UTF-8');
            $this->email = $player->email;
            $this->registerIp = $player->register_ip;
            $this->lastIp = $player->last_ip;
            $this->joinDate = $player->joined_date;
            $this->lastLoginDate = $player->last_login_date;

            $this->region = $player->region;
            $this->lastSyncDate = $player->last_sync_date;
            $this->battletag = $player->battletag;
            $this->subId = $player->sub_id;

            $this->visible = $player->visible;
            $this->showEmail = $player->show_email;
            $this->showBattletag = $player->show_battletag;
            $this->manageGuild = $player->manage_guild;

            $this->wowCharacters = WowCharacter::getCharacters($this->id);

            if (count($this->wowCharacters)) {
                $this->loadGuildPermissions();
            }
        }
    }

    // Getters

    /**
     * Returns whether or not the object has been instantiated
     * @return boolean True if the object has been instantiated, false if not
     */
    public function isDefined()
    {
        return $this->id !== null;
    }

    /**
     * Returns the player ID of the user
     * @return int Player id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the username of the user
     * @return string The username of the user
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the email of the user
     * @return string The email of the user
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the register IP of the user
     * @return int Numerical representation of the register IP
     */
    public function getRegisterIp()
    {
        return $this->registerIp;
    }

    /**
     * Returns the last IP of the user
     * @return int Numerical representation of the last  IP
     */
    public function getLastIp()
    {
        return $this->lastIp;
    }

    /**
     * Returns the join date of the user
     * @return date The join date of the user
     */
    public function getJoinDate()
    {
        return $this->joinDate;
    }

    /**
     * Returns the last login datetime of the user
     * @return datetime The last login datetime of the user
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate;
    }

    /**
     * Returns the last synchronization datetime of the user
     * @return datetime The last synchronization datetime of the user
     */
    public function getLastSyncDate()
    {
        return $this->lastSyncDate;
    }

    /**
     * Returns the battletag of the user
     * @return string The battle tag of the user
     */
    public function getBattleTag()
    {
        return $this->battletag;
    }

    /**
     * Returns the WOW subscription ID of the player
     * @return int subId
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * Set the WOW subscription ID of a player
     * @param int $subId
     */
    public function setSubId($subId)
    {
        $this->subId = $subId;
    }

    /**
     * Set the battle tag of a player
     * @param string $battletag
     */
    public function setBattleTag($battletag)
    {
        $this->battletag = $battletag;
    }

    public function setEmail(string $email)
    {
        $this->ensureIsValidEmail($email);

        $this->email = $email;
    }

    /**
     * Get the character count of the user
     * @return int The amount of characters saved in the database
     */
    public function getCharacterCount()
    {
        return (count($this->wowCharacters));
    }

    /**
     * Returns the WOW characters of the user
     * @return Array Containing WoWCharacter objects
     */
    public function getCharacters()
    {
        return $this->wowCharacters;
    }

    /**
     * Returns whether or not the player has any WoW characters associated to the account
     * @return Boolean True if the player has any wow characters associated, false if not
     */
    public function hasCharacters()
    {
        return (!empty($this->wowCharacters));
    }

    /**
     * Set the region of a player
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
        if ($region !== null) {
            $stmt = Db::getPdo()->prepare("UPDATE `accounts` SET `region` = :region WHERE `id` = :id");
            $stmt->execute([
                ":region" => $this->region,
                ":id"     => $this->id
            ]);
        } else {
            $stmt = Db::getPdo()->prepare("UPDATE `accounts` SET `region` = NULL WHERE `id` = :id");
            $stmt->execute(["id" => $this->id]);
        }
    }

    /**
     * Returns the region of a player
     * @return string|null $region
     */
    public function getRegion()
    {
        $stmt = Db::getPdo()->prepare("SELECT `region` FROM `accounts` WHERE `id` = :id");
        $stmt->execute(["id" => $this->id]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();
            if ($result->region == null || strlen($result->region) < 2) {
                $this->region = null;
                return null;
            } else {
                $this->region = $result->region;
                return $result->region;
            }
        } else {
            $this->region = null;
            return null;
        }
    }

    /**
     * Save a players wow characters to DB, using an associtiave array retrieved from Blizzard API
     * @param Array $array Json decoded array
     * @return int Count of saved characters, 0 if none
     */
    public function saveWowCharacters($array)
    {
        $wowCharacter = new WowCharacter();
        $count = 0;
        foreach ($array as $key => $value) {
            foreach ($value as $key1 => $value1) {
                foreach ($value1 as $key2 => $value2) {
                    if (!is_array($value2)) {
                        $wowCharacter->setValue($key2, $value2);
                    } else {
                        foreach ($value2 as $key3 => $value3) {
                            if (!is_array($value3)) {
                                $wowCharacter->setValue("_" . $key3, $value3);
                            }
                        }
                    }
                }
                if ($wowCharacter->isSaveable()) {
                    $this->wowCharacters[] = $wowCharacter;
                    $wowCharacter->save($this->id);
                    $count++;
                }
                $wowCharacter = new WowCharacter();
            }
        }
        return $count;
    }

    /**
     * Saves changeable variables to the database, only call this if you have set the variables
     * @return this
     */
    public function save()
    {
        $stmt = Db::getPdo()->prepare("UPDATE `accounts` SET `battletag` = :battletag, `sub_id` = :sub_id, `last_sync_date` = NOW() WHERE `id` = :id");

        $stmt->execute([
            ":battletag" => $this->battletag,
            ":sub_id"    => $this->subId,
            ":id"        => $this->id
        ]);
        return $this;
    }

    /**
     * Checks whether or not the player has associated the account with his battle.net account
     * @return boolean True if the user has, false if not
     */
    public function hasBattleNet()
    {
        return ($this->subId !== null) ? (true) : (false);
    }

    /**
     * Currently we have no setting for this in the database, so it'll temporarily return development mode
     * @return boolean Whether or not the user is an admin (always true in development mode temporarily)
     */
    public function isAdmin()
    {
        return Config::get("site.development");
    }

    /**
     * Gets the access token of a user if there is one
     * @return null|Token The access token
     */
    public function getAccessToken()
    {
        $stmt = Db::getPdo()->prepare("SELECT `access_token`, TIMESTAMPDIFF(SECOND, `created_at`, NOW()) AS `expires_in` FROM `accounts_tokens` WHERE `user_id` = :user_id AND `created_at` <= (NOW() + INTERVAL 1 DAY)");

        $stmt->execute([":user_id" => $this->id]);
        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();

            $token = new Token($result->access_token, 'bearer', $result->expires_in);
            if ($result->expires_in >= 86300 || $token->isExpired()) {
                $stmt = Db::getPdo()->prepare("DELETE FROM `accounts_tokens` WHERE `user_id` = :user_id");
                $stmt->execute([":user_id" => $this->id]);

                return null;
            } else {
                return $token;
            }
        } else {
            return null;
        }
    }

    /**
     * Set the access token of a user (they last for 24 hours)
     * @param string $access_token The access token
     * @param int $expires_in The amount of seconds before it expires
     */
    public function setAccessToken($access_token)
    {
        $stmt = Db::getPdo()->prepare(
            "INSERT INTO `accounts_tokens`(`user_id`, `access_token`, `created_at`)
            VALUES (:user_id, :access_token, NOW())
            ON DUPLICATE KEY UPDATE `access_token` = :access_token1, `created_at` = NOW()"
        );

        $stmt->execute([
            ":user_id"          => $this->id,
            ":access_token"     => $access_token,
            ":access_token1"    => $access_token,
        ]);
    }

    /**
     * Loads the guild permissions of a user, see class GuildPermission
     */
    private function loadGuildPermissions()
    {
        
        $stmt = Db::getPdo()->prepare("SELECT `guild_id`, `permissions` FROM `guilds_permissions` WHERE `user_id` = :user_id");
        $stmt->execute([":user_id" => $this->id]);
        
        if ($stmt->rowCount()) {
            $this->guildPermissions = array();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $guildPermission = new GuildPermission(Guild::byId($row["guild_id"]), $this, $row["permissions"]);
                $this->guildPermissions[] = $guildPermission;
            }
        }
    }

    /**
     * Returns the amount of guild permission objects the user has (guild memberships)
     * @return int The count of guild permission relationships
     */
    public function getGuildPermissionsCount()
    {
        return count($this->guildPermissions);
    }

    /**
     * Returns the users permissions in a guild, or null if no permissions are found
     * @return int|null Integer representing permissions, see GuildPermission class
     */
    public function getGuildPermissions(Guild $guild)
    {
        $permissions = null;
        
        if (!$this->getGuildPermissionsCount()) {
            return $permissions;
        }
        
        foreach ($this->guildPermissions as $guildPermission) {
            if ($guildPermission->getGuild()->__toString() === $guild->__toString()) {
                $permissions = $guildPermission->getPermissions();
                break;
            }
        }

        return $permissions;
    }

    /**
     * Returns the guild permissions array
     * @return array Consisting of GuildPermission classes
     */
    public function getGuildPermissionsArray()
    {
        return $this->guildPermissions;
    }

    /**
     * Gets the manage guild ID
     * @return null|id The id of the guild the user is managing, null if none
     */
    public function getManageGuildId()
    {
        return $this->manageGuild;
    }

    /**
     * Returns whether or not the player is managing a guild
     * @return boolean True if yes, false if not
     */
    public function isManagingGuild()
    {
        return ($this->manageGuild !== null);
    }

    /**
     * Returns the permissions of the guild the player is managing
     * @return int|null The guild permissions
     */
    public function getManageGuildPermissions()
    {
        $permissions = null;

        if (!$this->isManagingGuild()) {
            return $permissions;
        }

        foreach ($this->guildPermissions as $guildPermission) {
            if ($guildPermission->getGuild()->getId() == $this->manageGuild) {
                $permissions = $guildPermission->getPermissions();
                break;
            }
        }

        return $permissions;
    }

    /**
     * Set the manage guild ID of the user
     * @param int|null $guildId The guild ID of the guild, or null/no parameters for null
     */
    public function setManageGuildId($guildId = null)
    {
        if ($guildId === null) {
            $stmt = Db::getPdo()->prepare("UPDATE `accounts_settings` SET `manage_guild` = NULL WHERE `user_id` = :user_id");

            $stmt->execute([":user_id" => $this->id]);
        } else {
            $stmt = Db::getPdo()->prepare("UPDATE `accounts_settings` SET `manage_guild` = :manage_guild WHERE `user_id` = :user_id");

            $stmt->execute([
                ":manage_guild" => $guildId,
                ":user_id" => $this->id
            ]);
        }
    }

    /**
     * Returns the visiblity of the user
     * @return boolean True if visible, false if not
     */
    public function getVisibility()
    {
        return $this->visible;
    }

    /**
     * Sets the visibility of the user
     * @param int 1 if visible to others, 0 if not
     */
    public function setVisibility($visible)
    {
        $this->visible = $visible;

        $stmt = Db::getPdo()->prepare("UPDATE `accounts_settings` SET `visible` = :visible WHERE `user_id` = :user_id");
        
        $stmt->execute([
            ":visible" => $this->visible,
            ":user_id" => $this->id
        ]);
    }

    /**
     * Returns whether or not the player's email is visible on the profile page
     * @param boolean True if yes, false if not
     */
    public function getShowEmail()
    {
        return $this->showEmail;
    }

    /**
     * Sets the visibility of the user's email to others
     * @param int 1 if the email should be visible to others, 0 if not
     */
    public function setShowEmail($showEmail)
    {
        $this->showEmail = $showEmail;

        $stmt = Db::getPdo()->prepare("UPDATE `accounts_settings` SET `show_email` = :show_email WHERE `user_id` = :user_id");

        $stmt->execute([
            ":show_email" => $this->showEmail,
            ":user_id"    => $this->id
        ]);
    }

    /**
     * Returns whether or not the user's battletag is visible to others
     * @param boolean True if yes, false if not
     */
    public function getShowBattleTag()
    {
        return $this->showBattletag;
    }

    /**
     * Sets the visibility of the users battletag to others
     * @param int 1 if the battletag should be visible to others, 0 if not
     */
    public function setShowBattleTag($showBattletag)
    {
        $this->showBattletag = $showBattletag;

        $stmt = Db::getPdo()->prepare("UPDATE `accounts_settings` SET `show_battletag` = :show_battletag WHERE `user_id` = :user_id");

        $stmt->execute([
            ":show_battletag" => $this->showBattletag,
            ":user_id"        => $this->id
        ]);
    }

    /** Unit testing */

    /**
     * Ensure is valid email
     * @param string E-mail to ensure is valid
     * @throws InvalidArgumentException
     */
    private static function ensureIsValidEmail(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf(
                    "'%s' is not a valid e-mail address.",
                    $email
                )
            );
        }
    }


    /**
     * Return a new User object
     * @param int $id The user ID of the user
     * @return self New user object
     */
    public static function fromId(int $id): self
    {
        return new self($id);
    }

    /**
     * Return a new User object
     * @param string $email The email of the user
     * @return self New user object
     */
    public static function fromEmail(string $email): self
    {
        self::ensureIsValidEmail($email);
        
        $stmt = Db::getPdo()->prepare("SELECT `id` FROM `accounts` WHERE `email` = :email");
        $stmt->execute([":email" => $email]);

        $result = $stmt->fetchObject();

        return new self($result->id);
    }

    /**
     * Return a new User object
     * @param string $name The name of the user
     * @return self|null New user object or null if doesn't exist
     */
    public static function fromName(string $name)
    {
        $stmt = Db::getPdo()->prepare("SELECT `id` FROM `accounts` WHERE `username` = :name");
        $stmt->execute([":name" => $name]);

        $result = $stmt->fetchObject();
        if ($stmt->rowCount()) {
            return new self($result->id);
        } else {
            return null;
        }
    }
}
