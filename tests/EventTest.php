<?php 

declare(strict_types=1);

require_once __DIR__ . "/../public/include/classes/player.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/guild.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/guildevent.class.php";

use PHPUnit\Framework\TestCase;
use GuildCP\Player;
use GuildCP\Blizzard\Guild;
use GuildCP\Blizzard\GuildEvent;

final class EventTest extends TestCase
{
    // Test cannot be created from invalid info
    public function testCannotBeCreatedFromInvalidInfo()
    {
        $this->expectException(\InvalidArgumentException::class);
        $guild = Guild::byId(12);
        new GuildEvent($guild, "", "", "", "");
    }

    // Test event by id
    public function testEventById()
    {
        $event = GuildEvent::getEventById(1);

        $this->assertEquals($event->getName(), "Test event");
        $this->assertEquals($event->getRosterOnly(), 0);
    }

    // Test that player attending event function works
    public function testIsAttending()
    {
        $event = GuildEvent::getEventById(1);

        $this->assertEquals($event->isAttending(Player::fromId(15)), true);
    }
}