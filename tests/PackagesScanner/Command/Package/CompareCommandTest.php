<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Package;

use IchHabRecht\PackagesScanner\Command\Package\CompareCommand;
use IchHabRecht\PackagesScanner\Test\Command\AbstractCommandTestCase;

class CompareCommandTest extends AbstractCommandTestCase
{
    /**
     * @return array
     */
    public function testRegisterCommandRevealsUnregisteredPackagesDataProvider()
    {
        return [
            'Different packages' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/foo' => [],
                    'vendor/bar' => [],
                ],
                [
                    '0 shared packages found',
                ],
                [],
            ],
            'Shared packages' => [
                [
                    'ichhabrecht/foo' => [],
                    'ichhabrecht/package' => [],
                    'vendor/bar' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/foo' => [],
                    'vendor/bar' => [],
                ],
                [
                    ' - ichhabrecht/foo',
                    ' - vendor/bar',
                    '2 shared packages found',
                ],
                [
                    ' - ichhabrecht/packages',
                    ' - vendor/packages',
                ],
            ],
        ];
    }

    /**
     * @param array $packages
     * @param array $packagistPackages
     * @param array $expected
     * @param array $notExpected
     *
     * @dataProvider testRegisterCommandRevealsUnregisteredPackagesDataProvider
     */
    public function testRegisterCommandRevealsUnregisteredPackages(
        array $packages, array $packagistPackages, array $expected, array $notExpected
    ) {
        $inputProphecy = $this->getInputProphecy();
        $inputProphecy->getOption('exclude-vendor')->willReturn('');
        $inputProphecy->getOption('only-different')->willReturn(false);

        $validateCommand = new CompareCommand(
            null,
            $this->getRepositoryProphecy($packages)->reveal(),
            $this->getPackagistRepositoryProphecy($packagistPackages)->reveal()
        );
        $validateCommand->run($inputProphecy->reveal(), $this->output);

        $output = $this->output->fetch();
        foreach ($expected as $needle) {
            $this->assertContains($needle, $output);
        }

        foreach ($notExpected as $needle) {
            $this->assertNotContains($needle, $output);
        }
    }
}
