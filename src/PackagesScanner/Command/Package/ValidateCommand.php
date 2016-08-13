<?php
namespace IchHabRecht\PackagesScanner\Command\Package;

use IchHabRecht\PackagesScanner\Command\AbstractBaseCommand;
use IchHabRecht\PackagesScanner\Package\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends AbstractBaseCommand
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
        $repositoryUrl = $input->getArgument('repository-url');
        $output->writeln('Scanning packages at ' . $repositoryUrl);
        $output->writeln('');

        $packages = $this->packageRepository->findAllPackagesFromRepository($repositoryUrl);

        foreach ($packages as $packageName => $packagePackages) {
            if ($this->isInvalidPackageName($packageName)) {
                $output->writeln(' - ' . $packageName);
                $packageInformation = array_pop($packagePackages);
                $output->writeln('   - url: ' . ($packageInformation['source']['url'] ?? $packageInformation['dist']['url']));
                if (!empty($packageInformation['authors'])) {
                    foreach ($packageInformation['authors'] as $author) {
                        foreach ($author as $property => $value) {
                            $output->writeln('   - ' . $property . ': ' . $value);
                        }
                    }
                }
                $output->writeln('');
            }
        }

        return 0;
    }

    /**
     * @param string $packageName
     * @return bool
     */
    protected function isInvalidPackageName($packageName)
    {
        return empty($packageName)
        || !preg_match('{^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9]([_.-]?[a-z0-9]+)*$}', $packageName)
        || preg_match('{\.json$}', $packageName);
    }
}
