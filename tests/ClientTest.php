<?php 

declare(strict_types=1);

require_once __DIR__ . "/../public/include/config.php";
require_once __DIR__ . "/../public/include/classes/config.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/client.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/invalidoptionsexception.class.php";

use PHPUnit\Framework\TestCase;
use GuildCP\Config;
use GuildCP\Blizzard\Client;
use GuildCP\Blizzard\InvalidOptionsException;

final class ClientTest extends TestCase
{

    // Tries to create a client with an invalid region
    public function testInvalidRegion()
    {
        $this->expectException(InvalidOptionsException::class);
        new Client(Config::get("blizzard.client.id"), Config::get("blizzard.client.secret"), "ll");
    }

    // Verify that the test API url is correct
    public function testApiURL()
    {
        $client = new Client(Config::get("blizzard.client.id"), Config::get("blizzard.client.secret"));
        $this->assertEquals("https://eu.api.blizzard.com", $client->getApiURL());
    }
}