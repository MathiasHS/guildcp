<?php namespace GuildCP;

/**
 * Class for generating randoms
 */
class Random
{
    /**
     * Generates a random salt
     * @params $length The length of the salt
     * @params $encoding The encoding to use
     *
     * @return random $salt
     */
    public static function generateSalt($length = 12, $encoding = "base64")
    {
        $salt = random_bytes($length);

        switch ($encoding) {
            case "sha":
            {
                $salt = hash("sha512", $salt);
                break;
            }
            case "sha2":
            {
                $salt = hash("sha256", $salt);
                break;
            }
            case "base64":
            {
                $salt = base64_encode($salt);
                break;
            }
            default:
            {
                $salt = hash("whirlpool", $salt);
                break;
            }
        }

        $salt = substr($salt, 0, $length);
        return $salt;
    }
}
