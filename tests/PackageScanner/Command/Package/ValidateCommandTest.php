<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Package;

use IchHabRecht\PackagesScanner\Command\Package\ValidateCommand;
use IchHabRecht\PackagesScanner\Repository\Repository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class ValidateCommandTest extends TestCase
{
    /**
     * @var string
     */
    private $repositoryUrl = 'https://example.org';

    /**
     * @return array
     */
    public function testValidateCommandFindsInvalidPackageNamesDataProvider()
    {
        return [
            'Valid package names' => [
                [
                    'vendor/package' => [],
                    'vendor-suffix/package-suffix' => [],
                    'prefix-vendor-suffix/prefix-package-suffix' => [],
                ],
                '0 invalid packages found',
            ],
            'Invalid package names' => [
                [
                    'Vendor/package' => [],
                    'vendor/Package' => [],
                    'vendor--suffix/package' => [],
                    'vendor/package--suffix' => [],
                ],
                '4 invalid packages found',
            ],
            'Mixed package names' => [
                [
                    'vendor/package' => [],
                    'vendor-suffix/package-suffix' => [],
                    'vendor--suffix/package' => [],
                    'vendor/package--suffix' => [],
                ],
                '2 invalid packages found',
            ],
        ];
    }

    /**
     * @param array $packages
     * @param $expected
     *
     * @dataProvider testValidateCommandFindsInvalidPackageNamesDataProvider
     */
    public function testValidateCommandFindsInvalidPackageNames(array $packages, $expected)
    {
        $repositoryProphecy = $this->prophesize(Repository::class);
        $repositoryProphecy->findAllPackagesFromRepository()->willReturn($packages);
        $repositoryProphecy->findPackageVersionsByName(Argument::any())->willReturn([]);

        $validateCommand = new ValidateCommand(null, $repositoryProphecy->reveal());

        $inputProphecy = $this->prophesize(InputInterface::class);
        $inputProphecy->bind(Argument::type(InputDefinition::class))->shouldBeCalled();
        $inputProphecy->isInteractive()->willReturn(false);
        $inputProphecy->hasArgument('command')->willReturn(false);
        $inputProphecy->validate()->shouldBeCalled();
        $inputProphecy->getArgument('repository-url')->willReturn($this->repositoryUrl);

        $output = new BufferedOutput();

        $validateCommand->run($inputProphecy->reveal(), $output);

        $this->assertContains($expected, $output->fetch());
    }
}
