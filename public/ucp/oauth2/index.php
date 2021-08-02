<?php namespace GuildCP;

require_once "../../include/header.php";

use GuildCP\Blizzard\BattleNet;
use GuildCP\Blizzard\Token;

if (!Auth::check()) {
    Redirect::to("../");
}

if (isset($_GET["code"])) {
    $ip = new Ip("get");

    /**
     * CRSF prevention - should be more than sufficient for now
     * TODO: Look into the most up to date / secure ways to do this in PHP,
     * cookies / mysql might be an option
     */
    if ($_GET["state"] == base64_encode($ip->getIp())) {
        $client = $blizzardClient->setRegion(Auth::user()->getRegion());

        $bnet = new BattleNet($client);
        
        $bnet->setCode($_GET["code"]);
        $bnet->setClientId(Config::get("blizzard.client.id"));
        $bnet->setClientSecret(Config::get("blizzard.client.secret"));
        $bnet->setRedirectURI(Config::get("blizzard.redirect.uri"));

        $user = Auth::user();

        $token = $user->getAccessToken();
        if ($token !== null) {
            $bnet->setAccessToken($token);
        } else {
            try {
                $result = $bnet->post();
            } catch (\Exception $e) {
                Redirect::to("../?sync=failed_token");
                return;
            }
        }

        if ($token !== null || $result->getStatusCode() == 200) {
            if ($token === null) {
                $json = json_decode($result->getBody()->getContents(), true);
                
                $bnet->setAccessToken(new Token($json['access_token'], $json['token_type'], $json['expires_in']));
                $user->setAccessToken($json['access_token'], $json['expires_in']);
            }
    
            try {
                $result = $bnet->getUserInfo();
            } catch (\Exception $e) {
                throw new \Exception("Exception: $e");
                Redirect::to("../?sync=failed_battlenet");
            }
            if ($result->getStatusCode() == 200) {
                $json = json_decode($result->getBody()->getContents(), true);
    
                $user->setBattleTag($json['battletag']);
                $user->setSubId($json['id']);
                $user->save();
                try {
                    $result = $bnet->getCharactersInfo();
                } catch (\Exception $e) {
                    throw new \Exception("Exception: $e");
                    Redirect::to("../?sync=failed_characters");
                }
                if ($result->getStatusCode() == 200) {
                    $json = json_decode($result->getBody()->getContents(), true);
                    $user->saveWowCharacters($json);
                    Redirect::to("../?sync=complete");
                } else {
                    Redirect::to("../?sync=failed_characters");
                    throw new \HttpResponseException("Exception: Invalid response!");
                }
            } else {
                Auth::user()->setRegion(null);
                Redirect::to("../?sync=failed_battlenet");
                throw new \HttpResponseException("Exception: Invalid response!");
            }
        } else {
            Auth::user()->setRegion(null);
            Redirect::to("../?sync=failed_token");
            throw new \HttpResponseException("Exception: Invalid response!");
        }
    } else {
        Auth::user()->setRegion(null);
        Redirect::to("../?sync=failed_token");
        throw new \HttpResponseException("Exception: Invalid response!");
    }
} else {
    Auth::user()->setRegion("");
    Redirect::to("../");
}
