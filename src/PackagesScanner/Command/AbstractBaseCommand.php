<?php
namespace IchHabRecht\PackagesScanner\Command;

use IchHabRecht\PackagesScanner\Package\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractBaseCommand extends Command
{
    /**
     * @var Repository
     */
    private $packageRepository;

    /**
     * @param string $name
     * @param Repository $packageRepository
     */
    public function __construct($name = null, Repository $packageRepository = null)
    {
        parent::__construct($name);
        $this->packageRepository = $packageRepository ?: new Repository();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->addArgument('repository-url', InputArgument::REQUIRED, 'The repository url to your packages.json file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function getPackagesFromRepository(InputInterface $input, OutputInterface $output)
    {
        $repositoryUrl = $input->getArgument('repository-url');
        $output->writeln('Scanning packages at ' . $repositoryUrl);
        $output->writeln('');

        return $this->packageRepository->findAllPackagesFromRepository($repositoryUrl);
    }

    /**
     * @param string $packageName
     * @return bool
     */
    protected function isValidPackageName($packageName)
    {
        return !empty($packageName)
        && preg_match('{^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*$}', $packageName);
    }

    /**
     * @param array $packages
     * @return array
     */
    protected function splitPackagesByVendor(array $packages)
    {
        $packagesByVendor = [];

        foreach ($packages as $packageName => $packagePackages) {
            if (false === strpos($packageName, '/')) {
                continue;
            }

            list($vendor, $name) = explode('/', $packageName);
            if (!isset($packagesByVendor[$vendor])) {
                $packagesByVendor[$vendor] = [];
            }
            $packagesByVendor[$vendor][$name] = $packagePackages;
        }

        return $packagesByVendor;
    }
}
