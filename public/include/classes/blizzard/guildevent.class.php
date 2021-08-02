<?php namespace GuildCP\Blizzard;

use GuildCP\Db;
use GuildCP\Player;

/**
 * Represent a guild event (guilds_events table)
 */
class GuildEvent
{

    private $guild;
    private $name;
    private $description;
    private $time;
    private $rosterOnly;
    private $dbId;

    /**
     * Construct a new GuildEvent
     * @param Guild $guild The guild of the event
     * @param string $name The name of the event
     * @param string $description The description of the vent
     * @param string $time The time of the event
     * @param int $rosterOnly Whether or not the event is roster only (0-1 / true/false)
     * @param int $dbId The database ID of the event if needed
     * @throws \InvalidArgumentException
     */
    public function __construct(Guild $guild, $name, $description, $time, $rosterOnly, $dbId = null)
    {
        if (!$guild->isRegistered()) {
            throw new \InvalidArgumentException("The guild specified is invalid or not registered.");
        }

        if (strlen($name) < 3 || strlen($name) > 31) {
            throw new \InvalidArgumentException("The name specified has to be between 2 and 32 characters.");
        }

        if (strlen($description) > 255) {
            throw new \InvalidArgumentException("The description has to be between 0 and 256 characters.");
        }

        $dateTime = \DateTime::createFromFormat("Y-m-d H:i", $time);
        $date = new \DateTime();

        if ($dateTime > $date) {
            throw new \InvalidArgumentException("The date specified has to be in the future.");
        }

        if ($rosterOnly < 0 || $rosterOnly > 1) {
            throw new \InvalidArgumentException("Roster only has to be either 1 for true, or 0 for false.");
        }
        
        $this->guild = $guild;
        $this->name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $this->description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        $this->time = $time;
        $this->rosterOnly = $rosterOnly;
        $this->dbId = $dbId;
    }

    /**
     * Saves the event to the database
     */
    public function save()
    {
        $stmt = Db::getPdo()->prepare(
            "INSERT INTO `guilds_events`(`guild_id`, `name`, `description`, `time`, `roster_only`)
                VALUES (:guild_id, :name, :description, :time, :roster_only)"
        );

        $stmt->execute([
            ":guild_id"    => $this->guild->getId(),
            ":name"        => $this->name,
            ":description" => $this->description,
            ":time"        => $this->time,
            ":roster_only" => $this->rosterOnly
        ]);
    }

    /**
     * Returns the guild of the event
     * @return Guild The guild hosting the event
     */
    public function getGuild()
    {
        return $this->guild;
    }

    /**
     * Returns the name of the event
     * @return string Name of the event
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the description of the event
     * @return string Description of the event
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Gets the time of the event
     * @return string Time of the event
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns a human readable datetime string
     * @return string Display time
     */
    public function getDisplayTime()
    {
        return str_replace("T", " ", $this->time);
    }

    /**
     * Gets whether or not the event is roster only
     * @return int 1 if yes, 0 if not
     */
    public function getRosterOnly()
    {
        return $this->rosterOnly;
    }

    /**
     * Returns the database ID of the event if constructed with it
     * @return null|int The database ID of the event, or null
     */
    public function getId()
    {
        return $this->dbId;
    }

    /**
     * Returns the amount of participants for the specified event
     * @return int The amount of participants
     */
    public function getParticipantCount()
    {
        $stmt = Db::getPdo()->prepare("SELECT COUNT(`user_id`) AS `count` FROM `guilds_events_attendance` WHERE `event_id` = :id AND `attending` = 1");

        $stmt->execute([":id" => $this->dbId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();

            return $result->count;
        }

        return 0;
    }

    /**
     * Returns an array consisting of usernames participating in the event
     * @return Array Consisting of participants
     */
    public function getParticipants()
    {
        $names = array();
        
        $stmt = Db::getPdo()->prepare(
            "SELECT a.`username`, a.`battletag` FROM `guilds_events_attendance` ev 
            INNER JOIN `accounts` a ON (a.`id` = ev.`user_id`) 
            INNER JOIN `guilds_events` e ON (e.`id` = ev.`event_id`) 
            WHERE ev.`attending` = 1 AND ev.`event_id` = :id"
        );
        $stmt->execute([":id" => $this->dbId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $names[] = "{$row['username']}/{$row['battletag']}";
            }
        }

        return $names;
    }

    /**
     * Returns whether or not the player has signed to attend the event
     * @param Player The player
     * @return boolean True if yes, false if not
     */
    public function isAttending(Player $user)
    {
        $stmt = Db::getPdo()->prepare("SELECT `attending` FROM `guilds_events_attendance` WHERE `event_id` = :event_id AND `user_id` = :user_id");

        $stmt->execute([
            ":event_id" => $this->dbId,
            ":user_id"  => $user->getId()
        ]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchObject();

            return ($result->attending == 1) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Get the event with the specified database ID
     * @param int $eventId The database ID of the event
     * @return null|GioødEvent Null if the event is not found, Event if found
     */
    public static function getEventById($eventId)
    {
        $stmt = Db::getPdo()->prepare("SELECT `guild_id`, `name`, `description`, `time`, `roster_only` FROM `guilds_events` WHERE `id` = :id");
        $stmt->execute([":id" => $eventId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                return new self(Guild::byId($row['guild_id']), $row['name'], $row['description'], $row['time'], $row['roster_only'], $eventId);
            }
        } else {
            return null;
        }
    }

    /**
     * Returns all the upcoming events for the specified guild ID
     * @param int $guildId The ID of the guild
     * @return array Consisting of Event objects
     */
    public static function getUpcomingEventsByGuild($guildId)
    {
        $events = array();

        $stmt = Db::getPdo()->prepare("SELECT `id`, `name`, `description`, `time`, `roster_only` FROM `guilds_events` WHERE `guild_id` = :guild_id AND `time` > NOW() ORDER BY `time`");
        $stmt->execute([":guild_id"    => $guildId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $event = new self(Guild::byId($guildId), $row['name'], $row['description'], $row['time'], $row['roster_only'], $row['id']);

                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Returns all the events for the specified guild ID
     * @param int $guildId The ID of the guild
     * @param int $rosterOnly Whether or not the events should be roster only
     * @return array Consisting of Event objects
     */
    public static function getAllEventsByGuild($guildId, $rosterOnly)
    {
        $events = array();

        $stmt = Db::getPdo()->prepare("SELECT `id`, `name`, `description`, `time`, `roster_only` FROM `guilds_events` WHERE `guild_id` = :guild_id AND `roster_only` = :roster_only ORDER BY `time`");
        $stmt->execute([
            ":guild_id"    => $guildId,
            ":roster_only" => $rosterOnly
        ]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $event = new self(Guild::byId($guildId), $row['name'], $row['description'], $row['time'], $row['roster_only'], $row['id']);

                $events[] = $event;
            }
        }

        return $events;
    }
}
