<?php 

declare(strict_types=1);

require_once __DIR__ . "/../public/include/classes/player.class.php";

use PHPUnit\Framework\TestCase;
use GuildCP\Player;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;

final class PlayerTest extends TestCase
{
    // Test cannot be created from invalid email
    public function testCannotBeCreatedFromInvalidEmailAddress()
    {
        $this->expectException(InvalidArgumentException::class);

        Player::fromEmail('invalid');
    }

    // Assert by ID
    public function testAssertById()
    {
        $this->assertEquals(15, Player::fromId(15)->getId());
    }

    // Assert email
    public function testAssertEmail()
    {
        $this->assertEquals(
            'sondre.kjempekjenn@gmail.com',
            Player::fromEmail('sondre.kjempekjenn@gmail.com')->getEmail()
        );
    }
}