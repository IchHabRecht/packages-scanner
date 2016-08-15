<?php
namespace IchHabRecht\PackagesScanner\Command\Vendor;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use IchHabRecht\PackagesScanner\Repository\PackagistRepository;
use IchHabRecht\PackagesScanner\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommand extends AbstractBaseCommand
{
    /**
     * @var PackagistRepository
     */
    private $packagistRepository;

    /**
     * @param string $name
     * @param Repository $packageRepository
     * @param PackagistRepository $packagistRepository
     */
    public function __construct($name = null, Repository $packageRepository = null, PackagistRepository $packagistRepository = null)
    {
        parent::__construct($name, $packageRepository);
        $this->packagistRepository = $packagistRepository ?: new PackagistRepository();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('vendor:register')
            ->setDescription('Register unregistered vendor names')
            ->setHelp('This command registers the first package of unregistered vendor names on Packagist');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packages = $this->splitPackagesByVendor($this->getPackagesFromRepository($input, $output));
        $packagistPackages = $this->splitPackagesByVendor($this->packagistRepository->findAllPackagesFromRepository());

        $i = 0;
        $j = 0;
        foreach ($packages as $vendor => $vendorPackages) {
            $isRegistered = isset($packagistPackages[$vendor]);
            if ($isRegistered) {
                continue;
            }

            $output->writeln(' - ' . $vendor);
            foreach ($vendorPackages as $name => $packageVersions) {
                if (empty($packageVersions)) {
                    $packageVersions = $this->getPackageVersionsFromRepository($vendor . '/' . $name);
                    if (empty($packageVersions)) {
                        continue;
                    }
                }

                $package = array_pop($packageVersions);
                $output->writeln('   - ' . $name);
                $output->writeln('      - url: ' . ($package->getSourceUrl() ?: $package->getDistUrl()));
                if (!empty($package->getAuthors())) {
                    foreach ($package->getAuthors() as $author) {
                        foreach ($author as $property => $value) {
                            $output->writeln('      - ' . $property . ': ' . $value);
                        }
                    }
                }
                $i++;
            }
            $output->writeln('');
            $j++;
        }

        $output->writeln($i . ' unregistered packages for ' . $j . ' unregistered vendors found');

        return 0;
    }
}
