<?php
namespace IchHabRecht\PackagesScanner\Command\Vendor;

use IchHabRecht\PackagesScanner\Package\Repository as PackageRepository;
use IchHabRecht\PackagesScanner\Packagist\Repository as PackagistRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommand extends Command
{
    /**
     * @var PackageRepository
     */
    private $packageRepository;

    /**
     * @var PackagistRepository
     */
    private $packagistRepository;

    /**
     * @param string $name
     * @param PackageRepository $packageRepository
     * @param PackagistRepository $packagistRepository
     */
    public function __construct($name = null, PackageRepository $packageRepository = null, PackagistRepository $packagistRepository = null)
    {
        parent::__construct($name);
        $this->packageRepository = $packageRepository ?: new PackageRepository();
        $this->packagistRepository = $packagistRepository ?: new PackagistRepository();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('vendor:register')
            ->setDescription('Register unregistered vendor names')
            ->setHelp('This command registers the first package of unregistered vendor names on Packagist')
            ->addArgument('repository-url', InputArgument::REQUIRED, 'The repository url to your packages.json file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repositoryUrl = $input->getArgument('repository-url');

        $output->writeln('Scanning packages at ' . $repositoryUrl);
        $output->writeln('');

        $packages = $this->packageRepository->splitPackagesByVendor(
            $this->packageRepository->findAllPackagesFromRepository($repositoryUrl)
        );

        foreach ($packages as $vendor => $vendorPackages) {
            $packages = $this->packagistRepository->findPackagesByVendor($vendor);
            $isRegistered = !empty($packages);

            if ($isRegistered) {
                continue;
            }

            $output->writeln(' - ' . $vendor);
            foreach ($vendorPackages as $package) {
                $packageInformation = array_pop($package);
                $output->writeln('   - ' . $packageInformation['name']);
                $output->writeln('      - url: ' . ($packageInformation['source']['url'] ?? $packageInformation['dist']['url']));
                if (!empty($packageInformation['authors'])) {
                    foreach ($packageInformation['authors'] as $author) {
                        foreach ($author as $property => $value) {
                            $output->writeln('      - ' . $property . ': ' . $value);
                        }
                    }
                }
            }
            $output->writeln('');
        }

        return 0;
    }
}
