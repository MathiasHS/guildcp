<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/tokenexpired.class.php";

/**
 * Class for handling access token
 */
class Token
{
    private $token;
    private $tokenType;
    private $expiresIn;
    private $createdAt;
    private $expiresAt;

    /**
     * Initialize a new token
     * @param string $accessToken The access token
     * @param string $tokenType The token type
     * @param int $expiresIn Expires in (seconds)
     */
    public function __construct($accessToken, $tokenType, $expiresIn)
    {
        $this->token = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->createdAt = new \DateTime();
        $this->expiresAt = new \DateTime();
        $this->expiresAt->add(new \DateInterval("PT" . $expiresIn . "S"));
    }

    /**
     * Check if the token is expired or not
     * @return boolean True if expired, false if not
     */
    public function isExpired()
    {
        return $this->expiresAt < new \DateTime();
    }

    /**
     * Return the current token
     * @return string $token The token
     * @throws TokenExpired
     */
    public function getToken()
    {
        if ($this->isExpired()) {
            throw new TokenExpired("The token has expired.");
        }

        return $this->token;
    }
}
