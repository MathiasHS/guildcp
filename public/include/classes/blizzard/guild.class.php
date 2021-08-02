<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/../db.class.php";
require_once __DIR__ . "/../player.class.php";
require_once __DIR__ . "/../config.class.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/client.class.php";
require_once __DIR__ . "/wowcommunity.class.php";
require_once __DIR__ . "/wowcharacter.class.php";
require_once __DIR__ . "/guildmember.class.php";

use \GuildCp\Db;
use \GuildCp\Player;

/**
 * Class for representing WoW guilds
 */
class Guild
{

    private $dbId;

    private $region;
    private $realm;
    private $guildName;
    private $lastModified;
    private $level;
    private $side;
    private $achievementPoints;

    // Emblem
    private $icon;
    private $iconColor;
    private $iconColorId;
    private $border;
    private $borderColor;
    private $borderColorId;
    private $backgroundColor;
    private $backgroundColorId;

    private $guildMembers;
    private $guildMaster;

    private $ownerId;

    private $response;
    private $isRegistered;

    // Guild settings
    private $visibility;
    private $canApply;
    private $information;

    /**
     * Initialize a new guild
     * @param Client $client The client to use
     * @param string $realm The realm of the guild
     * @param string $guildName The name of the guild
     * @param string $region The region of the guild (nothing if using Client, only for DB loading only)
     */
    public function __construct($client, $realm, $guildName, $region = "")
    {
        $this->client = $client;
        $this->realm = $realm;
        $this->guildName = $guildName;
        $this->region = strlen($region) ? $region : $client->getRegion();

        $stmt = Db::getPdo()->prepare(
            "SELECT g.`id`, g.`level`, g.`faction`, g.`achievement_points`, g.`icon`, g.`icon_color`, g.`border`,
             g.`border_color`, g.`background_color`, g.`owner_id`, g.`last_modified`, 
             gs.`visibility`, gs.`can_apply`, gs.`information`
             FROM `guilds` g 
             LEFT JOIN `guilds_settings` gs ON (gs.`guild_id` = g.`id`) 
             WHERE g.`region` = :region AND g.`name` = :name AND g.`realm` = :realm
        ");

        $stmt->execute([
            ":region" => $this->region,
            ":name"   => $guildName,
            ":realm"  => $realm
        ]);

        // If registered in DB, load DB variables
        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();

            $this->isRegistered         = true;
            $this->dbId                 = $result->id;
            $this->side                 = $result->faction;
            $this->achievementPoints    = $result->achievement_points;
            $this->icon                 = $result->icon;
            $this->iconColor            = $result->icon_color;
            $this->border               = $result->border;
            $this->borderColor          = $result->border_color;
            $this->backgroundColor      = $result->background_color;
            $this->ownerId              = $result->owner_id;
            $this->lastModified         = $result->last_modified;

            $this->visibility           = $result->visibility;
            $this->canApply             = $result->can_apply;
            $this->information          = $result->information;
        }

        if ($this->client !== null) {
            $this->loadVariablesFromJson();
        }
    }

