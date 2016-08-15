<?php
namespace IchHabRecht\PackagesScanner\Command\Package;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use IchHabRecht\PackagesScanner\Repository\PackagistRepository;
use IchHabRecht\PackagesScanner\Repository\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends AbstractBaseCommand
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
            ->setName('package:compare')
            ->setDescription('Compares local packages with Packagist')
            ->setHelp('This command lists all packages which are found in the provided repository and on Packagist')
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

        $packages = $this->getPackagesFromRepository($input, $output);
        $packagistPackages = $this->packagistRepository->findAllPackagesFromRepository();

        $sharedPackages = array_intersect(array_keys($packagistPackages), array_keys($packages));
        $sharedVendorPackages = $this->splitPackagesByVendor(
            array_combine($sharedPackages, array_fill(0, count($sharedPackages), []))
        );

        $i = 0;
        foreach ($sharedVendorPackages as $vendor => $vendorPackages) {
            if (in_array($vendor, $excludeVendorNames, true)) {
                continue;
            }

            foreach ($vendorPackages as $name => $_) {
                $packageName = $vendor . '/' . $name;
                if (!$this->isValidPackageName($packageName)) {
                    continue;
                }

                if (empty($packages[$packageName])) {
                    $packages[$packageName] = $this->getPackageVersionsFromRepository($packageName);
                    if (empty($packages[$packageName])) {
                        continue;
                    }
                }
                if (empty($packagistPackages[$packageName])) {
                    $packagistPackages[$packageName] = $this->packagistRepository->findPackageVersionsByName($packageName);
                    if (empty($packagistPackages[$packageName])) {
                        continue;
                    }
                }

                $packageVersions = [
                    'Local' => array_pop($packages[$packageName]),
                    'Packagist' => array_pop($packagistPackages[$packageName]),
                ];
                $output->writeln(' - ' . $packageName);
                foreach ($packageVersions as $repository => $package) {
                    $output->writeln('   - ' . $repository);
                    $output->writeln('      - url: ' . ($package->getSourceUrl() ?: $package->getDistUrl()));
                    if (!empty($package->getAuthors())) {
                        foreach ($package->getAuthors() as $author) {
                            foreach ($author as $property => $value) {
                                $output->writeln('      - ' . $property . ': ' . $value);
                            }
                        }
                    }
                }
                $output->writeln('');
                $i++;
            }
        }

        $output->writeln($i . ' shared packages found');

        return 0;
    }
}
