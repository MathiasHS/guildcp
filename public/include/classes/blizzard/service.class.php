<?php namespace GuildCP\Blizzard;

require_once __DIR__ . "/client.class.php";
/**
 * Inheritable class representing a Service
 */
class Service
{

    protected $blizzardClient;

    protected $serviceParam;

    /**
     * Initialize the class with the BlizzardClient
     * @param Client $client The GuildCP Blizzard Client
     */
    public function __construct(Client $client)
    {
        $this->blizzardClient = $client;
    }

    /**
     * Make a request with the specified API url and URL suffix
     * @param string $urlSuffix API URL method
     * @param array $options Options
     * @return GuzzleHttp\ResponseInterface
     */
    protected function request($urlSuffix, array $options, $genererateOptions = true, $url = null)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => ($url == null) ? ($this->blizzardClient->getApiUrl()) : ($url),
        ]);

        if ($genererateOptions) {
            $options = $this->generateRequestOptions($options);
        }
        
        return $client->request('GET', $this->serviceParam . $urlSuffix, $options);
    }

    protected function requestPost($url, array $options, $generateOptions = true)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->blizzardClient->getApiUrl(),
        ]);

        if ($generateOptions) {
            $options = $this->generateRequestOptions($options);
        }

        return $client->request('POST', $url, $options);
    }

    /**
     * Generate request options, set default options if needed
     * @param array $options
     * @return array $options
     */
    private function generateRequestOptions(array $options = [])
    {
        $result = [
            'query' => [],
            'headers' => [],
            'connect_timeout' => 30,
            'read_timeout'    => 75,
        ];

        if (isset($options['query']) || isset($options['headers'])) {
            if (isset($options['query'])) {
                $result['query'] += $options['query'];
                $result['query'] += $this->getQueryDefaultOptions();
            }

            if (isset($options['headers'])) {
                $result['headers'] += $options['headers'];
            }
        } else {
            $result = $options + $this->getDefaltRequestOptions();
        }

        return $result;
    }

    /**
     * Get the default request options
     * @return array $options
     */
    private function getDefaltRequestOptions()
    {
        return [
            'query'     => $this->getQueryDefaultOptions(),
            'headers'   => $this->getHeadersDefaultOptions()
        ];
    }

    /**
     * Get the query default options
     * @return array $options
     */
    private function getQueryDefaultOptions()
    {
        return [
            'locale' => $this->blizzardClient->getLocale(),
            'access_token' => $this->blizzardClient->getAccessToken()
        ];
    }

    /**
     * Get the header default options
     * @return array $options
     */
    protected function getHeadersDefaultOptions()
    {
        return [
            'access_token' => $this->blizzardClient->getAccessToken()
        ];
    }
}
