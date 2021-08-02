<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/../db.class.php";

use \GuildCp\Db;
/**
 * Class for representing a WoW character
 */
class WowCharacter
{

    private $name;
    private $realm;
    private $class;
    private $race;
    private $gender;
    private $level;
    private $achievementPoints;
    private $thumbnail;
    private $region;

    // Guild class has not yet been created
    private $guildName;
    private $guildRealm;

    private $specName;
    private $specRole;
    private $lastModified;

    /**
     * Construct an object representing a WoW character
     */
    public function __construct()
    {
        $this->name = "";
        $this->realm = "";
        $this->class = 0;
        $this->race = 0;
        $this->gender = 0;
        $this->level = 0;
        $this->achievementPoints = 0;
        $this->guildName = "";
        $this->guildRealm = "";
        $this->specName = "";
        $this->specRole = "";
        $this->thumbnail = "";
        $this->region = "";
        $this->lastModified = 0;
    }

    /**
     * Set the private variables
     * @param string $key The key
     * @param string|int $value The value
     */
    public function setValue($key, $value)
    {
        switch ($key) {
            case "name":
                $this->name = $value;
                break;
            case "realm":
                $this->realm = $value;
                break;
            case "class":
                $this->class = $value;
                break;
            case "race":
                $this->race = $value;
                break;
            case "gender":
                $this->gender = $value;
                break;
            case "level":
                $this->level = $value;
                break;
            case "achievementPoints":
                $this->achievementPoints = $value;
                break;
            case "_name":
                $this->specName = $value;
                break;
            case "_role":
                $this->specRole = $value;
                break;
            case "guild":
                $this->guildName = $value;
                break;
            case "guildRealm":
                $this->guildRealm = $value;
                break;
            case "thumbnail":
                $this->thumbnail = $value;
                break;
            case "lastModified":
                $this->lastModified = $value;
                break;
            case "region":
                $this->region = $value;
                break;
            default:
                break;
        }
    }

    /**
     * Get the name of the character
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the realm(server) of the character
     * @return string $realm
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Get the characters class
     * @return int $class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the string representation of a class
     * @return string $class
     */
    public function getClassString()
    {
        $classString = "";
        switch ($this->class) {
            case 1:
                $classString = "Warrior";
                break;
            case 2:
                $classString = "Paladin";
                break;
            case 3:
                $classString = "Hunter";
                break;
            case 4:
                $classString = "Rogue";
                break;
            case 5:
                $classString = "Priest";
                break;
            case 6:
                $classString = "Death Knight";
                break;
            case 7:
                $classString = "Shaman";
                break;
            case 8:
                $classString = "Mage";
                break;
            case 9:
                $classString = "Warlock";
                break;
            case 10:
                $classString = "Monk";
                break;
            case 11:
                $classString = "Druid";
                break;
            case 12:
                $classString = "Demon Hunter";
                break;
            default:
                $classString = "Unknown";
                break;
        }

        return $classString;
    }

    /**
     * Get the character's race
     * @return int $race
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * Get the string representation of a race
     * @return string $race
     */
    public function getRaceString()
    {
        $raceString = "";
        switch ($this->race) {
            case 1:
                $raceString = "Human";
                break;
            case 2:
                $raceString = "Orc";
                break;
            case 3:
                $raceString = "Dwarf";
                break;
            case 4:
                $raceString = "Night Elf";
                break;
            case 5:
                $raceString = "Undead";
                break;
            case 6:
                $raceString = "Tauren";
                break;
            case 7:
                $raceString = "Gnome";
                break;
            case 8:
                $raceString = "Troll";
                break;
            case 9:
                $raceString = "Goblin";
                break;
            case 10:
                $raceString = "Blood Elf";
                break;
            case 11:
                $raceString = "Draenei";
                break;
            case 22:
                $raceString = "Worgen";
                break;
            case 25:
            case 26:
                $raceString = "Pandaren";
                break;
            case 27:
                $raceString = "Nightborne";
                break;
            case 28:
                $raceString = "Highmountain Tauren";
                break;
            case 29:
                $raceString = "Void Elf";
                break;
            case 30:
                $raceString = "Lightforged Draenei";
                break;
            case 31:
                $raceString = "Zandalari Troll";
                break;
            case 32:
                $raceString = "Kul Tiran";
                break;
            case 34:
                $raceString = "Dark Iron Dwarf";
                break;
            case 36:
                $raceString = "Mag'har Orc";
                break;
            default:
                $raceString = "Unknown";
                break;
        }

        return $raceString;
    }

