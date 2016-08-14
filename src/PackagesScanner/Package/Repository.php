<?php
namespace IchHabRecht\PackagesScanner\Package;

use GuzzleHttp\Client;

class Repository
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @param string $repositoryUrl
     * @return array
     */
    public function findAllPackagesFromRepository($repositoryUrl)
    {
        $packagesJson = $this->getRepositoryContent($repositoryUrl);
        $packages = $packagesJson['packages'] ?? [];
        $packages += $this->resolveRepositoryIncludes($repositoryUrl, $packagesJson);
        $packages += $this->resolveRepositoryProviderIncludes($repositoryUrl, $packagesJson);
        ksort($packages);

        return $packages;
    }

    /**
     * @param string $repositoryUrl
     * @return array
     */
    protected function getRepositoryContent($repositoryUrl)
    {
        $result = $this->client->request('GET', rtrim($repositoryUrl, '/') . '/packages.json');

        return json_decode($result->getBody(), true);
    }

    /**
     * @param string $repositoryUrl
     * @param array $packagesJson
     * @return array
     */
    protected function resolveRepositoryIncludes($repositoryUrl, array $packagesJson)
    {
        if (empty($packagesJson['includes'])) {
            return [];
        }

        $packages = [];
        foreach ($packagesJson['includes'] as $include => $information) {
            $url = rtrim($repositoryUrl, '/') . '/' . $include;
            $result = $this->client->request('GET', $url);
            $packages += json_decode($result->getBody(), true)['packages'];
        }

        return $packages;
    }

    /**
     * @param string $repositoryUrl
     * @param array $packagesJson
     * @return array
     */
    protected function resolveRepositoryProviderIncludes($repositoryUrl, array $packagesJson)
    {
        if (empty($packagesJson['provider-includes']) || empty($packagesJson['providers-url'])) {
            return [];
        }

        $packages = [];
        $repositoryUrl = rtrim($repositoryUrl, '/');
        $providersUrl = ltrim($packagesJson['providers-url'], '/');
        foreach ($packagesJson['provider-includes'] as $providerFileName => $providerInformation) {
            $hash = '';
            if (!empty($providerInformation['sha256'])) {
                $hash = $providerInformation['sha256'];
            }
            $absoluteProviderUrl = $repositoryUrl . '/' . str_replace('%hash%', $hash, ltrim($providerFileName, '/'));
            $result = $this->client->request('GET', $absoluteProviderUrl);
            $providers = json_decode($result->getBody(), true)['providers'];
            foreach ($providers as $packageName => $hashInformation) {
                $hash = '';
                if (!empty($hashInformation['sha256'])) {
                    $hash = $hashInformation['sha256'];
                }
                $providerUrl = str_replace('%hash%', $hash, $providersUrl);
                $providerUrl = str_replace('%package%', $packageName, $providerUrl);
                $absoluteProviderUrl = $repositoryUrl . '/' . $providerUrl;
                $result = $this->client->request('GET', $absoluteProviderUrl);
                $packages += json_decode($result->getBody(), true)['packages'];
            }
        }

        return $packages;
    }
}
