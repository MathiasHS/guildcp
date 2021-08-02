<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/client.class.php";
require_once __DIR__ . "/service.class.php";
require_once __DIR__ . "/token.class.php";

/**
 * Class for World of Warcraft community API
 */
class WoWCommunity extends Service
{

    protected $serviceParam = '/wow';
    
    /**
     * Return information about a specific achievement
     * @param array $options
     * @return ResponseInterface
     */
    public function getAchievement($achievementId, array $options = [])
    {
        return $this->request('/achievement/'. (int) $achievementId, $options);
    }

    /**
     * Return information about auction data
     * @param array $options
     * @return ResponseInterface
     */
    public function getAuctionDataStatus($realm, array $options = [])
    {
        return $this->request('/auction/'. (string) $realm, $options);
    }

    /**
     * Return information about the boss master liss
     * @param array $options
     * @return ResponseInterface
     */
    public function getBossMasterList(array $options = [])
    {
        return $this->request('/boss/', $options);
    }

    /**
     * Return information about a specific boss
     * @param array $options
     * @return ResponseInterface
     */
    public function getBoss($bossId, array $options = [])
    {
        return $this->request('/boss/' . (int) $bossId, $options);
    }

    /**
     * Return information about a specific character
     * @param string $realm The realm of the character
     * @param string $characterName The character name of the character
     * @param string $fields Fields to search for, look at Wow Documentation
     * @param array $options
     * @return ResponseInterface
     */
    public function getCharacter($realm, $characterName, $fields = "", array $options = [])
    {
        $queryParam = [
            'fields' => $fields
        ];

        if (isset($options['query'])) {
            $options['query'] += $queryParam;
        } else {
            $options['query'] = $queryParam;
        }

        return $this->request('/character/' . (string) $realm . '/' . (string) $characterName, $options);
    }

    /**
     * Return information about a specific guild
     * @param string $realm The realm of the guild
     * @param string $characterName The name of the guild
     * @param string $fields Fields to search for, look at Wow Documentation
     * @param array $options
     * @return ResponseInterface
     */
    public function getGuildProfile($realm, $guildName, $fields = "", array $options = [])
    {
        $queryParam = [
            'fields' => $fields
        ];

        if (isset($options['query'])) {
            $options['query'] += $queryParam;
        } else {
            $options['query'] = $queryParam;
        }

        return $this->request('/guild/' . (string) $realm . '/' . (string) $guildName, $options);
    }
}
