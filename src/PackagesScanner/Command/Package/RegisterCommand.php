<?php
namespace IchHabRecht\PackagesScanner\Command\Package;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use IchHabRecht\PackagesScanner\Repository\PackagistRepository;
use IchHabRecht\PackagesScanner\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setName('package:register')
            ->setDescription('Register unregistered packages')
            ->setHelp('This command registers non-existing packages on Packagist')
            ->addOption('exclude-vendor', null, InputOption::VALUE_OPTIONAL, 'Comma separated list of vendor names to exclude');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $excludeVendorNames = explode(',', $input->getOption('exclude-vendor'));
        array_walk($excludeVendorNames, 'trim');

        $packages = $this->splitPackagesByVendor($this->getPackagesFromRepository($input, $output));
        $packagistPackages = $this->packagistRepository->findAllPackagesFromRepository();

        $i = 0;
        foreach ($packages as $vendor => $vendorPackages) {
            if (in_array($vendor, $excludeVendorNames, true)) {
                continue;
            }

            foreach ($vendorPackages as $name => $packageVersions) {
                $packageName = $vendor . '/' . $name;
                if (!$this->isValidPackageName($packageName)) {
                    continue;
                }

                $isRegistered = isset($packagistPackages[$packageName]);
                if ($isRegistered) {
                    continue;
                }

                if (empty($packageVersions)) {
                    $packageVersions = $this->getPackageVersionsFromRepository($packageName);
                    if (empty($packageVersions)) {
                        continue;
                    }
                }

                $output->writeln(' - ' . $packageName);
                $package = array_pop($packageVersions);
                $output->writeln('   - url: ' . ($package->getSourceUrl() ?: $package->getDistUrl()));
                if (!empty($package->getAuthors())) {
                    foreach ($package->getAuthors() as $author) {
                        foreach ($author as $property => $value) {
                            $output->writeln('   - ' . $property . ': ' . $value);
                        }
                    }
                }
                $output->writeln('');
                $i++;
            }
        }

        $output->writeln($i . ' unregistered packages found');

        return 0;
    }
}
