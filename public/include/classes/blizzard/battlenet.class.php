<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/client.class.php";
require_once __DIR__ . "/service.class.php";

class BattleNet extends Service
{
    private $code;
    private $uri;
    private $clientId;
    private $clientSecret;

    private $token = null;

    /**
     * Set the code that was retrieved in the authorization flow
     * @param $code The code to set
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Set the redirect uri used in the provider
     * @param string uri The redirect uri
     */
    public function setRedirectURI($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Set the client id of the application
     * @param $clientId The client Id to set
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * Set the client secret
     * @param $clientSecret The client secret to set
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Make a post request to get the authorization code flow token
     */
    public function post()
    {
        return $this->requestPost($this->blizzardClient->getApiAccessTokenURL(), $this->getDefaultOptions(), false);
    }

    /**
     * Make a request asking about Battle.Net userinfo about the player
     * @return ResponseInterface
     */
    public function getUserInfo()
    {
        $url = $this->blizzardClient->getApiAccessTokenURL();
        $url = str_replace("token", "userinfo", $url);

        return $this->request('', $this->getUserInfoOptions(), false, $url);
    }

    /**
     * Make a request about the player's WoW characters
     * @return ResponseInterface
     */
    public function getCharactersInfo()
    {
        return $this->request("/wow/user/characters", $this->getUserInfoOptions(), false);
    }

    /**
     * Get the access token
     * @return Token The access token
     */
    public function getAccessToken()
    {
        return $this->token;
    }

    /**
     * Set the access token
     * @param Token $token The token to set
     */
    public function setAccessToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * Get user info options
     * @return Array Options for authorization code flow part 2
     */
    private function getUserInfoOptions()
    {
        return [
            'headers' => $this->getHeadersDefaultOptions(),
            'debug' => false
        ];
    }

    /**
     * Get default options, for authorization code flow part 1
     * @return Array Options for code flow part 1
     */
    protected function getDefaultOptions()
    {
        return [
            'headers'  => $this->getHeadersDefaultOptions(),
            'debug' => false,
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'code'          => $this->code,
                'redirect_uri'  => $this->uri,
                'scope'         => 'wow.profile',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret
            ]
        ];
    }

    /**
     * Get the header default options
     * @return array $options
     */
    protected function getHeadersDefaultOptions()
    {
        if ($this->token === null) {
            $auth = 'Basic ' . base64_encode($this->clientId . ":" . $this->clientSecret);
            return [
                'Authorization: ' => $auth
            ];
        } else {
            return [
                'Authorization' => 'Bearer ' . $this->token->getToken()
            ];
        }
    }
}
