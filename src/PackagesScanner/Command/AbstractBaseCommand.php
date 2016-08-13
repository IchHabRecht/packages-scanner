<?php
namespace IchHabRecht\PackagesScanner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractBaseCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->addArgument('repository-url', InputArgument::REQUIRED, 'The repository url to your packages.json file');
    }
}
