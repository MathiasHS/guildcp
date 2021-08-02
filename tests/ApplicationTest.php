<?php

declare (strict_types = 1);

require_once __DIR__ . "/../public/include/classes/player.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/guild.class.php";
require_once __DIR__ . "/../public/include/classes/blizzard/guildapplication.class.php";

use PHPUnit\Framework\TestCase;
use GuildCP\Player;
use GuildCP\Blizzard\Guild;
use GuildCP\Blizzard\GuildApplication;

final class ApplicationTest extends TestCase
{
    // Test cannot be created from invalid info
    public function testCannotBeCreatedFromInvalidInfo()
    {
        $this->expectException(\InvalidArgumentException::class);
        $guild = Guild::byId(12);
        $user = Player::fromId(15);
        new GuildApplication($guild, $user, "", "", "", "", "");
    }

    // Test cannot be created when user doesn't own the character
    public function testCannotBeCreatedWithWrongCharacter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $guild = Guild::byId(12);
        $user = Player::fromId(15);
        new GuildApplication($guild, $user, "wrongcharacter", 20, "Test", "Yes");
    }
}
