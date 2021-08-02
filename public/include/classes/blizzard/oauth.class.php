<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/client.class.php";
require_once __DIR__ . "/service.class.php";
require_once __DIR__ . "/token.class.php";

/**
 * Class for Oauth authorization flow
 */
class Oauth extends Service
{

    /**
     * Request access token is the second part of the blizzard API authorization code flow
     * https://develop.battle.net/documentation/guides/using-oauth/authorization-code-flow
     * @param string $grantType Grant type
     * @return Token $token Access token
     * @throws \HttpResponseException
     */
    public function requestAccessToken($grantType = 'client_credentials')
    {
        $options = [
            'auth'      => [$this->blizzardClient->getClientId(), $this->blizzardClient->getClientSecret()],
            'form_params' => [
                'grant_type' => $grantType
            ]
        ];

        $result = (new \GuzzleHttp\Client())->post($this->blizzardClient->getApiAccessTokenURL(), $options);

        
        if ($result->getStatusCode() == 200) {
            $json = json_decode($result->getBody()->getContents(), true);
            return new Token($json['access_token'], $json['token_type'], $json['expires_in']);
        } else {
            throw new \HttpResponseException("Invalid response");
        }
    }
}
