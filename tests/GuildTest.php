<?php 

declare (strict_types = 1);

require_once __DIR__ . "/../public/include/classes/player.class.php";
require_once __DIR__ . "/../public/include/config.php";
require_once __DIR__ . "/../public/include/classes/config.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/client.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/guild.class.php";

use PHPUnit\Framework\TestCase;
use GuildCP\Player;
use GuildCP\Config;
use GuildCP\Blizzard\Client;
use GuildCP\Blizzard\Guild;
use GuzzleHttp\Exception\ClientException;

final class GuildTest extends TestCase
{

    // Test that my character is the guild master of the specified guild
    public function testUserIsGuildMaster()
    {
        $client = new Client(Config::get("blizzard.client.id"), Config::get("blizzard.client.secret"));
        $guild = new Guild($client, "Emeriss", "Colabanken");

        $user = new Player(20);

        $this->assertTrue($guild->hasGuildMasterCharacter($user));
    }

    // Except exception if invalid guild
    public function testInvalidGuild()
    {
        $this->expectException(ClientException::class);

        $client = new Client(Config::get("blizzard.client.id"), Config::get("blizzard.client.secret"));
        $guild = new Guild($client, "Invalid guild", "Colabanken");
    }

    // Assert by ID
    public function testGuildByID()
    {
        $this->assertEquals(10, Guild::byId(10)->getId());
    }
}