    /**
     * Returns the characters gender
     * @return int $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Get the characters level
     * @return int $level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get the achievement points of the character
     * @return int $achievementPoints
     */
    public function getAchievementPoints()
    {
        return $this->achievementPoints;
    }

    /**
     * Returns whether or not the player is associated to a guild
     * @return boolean True if yes, false if not
     */
    public function hasGuild()
    {
        return ($this->guildName != null && strlen($this->guildName) >= 2);
    }

    /**
     * Get the guild name of the character
     * @return string $guildName
     */
    public function getGuildName()
    {
        return $this->guildName;
    }

    /**
     * Get the guild realm of the character
     * @return string $guildRealm
     */
    public function getGuildRealm()
    {
        return $this->guildRealm;
    }

    /**
     * Get the specialization name of the character
     * @return string $specName
     */
    public function getSpecName()
    {
        return $this->specName;
    }

    /**
     * Get the specialization role of the character
     * @return string $specRole
     */
    public function getSpecRole()
    {
        return $this->specRole;
    }

    /**
     * Get the thumbnail of the character
     * @return string The last part of the URL of the thumbnail
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Get the region of the character
     * @return string The region of the character (loaded if associated to Battle.Net account)
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Returns the last modification date of the character
     * @return int $lastModified
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Get the absolute image URL of a character if a region and thumbnail is set
     * @param string $Type Available renders are: avatar | main | inset
     * @return string $url The absolute URL of the image
     */
    public function getImageURL($type = "avatar")
    {
        $url = "https://render-{$this->region}.worldofwarcraft.com/character/";
        if ($type == "avatar") {
            $url .= $this->thumbnail;
        } else {
            $url .= $this->thumbnail;
            $url = str_replace("avatar", $type, $url);
        }
        return $url;
    }

    /**
     * Returns whether or not the player is saveable
     * @return boolean True if saveable, false if not
     */
    public function isSaveable()
    {
        if ($this->level >= 110) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the characters of a specified user
     * @return Array[] characters
     */
    public static function getCharacters($userId)
    {
        $characters = array();

        $stmt = Db::getPdo()->prepare(
            "SELECT a.`region`, ac.`name`, ac.`realm`, ac.`class`, ac.`race`, 
            ac.`spec_name`, ac.`spec_role`,  ac.`thumbnail`, ac.`last_modified`, 
            ac.`gender`, ac.`level`,  ac.`achievement_points`, ac.`guild_name`, 
            ac.`guild_realm` FROM `accounts_characters` ac 
            INNER JOIN `accounts` a ON (a.`id` = ac.`user_id`) 
            WHERE ac.`user_id` = :user_id"
        );

        $stmt->execute([":user_id" => $userId]);

        if ($stmt->rowCount()) {
            $wowChar = new WowCharacter();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $wowChar->setValue("region", $row["region"]);
                $wowChar->setValue("name", $row["name"]);
                $wowChar->setValue("realm", $row["realm"]);
                $wowChar->setValue("class", $row["class"]);
                $wowChar->setValue("race", $row["race"]);
                $wowChar->setValue("gender", $row["gender"]);
                $wowChar->setValue("level", $row["level"]);
                $wowChar->setValue("achievementPoints", $row["achievement_points"]);
                $wowChar->setValue("_name", $row["spec_name"]);
                $wowChar->setValue("_role", $row["spec_role"]);
                $wowChar->setValue("guild", $row["guild_name"]);
                $wowChar->setValue("guildRealm", $row["guild_realm"]);
                $wowChar->setValue("thumbnail", $row["thumbnail"]);
                $wowChar->setValue("lastModified", $row["last_modified"]);

                $characters[] = $wowChar;
                $wowChar = new WowCharacter();
            }
        }

        return $characters;
    }

