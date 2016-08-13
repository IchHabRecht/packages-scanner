<?php
namespace IchHabRecht\PackagesScanner\Command\Vendor;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use IchHabRecht\PackagesScanner\Package\Repository as PackageRepository;
use IchHabRecht\PackagesScanner\Packagist\Repository as PackagistRepository;
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
     * @param PackageRepository $packageRepository
     * @param PackagistRepository $packagistRepository
     */
    public function __construct($name = null, PackageRepository $packageRepository = null, PackagistRepository $packagistRepository = null)
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
