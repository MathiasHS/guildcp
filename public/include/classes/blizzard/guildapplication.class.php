<?php namespace GuildCP\Blizzard;

use GuildCP\Player;
use GuildCP\Db;

class GuildApplication
{

    private $dbId;
    private $guild;
    private $user;
    private $characterName;
    private $age;
    private $questionAddition;
    private $questionAttend;
    private $questionExtra;
    private $state;

    /**
     * Construct a new GuildApplication object, representing a guild application
     * @param Guild $guild The guild to apply for
     * @param Player $user The user who is applying
     * @param string $characterName The character name of the main character
     * @param int $age The age of the player
     * @param string $questionAddition Question about why you would be an addition to the roster
     * @param string $questionAttend Question about how much you would attend raids
     * @param string $questionExtra Optional extra information
     * @throws \InvalidArgumentException InvalidArgumentException if invalid input
     */
    public function __construct(Guild $guild, Player $user, $characterName, $age, $questionAddition, $questionAttend, $questionExtra = "", $dbId = null, $state = "")
    {
        if (!$guild->isRegistered() || (!$guild->getCanApply())) {
            throw new \InvalidArgumentException("The guild is not registered or does not accept applications.");
        }

        if (!$user->getCharacterCount()) {
            throw new \InvalidArgumentException("The user {$user->getUsername()} does not have any characters.");
        }

        if ($dbId == null && self::hasPendingApplication($guild, $user)) {
            throw new \InvalidArgumentException("The user {$user->getUsername()} already has a pending application for {$guild->getName()}.");
        }

        if (!strlen($characterName) || strlen($characterName) > 32) {
            throw new \InvalidArgumentException("The character name has to be between 1 and 32 characters.");
        }

        $characters = $user->getCharacters();
        $foundMain = false;
        foreach ($characters as $character) {
            if (strtolower($character->getName()) == strtolower($characterName)) {
                $foundMain = true;
                break;
            }
        }

        if (!$foundMain) {
            throw new \InvalidArgumentException("The character name entered must be owned by the player specified.");
        }

        if ($age <= 13 && $age >= 120) {
            throw new \InvalidArgumentException("The age specified has to be between 13 and 120.");
        }

        if (!strlen($questionAddition) || strlen($questionAddition) > 512) {
            throw new \InvalidArgumentException("The addition question has to be between 1 and 512 characters.");
        }

        if (!strlen($questionAttend) || strlen($questionAttend) > 256) {
            throw new \InvalidArgumentException("The attendance question has to be between 1 and 256 characters.");
        }

        if (strlen($questionExtra) > 256) {
            throw new \InvalidArgumentException("The extra information question has to be between 0 and 256 characters.");
        }

        $this->guild = $guild;
        $this->user = $user;
        $this->characterName = htmlspecialchars($characterName, ENT_QUOTES, 'UTF-8');
        $this->age = $age;
        $this->questionAddition = htmlspecialchars($questionAddition, ENT_QUOTES, 'UTF-8');
        $this->questionAttend = htmlspecialchars($questionAttend, ENT_QUOTES, 'UTF-8');
        $this->questionExtra = htmlspecialchars($questionExtra, ENT_QUOTES, 'UTF-8');
        $this->dbId = $dbId;
        $this->state = $state;
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
     * Returns the player that sent the application
     * @return Player Player object
     */
    public function getPlayer()
    {
        return $this->user;
    }

    /**
     * Returns the character name for the application
     * @return string Character name
     */
    public function getCharacterName()
    {
        return $this->characterName;
    }

    /**
     * Returns the state of the application
     * @return string Accepted|Denied|Pending, state of the application
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Returns the age of the player
     * @return int Age of the player
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Returns the answer to why the player should join the guild
     * @return string Answer to why the player should join the guild
     */
    public function getQuestionAddition()
    {
        return $this->questionAddition;
    }

    /**
     * Returns the answer to when he/she can play
     * @return string When he/she can play
     */
    public function getQuestionAttend()
    {
        return $this->questionAttend;
    }

    /**
     * Returns an optional answer, free text
     * @return string Optional answer, free text
     */
    public function getQuestionExtra()
    {
        return $this->questionExtra;
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
     * Saves the application to the database
     */
    public function save()
    {
        $stmt = Db::getPdo()->prepare(
            "INSERT INTO `guilds_applications` (`guild_id`, `user_id`, `character_name`, `age`, `question_addition`, `question_attend`, `question_extra`)
                VALUES (:guild_id, :user_id, :character_name, :age, :question_addition, :question_attend, :question_extra)"
        );

        $stmt->execute([
            ":guild_id"         => $this->guild->getId(),
            ":user_id"          => $this->user->getId(),
            ":character_name"   => $this->characterName,
            ":age"              => $this->age,
            "question_addition" => $this->questionAddition,
            "question_attend"   => $this->questionAttend,
            "question_extra"    => $this->questionExtra
        ]);
    }

    /**
     * Set the state of the application
     * @param string State, allowed options are ACCEPTED, DENIED, PENDING
     */
    public function setState($state)
    {
        $stmt = Db::getPdo()->prepare("UPDATE `guilds_applications` SET `state` = :state WHERE `id` = :id");
        $stmt->execute([
            ":state" => $state,
            ":id" => $this->dbId
        ]);
    }

    /**
     * Checks whether or not the player has a pending application
     * @param Guild The guild
     * @param Player The player
     * @return boolean True if yes, false if not
     */
    public static function hasPendingApplication(Guild $guild, Player $user)
    {
        $stmt = Db::getPdo()->prepare(
            "SELECT `id` FROM `guilds_applications` WHERE `guild_id` = :guild_id AND `user_id` = :user_id AND `state` = 'PENDING'"
        );

        $stmt->execute([
            ":guild_id" => $guild->getId(),
            ":user_id" => $user->getId()
        ]);

        if ($stmt->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the application with the specified database ID
     * @param int $applicationId The database ID of the event
     * @return null|GuildApplication Null if the event is not found, GuildApplication if found
     */
    public static function getApplicationById($applicationId)
    {
        $stmt = Db::getPdo()->prepare(
            "SELECT `id`, `guild_id`, `user_id`, `character_name`, `age`, `question_addition`, `question_attend`, `question_extra`
                FROM `guilds_applications` 
                WHERE `id` = :id");
        $stmt->execute([":id" => $applicationId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row) {
                return new self(Guild::byId($row['guild_id']), Player::fromId($row['user_id']), $row['character_name'], $row['age'], $row['question_addition'], $row['question_attend'], $row['question_extra'], $row['id']);
            }
        } else {
            return null;
        }
    }

    /**
     * Returns all the applications for the specified guild ID
     * @param int $guildId The database ID of the guild
     * @return array Consisting of GuildApplication objects
     */
    public static function getAllApplications($guildId)
    {
        $applications = array();

        $stmt = Db::getPdo()->prepare("SELECT `id`, `guild_id`, `user_id`, `character_name`, `age`, `question_addition`, `question_attend`, `question_extra`, `state`
            FROM `guilds_applications` WHERE `guild_id` = :guild_id AND `state` != 'PENDING' ORDER BY `guild_id` ASC");

        $stmt->execute([":guild_id" => $guildId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $application = new self(Guild::byId($guildId), Player::fromId($row['user_id']), $row['character_name'], $row['age'],
                 $row['question_addition'], $row['question_attend'], $row['question_extra'], $row['id'], $row['state']);

                $applications[] = $application;
            }
        }

        return $applications;
    }

    /**
     * Returns all the the pending applicants for the specified guild ID
     * @param int $guildId The ID of the guild
     * @return array Consisting of GuildApplication objects
     */
    public static function getAllApplicants($guildId)
    {
        $applications = array();

        $stmt = Db::getPdo()->prepare("SELECT `id`, `guild_id`, `user_id`, `character_name`, `age`, `question_addition`, `question_attend`, `question_extra`
            FROM `guilds_applications` WHERE `guild_id` = :guild_id AND `state` = 'PENDING' ORDER BY `guild_id` ASC");

        $stmt->execute(["guild_id"  => $guildId]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $application = new self(Guild::byId($guildId), Player::fromId($row['user_id']), $row['character_name'], $row['age'], $row['question_addition'], $row['question_attend'], $row['question_extra'], $row['id']);

                $applications[] = $application;
            }
        }

        return $applications;
    }
}