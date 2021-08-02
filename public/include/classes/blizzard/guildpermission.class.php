<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/guild.class.php";
require_once __DIR__ . "/../player.class.php";

use GuildCP\Player;

/**
 * Class for representing a guild permission relationship between a guild and a user
 */
class GuildPermission
{

    private $guild;
    private $user;
    private $permissions;

    /**
     * Construct a GuildPermission object
     * @param Guild $guild The guild of the relationship
     * @param Player $user The user in the relationship
     * @param int $permissions The permissions of the user
     */
    public function __construct(Guild $guild, Player $user, $permissions)
    {
        $this->guild = $guild;
        $this->user = $user;
        $this->permissions = $permissions;
    }

    /**
     * Returns the Guild object
     * @return Guild The guild object
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * Returns the user object
     * @return Player The user (polymorphed) object
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the permissions of the player
     * @return int 0 if guild master, 1 if moderator, 2 if member
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Returns whether or not the player is the guild master of the guild
     * @return boolean True if guild master, false if not
     */
    public function isGuildMaster()
    {
        return ($this->permissions == 0);
    }

    /**
     * Returns whether or not the player is a guild moderator of the guild
     * @return boolean True if guild moderator, false if not
     */
    public function isGuildModerator()
    {
        return ($this->permissions <= 1);
    }

    /**
     * Returns whether or not the player is a guild member of the guild
     * In the future, we might want to expand on the ranks or make a rank for disallowing access
     * @return boolean True if the player is a guild member with permissions to see, false if not
     */
    public function isGuildMember()
    {
        return ($this->permissions <= 2);
    }

    /**
     * Get the rank name of the specified permission
     * @param int $permission The permission to check
     * @return string The string representation of the permission
     */
    public static function getRankName($permission)
    {
        $permission = "";
        switch ($permission) {
            case 0:
                $permission = 'Guild Master';
                break;
            case 1:
                $permission = 'Officer';
                break;
            case 2:
                $permission = 'Member';
                break;
            default:
                $permission = $permission;
                break;
        }
        return $permission;
    }

}