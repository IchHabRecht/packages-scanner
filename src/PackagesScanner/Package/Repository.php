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
        return [];
    }
}
