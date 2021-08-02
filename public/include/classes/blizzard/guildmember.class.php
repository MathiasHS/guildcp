<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/wowcharacter.class.php";
require_once __DIR__ . "/guild.class.php";

/**
 * Class for representing a member of a guild
 */
class GuildMember
{

    private $wowCharacter;
    private $guild;
    private $rank;

    /**
     * Construct a GuildMember object
     * @param WowCharacter $wowCharacter The WoW character that is a member of the guild
     * @param Guild $guild The guild the WoW character is part of
     * @param int $rank The rank of the guild member
     */
    public function __construct(WowCharacter $wowCharacter, Guild $guild, $rank)
    {
        $this->wowCharacter = $wowCharacter;
        $this->guild = $guild;
        $this->rank = $rank;
    }

    /**
     * Returns the wow character of the GuildMember
     * @return WowCharacter The WoWCharacter
     */
    public function getCharacter()
    {
        return $this->wowCharacter;
    }

    /**
     * Returns the guild of the player
     * @return Guild the guild of the player
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * Returns the rank of the player
     * @return int A numerical representation of the players rank (0 is highest rank)
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Returns whether or not the GuildMember is the owner of the guild
     * @return boolean True if yes, false if not
     */
    public function isGuildMaster()
    {
        return ($this->rank == 0);
    }

    /**
     * Returns whether or not the GuildMember is an officer in the guild
     * @return boolean True if yes, false if not
     */
    public function isOfficer()
    {
        return ($this->rank <= 1);
    }

}
