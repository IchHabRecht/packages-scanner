<?php
namespace IchHabRecht\PackagesScanner\Command\Vendor;

use IchHabRecht\PackagesScanner\Package\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
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
            ->setName('vendor:validate')
            ->setDescription('Validate package namespaces')
            ->setHelp('This command validates the package namespaces found in the provided repository')
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

        $packages = $this->packageRepository->findAllPackagesFromRepository($repositoryUrl);

        foreach ($packages as $packageName => $packagePackages) {
            if (false === strpos($packageName, '/')) {
                $output->writeln(' - ' . $packageName);
            }
        }

        return 0;
    }
}