    /**
     * Get the characters from DB for a specified guild
     * @return Array[] characters
     */
    public static function getGuildCharacters($guildId)
    {
        $characters = array();

        $stmt = Db::getPdo()->prepare(
            "SELECT `name`, `rank`, `level`, `class`, `race`, `gender`, `achievement_points`, 
            `spec_name`, `spec_role`, `thumbnail`, `last_modified` FROM `guilds_characters`
            WHERE `guild_id` = :guild_id"
        );

        $stmt->execute([":guild_id" => $guildId]);

        if ($stmt->rowCount()) {
            $guild = Guild::byId($guildId);
            $wowChar = new WowCharacter();

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                $wowChar->setValue("region", $guild->getRegion());
                $wowChar->setValue("name", $row["name"]);
                $wowChar->setValue("realm", $guild->getRealm());
                $wowChar->setValue("class", $row["class"]);
                $wowChar->setValue("race", $row["race"]);
                $wowChar->setValue("gender", $row["gender"]);
                $wowChar->setValue("level", $row["level"]);
                $wowChar->setValue("achievementPoints", $row["achievement_points"]);
                $wowChar->setValue("_name", $row["spec_name"]);
                $wowChar->setValue("_role", $row["spec_role"]);
                $wowChar->setValue("guild", $guild->getName());
                $wowChar->setValue("guildRealm", $guild->getRealm());
                $wowChar->setValue("thumbnail", $row["thumbnail"]);
                $wowChar->setValue("lastModified", $row["last_modified"]);

                $characters[] = $wowChar;
                $wowChar = new WowCharacter();
            }
        }

