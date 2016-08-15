<?php
namespace IchHabRecht\PackagesScanner\Test\Command;

use Composer\Package\CompletePackage;
use IchHabRecht\PackagesScanner\Repository\PackagistRepository;
use IchHabRecht\PackagesScanner\Repository\Repository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractCommandTestCase extends TestCase
{
    /**
     * @var BufferedOutput
     */
    protected $output;

    /**
     * @var string
     */
    private $repositoryUrl = 'https://example.org';

    protected function setUp()
    {
        parent::setUp();
        $this->output = new BufferedOutput();
    }

    /**
     * @return ObjectProphecy
     */
    protected function getInputProphecy()
    {
        $inputProphecy = $this->prophesize(InputInterface::class);
        $inputProphecy->bind(Argument::type(InputDefinition::class))->shouldBeCalled();
        $inputProphecy->isInteractive()->willReturn(false);
        $inputProphecy->hasArgument('command')->willReturn(false);
        $inputProphecy->validate()->shouldBeCalled();
        $inputProphecy->getArgument('repository-url')->willReturn($this->repositoryUrl);

        return $inputProphecy;
    }

    /**
     * @param array $packages
     * @return ObjectProphecy
     */
    protected function getRepositoryProphecy(array $packages)
    {
        $repositoryProphecy = $this->prophesize(Repository::class);
        $repositoryProphecy->findAllPackagesFromRepository()->willReturn($packages);
        $repositoryProphecy->findPackageVersionsByName(Argument::any())->will(function ($arguments) {
            return [
                new CompletePackage($arguments[0], '1.0.0', '1.0.0'),
            ];
        });

        return $repositoryProphecy;
    }

    /**
     * @param array $packages
     * @return ObjectProphecy
     */
    protected function getPackagistRepositoryProphecy(array $packages)
    {
        $repositoryProphecy = $this->prophesize(PackagistRepository::class);
        $repositoryProphecy->findAllPackagesFromRepository()->willReturn($packages);
        $repositoryProphecy->findPackageVersionsByName(Argument::any())->will(function ($arguments) {
            return [
                new CompletePackage($arguments[0], '1.0.0', '1.0.0'),
            ];
        });

        return $repositoryProphecy;
    }
}
