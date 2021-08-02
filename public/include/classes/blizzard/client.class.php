<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../config.class.php";
require_once __DIR__ . "/region.class.php";
require_once __DIR__ . "/oauth.class.php";
require_once __DIR__ . "/invalidoptionsexception.class.php";

use GuildCP\Config;
/**
 * Class representing a client for blizzard connection
 */
class Client
{
    const API_URL_PATTERN                   = 'https://region.api.blizzard.com';
    // China's URL is the only one that is not using the api subdomains
    const CHINA_API_URL                     = 'https://gateway.battlenet.com.cn';
    const CHINA_OAUTH_URL                   = 'https://www.battlenet.com.cn/oauth/token';
    const API_ACCESS_TOKEN_URL_PATTERN      = 'https://region.battle.net/oauth/token';

    private $apiUrl;
    private $apiAccessTokenUrl;

    private $clientId;
    private $clientSecret;

    private $locale;
    private $region;
    
    private $accessTokens;
    private $options;

    /**
     * Construct a client to access data from the Blizzard API
     * @param string $clientId The client ID from blizzard
     * @param string $clientSecret The client secret from blizzard
     * @param string $region The region to access (check blizzard docs)
     * @param string $locale The language you would like to return data in
     */
    public function __construct($clientId, $clientSecret, $region = 'eu', $locale = 'en_eu')
    {
        $this->options = [
            'clientId'      => $clientId,
            'clientSecret'  => $clientSecret,
            'region'        => strtolower($region),
            'locale'        => strtolower($locale)
        ];

        $this->verifyOptions();

        $this->clientId     = $this->options['clientId'];
        $this->clientSecret = $this->options['clientSecret'];
        $this->region       = $this->options['region'];
        $this->locale       = $this->options['locale'];

        $this->updateApiURL($this->options['region']);
        $this->updateApiAccessTokenURL($this->options['region']);
    }

    /**
     * Verify that the options provided are actually correct
     * @throws InvalidOptionsException
     */
    private function verifyOptions()
    {
        if (!strlen($this->options['clientId'])) {
            throw new InvalidOptionsException("The client ID must be specified.");
        }

        if (!strlen($this->options['clientSecret'])) {
            throw new InvalidOptionsException("The client secret must be specified.");
        }
        
        if (!isset(Region::$list[$this->options['region']])) {
            throw new InvalidOptionsException(
                sprintf(
                    'The option region with value %s is invalid. Accepted values are: "%s".',
                    $this->options['region'],
                    implode('", "', array_keys(Region::$list))
                )
            );
        }
    }

    /**
     * Update the API URL by replacing the region in the API url pattern
     * @param string $region The region
     * @return $this
     */
    private function updateApiURL($region)
    {
        if ($region == 'cn') {
            $this->apiUrl = self::CHINA_API_URL;
        } else {
            $this->apiUrl = str_replace('region', strtolower($region), self::API_URL_PATTERN);
        }
        return $this;
    }

    /**
     * Updates the API Access Token URL by replacing region in the API URL pattern
     * @param string $region The region
     * @return $this
     */
    private function updateApiAccessTokenURL($region)
    {
        if ($region == 'cn') {
            $this->apiAccessTokenUrl = self::CHINA_OAUTH_URL;
        } else {
            $this->apiAccessTokenUrl = str_replace('region', strtolower($region), self::API_ACCESS_TOKEN_URL_PATTERN);
        }
        return $this;
    }

    /**
     * Set the access token
     * @param Token $accessToken The access token
     * @return $this
     */
    public function setAccessToken(Token $accessToken)
    {
        $this->accessTokens[$this->getRegion()] = $accessToken;
        return $this;
    }

    // Getters

    /**
     * Get the access token
     * @return null|string Access token
     * @throws TokenExpired
     * @throws \HttpResponseException
     */
    public function getAccessToken()
    {
        if (null === $this->accessTokens) {
            $this->accessTokens = [];
        }

        if (!array_key_exists($this->getRegion(), $this->accessTokens)) {
            $oauth = new Oauth($this);

            $this->accessTokens[$this->getRegion()] = $oauth->requestAccessToken();
        }

        return $this->accessTokens[$this->getRegion()]->getToken();
    }

    /**
     * Set the region
     * @param string $region The region to set
     * @return this The current instance
     */
    public function setRegion($region)
    {
        $options = array();
        
        $this->verifyOptions($options, $region);

        $this->updateApiURL($region);
        $this->updateApiAccessTokenURL($region);
        
        return $this;
    }

    /**
     * Get the API url
     * @return string Api url
     */
    public function getApiURL()
    {
        return $this->apiUrl;
    }

    /**
     * Get the API Access token URl
     * @return string Api access token URL
     */
    public function getApiAccessTokenURL()
    {
        return $this->apiAccessTokenUrl;
    }

    /**
     * Get the current locale
     * @return string Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get the current region
     * @return string Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Get the current client ID
     * @return string Client ID
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get the current client secret
     * @return string Client secret
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }
}