    private function loadVariablesFromJson()
    {
        $wowService = new WoWCommunity($this->client);

        $response = $wowService->getGuildProfile($this->realm, $this->guildName, "members");
        $this->guildMembers = array();

        if ($response->getStatusCode() == 200) {
            $this->response = $response;
            $this->region = $this->client->getRegion();

            $body = json_decode($response->getBody(), true);
            $wowCharacter = new WowCharacter();
            foreach ($body as $key => $value) {
                if (!is_array($value)) {
                    $this->setValue($key, $value);
                } else {
                    foreach ($value as $key1 => $value1) {
                        // Emblem
                        if (!is_array($value1)) {
                            $this->setValue($key1, $value1);
                        } else {
                            foreach ($value1 as $key2 => $value2) {
                                // Members, first rank and then character information
                                if (!is_array($value2)) {
                                    if ($key2 == "rank" && $value2 == 0) {
                                        $this->guildMaster = new GuildMember($wowCharacter, $this, 0);
                                        $this->guildMembers[] = $this->guildMaster;
                                    } else {
                                        continue;
                                    }
                                } else {
                                    foreach ($value2 as $key3 => $value3) {
                                        if (!is_array($value3)) {
                                            $wowCharacter->setValue($key3, $value3);
                                        } else {
                                            foreach ($value3 as $key4 => $value4) {
                                                $wowCharacter->setValue("_" . $key4, $value4);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $this->region = null;
            $this->realm = null;
            $this->guildName = null;

            $this->response = $response;
        }
    }

    /**
     * Sets the private variables
     * @param string $key The key to set (from JSON)
     * @param string|int $value The value to set (from JSON)
     */
    private function setValue($key, $value)
    {
        switch ($key) {
            case 'lastModified':
                $this->lastModified = $value;
                break;
            case 'level':
                $this->level = $value;
                break;
            case 'side':
                $this->side = $value;
                break;
            case 'achievementPoints':
                $this->achievementPoints = $value;
                break;
            case 'icon':
                $this->icon = $value;
                break;
            case 'iconColor':
                $this->iconColor = $value;
                break;
            case 'iconColorId':
                $this->iconColorId = $value;
                break;
            case 'border':
                $this->border = $value;
                break;
            case 'borderColor':
                $this->borderColor = $value;
                break;
            case 'borderColorId':
                $this->borderColorId = $value;
                break;
            case 'backgroundColor':
                $this->backgroundColor = $value;
                break;
            case 'backgroundColorId':
                $this->backgroundColorId = $value;
                break;
            default:
                break;
        }
    }

    /**
     * Returns a string representation of the object
     * @return string String representation of this object
     */
    public function __toString()
    {
        return $this->guildName . ";" . $this->realm;
    }

    /**
     * Get the path to access the guild in /guild/GUILD (g.php?data=)
     * @return string The guild's path for g.php
     */
    public function getGuildPath()
    {
        return "{$this->region};{$this->realm};{$this->guildName}";
    }

    /**
     * Checks whether or not the user has a character that is the owner of the guild
     * @param Player The user to check
     * @return boolean True if the player does, false if not
     */
    public function hasGuildMasterCharacter($user)
    {
        $ret = false;
        $gmChar = $this->guildMaster->getCharacter();
        foreach ($user->getCharacters() as $character) {
            if ($character->getName() == $gmChar->getName() && $character->getRealm() == $gmChar->getRealm()) {
                $ret = true;
                break;
            }
        }

        return $ret;
    }

    /**
     * Returns the WoWCharacter object of the GuildMaster
     * @return WowCharacter The guild master of the guild
     */
    public function getGuildMasterCharacter()
    {
        return $this->guildMaster->getCharacter();
    }

    /**
     * Checks whether or not the guild is registered in the database
     * @return boolean True if the guild has been registered, false if not
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * Returns the DB id of the guild
     * @return int The database ID of the guild
     */
    public function getId()
    {
        return $this->dbId;
    }

    /**
     * Returns the name of the guild
     * @return string $guildName The name of the guild
     */
    public function getName()
    {
        return $this->guildName;
    }

    /**
     * Returns the realm of the guild
     * @return string $realm The realm of the guild
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Returns the region of the guild
     * @return string $region The region of the guild
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Get the icon ID of the emblem
     * @return int emblem ID
     */
    public function getEmblemIcon()
    {
        return $this->icon;
    }

    /**
     * Get the color of the emblem
     * @return string $color The color of the emblem (HEX)
     */
    public function getEmblemColor()
    {
        return $this->iconColor;
    }

    /**
     * Get the emblem border (0 if none)
     * @return int Emblem border
     */
    public function getEmblemBorder()
    {
        return $this->border;
    }

    /**
     * Get the color of the emblem border
     * @return string Emblem border color (HEX)
     */
    public function getEmblemBorderColor()
    {
        return $this->borderColor;
    }

    /**
     * Get the background color of the emblem
     * @return string Emblem background color (HEX)
     */
    public function getEmblemBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Returns the faction of the guild
     * @return int 0 if alliance, 1 if horde
     */
    public function getFaction()
    {
        return $this->side;
    }

    /**
     * Returns the guilds level
     * @return int guild level
     */
    public function getGuildLevel()
    {
        return $this->level;
    }

    /**
     * Returns the amount of achievement points the guild has
     * @return int achievement points
     */
    public function getAchievement()
    {
        return $this->achievementPoints;
    }

    /**
     * Returns the amount of members that have a relationship to the guild on GCP
     * @return int The amount of members registered for the guild in the database
     */
    public function getMemberCount()
    {
        $stmt = Db::getPdo()->prepare("SELECT COUNT(`user_id`) AS `count` FROM `guilds_permissions` WHERE `guild_id` = :guild_id");
        $stmt->execute([":guild_id" => $this->dbId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();

            return $result->count;
        } else {
            return 0;
        }
    }

    /**
     * Returns information about the guild
     * @return string Information from the guild settings
     */
    public function getInformation()
    {
        return (strlen($this->information)) ? (htmlentities($this->information)) : ("{$this->guildName} hasn't written any information yet.");
    }

    /**
     * Returns the visibility of the guild
     * @return int -1 if hidden to all except moderators, 0 open to members, 1 open to everyone
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set the visibility of the guild
     * @param int -1 if hidden to all except moderators, 0 open to members, 1 open to everyone
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        $stmt = Db::getPdo()->prepare("UPDATE `guilds_settings` SET `visibility` = :visibility WHERE `guild_id` = :guild_id");

        $stmt->execute([
            ":visibility" => $visibility,
            ":guild_id"   => $this->dbId
        ]);
    }

    /**
     * Returns whether or not you can apply for the guild
     * @return boolean True if you can apply, false if not
     */
    public function getCanApply()
    {
        return $this->canApply;
    }

    /**
     * Sets whether or not the guild is accepting applications
     * @param int 1 if true, 0 if not
     */
    public function setCanApply($canApply)
    {
        $this->canApply = $canApply;

        $stmt = Db::getPdo()->prepare("UPDATE `guilds_settings` SET `can_apply` = :can_apply WHERE `guild_id` = :guild_id");

        $stmt->execute([
            ":can_apply" => $this->canApply,
            ":guild_id"  => $this->dbId
        ]);
    }

    /**
     * Returns the response interface
     * @return ResponseInterface The response interface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns an array of strings representing roster members
     * @return Array Consisting of strings representing roster character names
     */
    public function getRosterMembers()
    {
        $roster = array();
        
        $stmt = Db::getPdo()->prepare("SELECT `name` FROM `guilds_roster` WHERE `guild_id` = :guild_id");
        $stmt->execute([":guild_id" => $this->dbId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $roster[] = $row['name'];
            }
        }

        return $roster;
    }

    /**
     * Save and register to the guild with the specified user as the registerer
     * For a guild to be saved, it needs to load data from the API and not the database (no initialization by ID)
     * @param User $user The user that is guild master
     */
    public function save($user)
    {
        if (!$this->isRegistered) {
            $db = Db::getPdo();
            $stmt = $db->prepare(
                "INSERT INTO `guilds`(`region`, `name`, `realm`, `level`, `faction`, `achievement_points`, `icon`, `icon_color`, `icon_color_id`, `border`,
                    `border_color`, `border_color_id`, `background_color`, `background_color_id`, `owner_id`, `last_modified`)
                    VALUES
                    (:region, :name, :realm, :level, :faction, :achievement_points, :icon, :icon_color, :icon_color_id, :border, :border_color, :border_color_id,
                    :background_color, :background_color_id, :owner_id, :last_modified)"
            );

            $stmt->execute([
                ":region"               => $this->region,
                ":name"                 => $this->guildName,
                ":realm"                => $this->realm,
                ":level"                => $this->level,
                ":faction"              => $this->side,
                ":achievement_points"   => $this->achievementPoints,
                ":icon"                 => $this->icon,
                ":icon_color"           => $this->iconColor,
                ":icon_color_id"        => $this->iconColorId,
                ":border"               => $this->border,
                ":border_color"         => $this->borderColor,
                ":border_color_id"      => $this->borderColorId,
                ":background_color"     => $this->backgroundColor,
                ":background_color_id"  => $this->backgroundColorId,
                ":owner_id"             => $user->getId(),
                ":last_modified"        => $this->lastModified
            ]);

            $this->dbId = $db->lastInsertId();

            // Insert into guild settings with default values
            $stmt = $db->prepare("INSERT INTO `guilds_settings`(`guild_id`) VALUES (:guild_id)");
            $stmt->execute([":guild_id" => $this->dbId]);
            
            
            $stmt = $db->prepare("INSERT INTO `guilds_permissions`(`guild_id`, `user_id`, `permissions`) VALUES (:guild_id, :user_id, 0)");
            $stmt->execute([
                ":guild_id"     => $this->dbId,
                ":user_id"      => $user->getId()
            ]);

            $this->saveCharacters();
        } else {
            $stmt = Db::getPdo()->prepare(
                "UPDATE `guilds` SET `level` = :level, `faction` = :faction, `achievement_points` = :achievement_points,
                `icon` = :icon, `icon_color` = :icon_color, `border` = :border, `border_color` = :border_color, 
                `background_color` = :background_color, `last_modified` = :last_modified WHERE `id` = :id"
            );

            $stmt->execute([
                ":level"                => $this->level,
                ":faction"              => $this->side,
                ":achievement_points"   => $this->achievementPoints,
                ":icon"                 => $this->icon,
                ":icon_color"           => $this->iconColor,
                ":border"               => $this->border,
                ":border_color"         => $this->borderColor,
                ":background_color"     => $this->backgroundColor,
                ":last_modified"        => $this->lastModified,
                ":id"                   => $this->dbId
            ]);

            $this->saveCharacters();
        }
    }

    /**
     * Save guilds the characters to the database
     */
    private function saveCharacters()
    {
        $body = json_decode($this->response->getBody(), true);
        $wowCharacter = new WowCharacter();
        $this->guildMembers = array();

        foreach ($body as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    if (is_array($value1)) {
                        foreach ($value1 as $key2 => $value2) {
                            // Members, first rank and then character information
                            if (!is_array($value2)) {
                                if ($key2 == "rank") {
                                    $guildMember = new GuildMember($wowCharacter, $this, $value2);
                                    $this->guildMembers[] = $guildMember;
                                    $wowCharacter = new WowCharacter();
                                } else {
                                    continue;
                                }
                            } else {
                                foreach ($value2 as $key3 => $value3) {
                                    if (!is_array($value3)) {
                                        $wowCharacter->setValue($key3, $value3);
                                    } else {
                                        foreach ($value3 as $key4 => $value4) {
                                            $wowCharacter->setValue("_" . $key4, $value4);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->guildMembers as $guildMember) {
            $wowChar = $guildMember->getCharacter();
            if ($wowChar->isSaveable()) {
                $wowChar->saveToGuild($this->dbId, $guildMember->getRank());
            }
        }
        $this->isRegistered = true;
    }

    /**
     * Initializes a new guild by ID by loading database information only
     * @param int $id The database ID of the guild
     * @return self A guild object containing only DB information about the specified guild, null if it doesn't exist
     */
    public static function byId($id)
    {
        $stmt = Db::getPdo()->prepare("SELECT `region`, `name`, `realm` FROM `guilds` WHERE `id` = :id");

        $stmt->execute([":id" => $id]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();
            return new self(null, $result->realm, $result->name, $result->region);
        } else {
            return null;
        }
    }
}
