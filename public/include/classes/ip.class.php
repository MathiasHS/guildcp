<?php namespace GuildCP;

/**
 * Represent any IPv4 address and handle IPs from Cloudflare
 */
class Ip
{
    private $ip;

    /**
     * Create an object representing an IPv4 address
     * @param string $ip A numeric, dot-decimal or a null. If "get" is given, the current IP of the user will be used.
     */
    public function __construct($ip)
    {
        if ($ip == "get") {
            $this->ip = $this->getRealIp();
        } elseif (is_numeric($ip)) {
            $this->ip = long2ip($ip);
        } else {
            $this->ip = $ip;
        }
    }

    /**
     * Get a dot-decimal representation of the Ip object. If no address has been specified, N/A will be returned.
     * @return string The IP of the user
     */
    public function getIp()
    {
        return !empty($this->ip) ? $this->ip : "N/A";
    }

    /**
     * Get a dot-decimal representation of the connecting IP, regardless of it's coming through Cloudflare or not.
     * @return string The IP of the user
     */
    private function getRealIp()
    {
        return (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) ? ($_SERVER["HTTP_CF_CONNECTING_IP"]) : ($_SERVER["REMOTE_ADDR"]);
    }
}
