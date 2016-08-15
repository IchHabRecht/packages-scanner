<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Vendor;

use IchHabRecht\PackagesScanner\Command\Vendor\RegisterCommand;
use IchHabRecht\PackagesScanner\Test\Command\AbstractCommandTestCase;

class RegisterCommandTest extends AbstractCommandTestCase
{
    /**
     * @return array
     */
    public function testRegisterCommandRevealsPackagesOfUnregisteredVendorsDataProvider()
    {
        return [
            'Registered vendor names' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/packages-scanner' => [],
                    'vendor/foo' => [],
                ],
                [
                    '0 unregistered packages for 0 unregistered vendors found',
                ],
                [],
            ],
            'Unregistered vendor names' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'bar/package' => [],
                    'baz/package' => [],
                ],
                [
                    ' - ichhabrecht' . "\n" . '   - package',
                    ' - vendor' . "\n" . '   - package',
                ],
                [
                    '0 unregistered packages for 0 unregistered vendors found',
                ],
            ],
            'Mixed vendor names' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'foo/packages-scanner' => [],
                    'vendor/package' => [],
                ],
                [
                    ' - ichhabrecht' . "\n" . '   - package',
                ],
                [
                    ' - vendor' . "\n" . '   - package',
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
     * @dataProvider testRegisterCommandRevealsPackagesOfUnregisteredVendorsDataProvider
     */
    public function testRegisterCommandRevealsPackagesOfUnregisteredVendors(
        array $packages, array $packagistPackages, array $expected, array $notExpected
    ) {
        $validateCommand = new RegisterCommand(
            null,
            $this->getRepositoryProphecy($packages)->reveal(),
            $this->getPackagistRepositoryProphecy($packagistPackages)->reveal()
        );
        $validateCommand->run($this->getInputProphecy()->reveal(), $this->output);

        $output = $this->output->fetch();
        foreach ($expected as $needle) {
            $this->assertContains($needle, $output);
        }

        foreach ($notExpected as $needle) {
            $this->assertNotContains($needle, $output);
        }
    }
}