        return $characters;
    }

    /**
     * Get the string representation of a class
     * @param int $classId The classId
     * @return string $class
     */
    public static function getClassStringById($classId)
    {
        $classString = "";
        switch ($classId) {
            case 1:
                $classString = "Warrior";
                break;
            case 2:
                $classString = "Paladin";
                break;
            case 3:
                $classString = "Hunter";
                break;
            case 4:
                $classString = "Rogue";
                break;
            case 5:
                $classString = "Priest";
                break;
            case 6:
                $classString = "Death Knight";
                break;
            case 7:
                $classString = "Shaman";
                break;
            case 8:
                $classString = "Mage";
                break;
            case 9:
                $classString = "Warlock";
                break;
            case 10:
                $classString = "Monk";
                break;
            case 11:
                $classString = "Druid";
                break;
            case 12:
                $classString = "Demon Hunter";
                break;
            default:
                $classString = "Unknown";
                break;
        }

        return $classString;
    }

    /**
     * Get the string representation of a race
     * @param int $raceId The race ID
     * @return string $race
     */
    public static function getRaceStringById($raceId)
    {
        $raceString = "";
        switch ($raceId) {
            case 1:
                $raceString = "Human";
                break;
            case 2:
                $raceString = "Orc";
                break;
            case 3:
                $raceString = "Dwarf";
                break;
            case 4:
                $raceString = "Night Elf";
                break;
            case 5:
                $raceString = "Undead";
                break;
            case 6:
                $raceString = "Tauren";
                break;
            case 7:
                $raceString = "Gnome";
                break;
            case 8:
                $raceString = "Troll";
                break;
            case 9:
                $raceString = "Goblin";
                break;
            case 10:
                $raceString = "Blood Elf";
                break;
            case 11:
                $raceString = "Draenei";
                break;
            case 22:
                $raceString = "Worgen";
                break;
            case 25:
            case 26:
                $raceString = "Pandaren";
                break;
            case 27:
                $raceString = "Nightborne";
                break;
            case 28:
                $raceString = "Highmountain Tauren";
                break;
            case 29:
                $raceString = "Void Elf";
                break;
            case 30:
                $raceString = "Lightforged Draenei";
                break;
            case 31:
                $raceString = "Zandalari Troll";
                break;
            case 32:
                $raceString = "Kul Tiran";
                break;
            case 34:
                $raceString = "Dark Iron Dwarf";
                break;
            case 36:
                $raceString = "Mag'har Orc";
                break;
            default:
                $raceString = "Unknown";
                break;
        }

        return $raceString;
    }

    /**
     * Saves a character to the database, associated to the specified user ID
     * @param int $userId The user ID of the owner
     */
    public function save($userId)
    {
        $stmt = Db::getPdo()->prepare(
            "INSERT INTO `accounts_characters`
            (`user_id`, `name`, `realm`, `class`, `race`, `gender`, `level`, `achievement_points`,
            `guild_name`, `guild_realm`, `spec_name`, `spec_role`, `thumbnail`, `last_modified`)
            VALUES
            (:user_id, :name, :realm, :class, :race, :gender, :level, :achievement_points,
            :guild_name, :guild_realm, :spec_name, :spec_role, :thumbnail, :last_modified)
            ON DUPLICATE KEY UPDATE `class` = :class1, `race` = :race1, `gender` = :gender1, `level` = :level1,
            `achievement_points` = :achievement_points1, `guild_name` = :guild_name1, `guild_realm` = :guild_realm1,
            `spec_name` = :spec_name1, `spec_role` = :spec_role1, `thumbnail` = :thumbnail1, `last_modified` = :last_modified1;"
        );

        $stmt->execute(
            [":user_id"              => (int) $userId,
            ":name"                 => $this->name,
            ":realm"                => $this->realm,
            ":class"                => $this->class,
            ":race"                 => $this->race,
            ":gender"               => $this->gender,
            ":level"                => $this->level,
            ":achievement_points"   => $this->achievementPoints,
            ":guild_name"           => $this->guildName,
            ":guild_realm"          => $this->guildRealm,
            ":spec_name"            => $this->specName,
            ":spec_role"            => $this->specRole,
            ":thumbnail"            => $this->thumbnail,
            ":last_modified"        => $this->lastModified,
            ":class1"               => $this->class,
            ":race1"                => $this->race,
            ":gender1"              => $this->gender,
            ":level1"               => $this->level,
            ":achievement_points1"  => $this->achievementPoints,
            ":guild_name1"           => $this->guildName,
            ":guild_realm1"          => $this->guildRealm,
            ":spec_name1"           => $this->specName,
            ":spec_role1"           => $this->specRole,
            ":thumbnail1"           => $this->thumbnail,
            ":last_modified1"       => $this->lastModified]
        );
    }

    /**
     * Saves a character to the database, associated to a guild ID
     * @param int $guildId The ID of the guild
     * @param int $rank The rank of the character
     */
    public function saveToGuild($guildId, $rank)
    {
        $stmt = Db::getPdo()->prepare(
            "INSERT INTO `guilds_characters`(`guild_id`, `name`, `rank`, `level`, `class`, `race`, `gender`, `achievement_points`, `spec_name`,
            `spec_role`, `thumbnail`, `last_modified`)
            VALUES
            (:guild_id, :name, :rank, :level, :class, :race, :gender, :achievement_points, :spec_name, :spec_role, :thumbnail, :last_modified)
            ON DUPLICATE KEY UPDATE `rank` = :rank1, `level` = :level1, `race` = :race1, `gender` = :gender1, `achievement_points` = :achievement_points1,
            `spec_name` = :spec_name1, `spec_role` = :spec_role1, `thumbnail` = :thumbnail1, `last_modified` = :last_modified1;"
        );

        $stmt->execute(
            [":guild_id"                         => $guildId,
            ":name"                             => $this->name,
            ":rank"                             => $rank,
            ":level"                            => $this->level,
            ":class"                            => $this->class,
            ":race"                             => $this->race,
            ":gender"                           => $this->gender,
            ":achievement_points"               => $this->achievementPoints,
            ":spec_name"                        => $this->specName,
            ":spec_role"                        => $this->specRole,
            ":thumbnail"                        => $this->thumbnail,
            ":last_modified"                    => $this->lastModified,
            ":rank1"                            => $rank,
            ":level1"                           => $this->level,
            ":race1"                            => $this->race,
            ":gender1"                          => $this->gender,
            ":achievement_points1"              => $this->achievementPoints,
            ":spec_name1"                       => $this->specName,
            ":spec_role1"                       => $this->specRole,
            ":thumbnail1"                       => $this->thumbnail,
            ":last_modified1"                   => $this->lastModified]
        );
    }
}
