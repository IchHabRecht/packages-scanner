<?php
namespace IchHabRecht\PackagesScanner\Command\Package;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends AbstractBaseCommand
{
    /**
     * Configure command
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('package:validate')
            ->setDescription('Validate package names')
            ->setHelp('This command validates the package names found in the provided repository');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packages = $this->getPackagesFromRepository($input, $output);

        $i = 0;
        foreach ($packages as $packageName => $packageVersions) {
            if (!$this->isValidPackageName($packageName)) {
                $output->writeln(' - ' . $packageName);
                if (empty($packageVersions)) {
                    $packageVersions = $this->getPackageVersionsFromRepository($packageName);
                }
                if (!empty($packageVersions)) {
                    $package = array_pop($packageVersions);
                    $output->writeln('   - url: ' . ($package->getSourceUrl() ?: $package->getDistUrl()));
                    if (!empty($package->getAuthors())) {
                        foreach ($package->getAuthors() as $author) {
                            foreach ($author as $property => $value) {
                                $output->writeln('   - ' . $property . ': ' . $value);
                            }
                        }
                    }
                }
                $output->writeln('');
                $i++;
            }
        }

        $output->writeln($i . ' invalid packages found');

        return 0;
    }
}
