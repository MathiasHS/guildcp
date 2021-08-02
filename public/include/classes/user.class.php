<?php namespace GuildCP;

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/db.class.php";
require_once __DIR__ . "/player.class.php";

/**
 * Represents the online web session of a user
 */
class User extends Player
{

    /**
     * Attempt to construct the user if the user exists
     * @param $id The user ID
     * @return void
     */
    public function __construct($id)
    {
        parent::__construct($id);
    }

}