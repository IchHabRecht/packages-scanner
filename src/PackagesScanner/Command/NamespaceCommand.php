<?php
namespace IchHabRecht\PackagesScanner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NamespaceCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('namespace:list')
            ->setDescription('List unregistered package namespaces')
            ->setHelp('This command checks your package information for namespaces which are not registered at packagist.org');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
