<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Package;

use IchHabRecht\PackagesScanner\Command\Package\RegisterCommand;
use IchHabRecht\PackagesScanner\Test\Command\AbstractCommandTestCase;

class RegisterCommandTest extends AbstractCommandTestCase
{
    /**
     * @return array
     */
    public function testRegisterCommandRevealsUnregisteredPackagesDataProvider()
    {
        return [
            'Registered packages' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    '0 unregistered packages found',
                ],
                [],
            ],
            'Unregistered packages' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/foo' => [],
                    'vendor/bar' => [],
                ],
                [
                    ' - ichhabrecht/package',
                    ' - vendor/package',
                    '2 unregistered packages found',
                ],
                [
                ],
            ],
            'Mixed packages' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                    'vendor/foo' => [],
                ],
                [
                    'ichhabrecht/foo' => [],
                    'vendor/package' => [],
                ],
                [
                    ' - ichhabrecht/package',
                    ' - vendor/foo',
                    '2 unregistered packages found',
                ],
                [
                    ' - vendor/package',
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

        $validateCommand = new RegisterCommand(
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
