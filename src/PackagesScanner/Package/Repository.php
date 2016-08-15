<?php
namespace IchHabRecht\PackagesScanner\Package;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\CompletePackage;
use Composer\Repository\ComposerRepository;

class Repository
{
    /**
     * @var ComposerRepository
     */
    private $composerRepository;

    /**
     * @param string $repositoryUrl
     * @return array
     */
    public function findAllPackagesFromRepository($repositoryUrl)
    {
        $io = new NullIO();
        $config = Factory::createConfig($io);
        $this->composerRepository = new ComposerRepository([
            'url' => $repositoryUrl,
        ], $io, $config);

        $packages = [];
        if ($this->composerRepository->hasProviders()) {
            foreach ($this->composerRepository->getProviderNames() as $name) {
                $packages[$name] = [];
            }
        } else {
            foreach ($this->composerRepository->getPackages() as $package) {
                if ($package instanceof CompletePackage) {
                    $packages[$package->getPrettyName()][$package->getPrettyVersion()] = $package;
                    ksort($packages[$package->getPrettyName()]);
                }
            };
        }

        ksort($packages);

        return $packages;
    }

    /**
     * @param string $packageName
     * @return CompletePackage[]
     */
    public function findPackageVersionsByName($packageName)
    {
        $versions = [];
        foreach ($this->composerRepository->findPackages($packageName) as $package) {
            if ($package instanceof CompletePackage) {
                $versions[$package->getPrettyVersion()] = $package;
            }
        }

        ksort($versions);

        return $versions;
    }
}
