<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Vendor;

use IchHabRecht\PackagesScanner\Command\Vendor\ListCommand;
use IchHabRecht\PackagesScanner\Test\Command\AbstractCommandTestCase;

class ListCommandTest extends AbstractCommandTestCase
{
    /**
     * @return array
     */
    public function testListCommandRevealsVendorInformationDataProvider()
    {
        return [
            'Registered vendor names' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'ichhabrecht/packages-scanner' => [],
                    'vendor/package' => [],
                ],
                [
                    ' - ichhabrecht' . "\n" . '   - registered',
                    ' - vendor' . "\n" . '   - registered',
                ],
            ],
            'Unregistered vendor names' => [
                [
                    'ichhabrecht/package' => [],
                    'vendor/package' => [],
                ],
                [
                    'foo/packages-scanner' => [],
                    'bar/package' => [],
                ],
                [
                    ' - ichhabrecht' . "\n" . '   - unregistered',
                    ' - vendor' . "\n" . '   - unregistered',
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
                    ' - ichhabrecht' . "\n" . '   - unregistered',
                    ' - vendor' . "\n" . '   - registered',
                ],
            ],
        ];
    }

    /**
     * @param array $packages
     * @param array $packagistPackages
     * @param array $expected
     *
     * @dataProvider testListCommandRevealsVendorInformationDataProvider
     */
    public function testListCommandRevealsVendorInformation(array $packages, array $packagistPackages, array $expected)
    {
        $inputProphecy = $this->getInputProphecy();
        $inputProphecy->getOption('only-registered')->willReturn(false);
        $inputProphecy->getOption('only-unregistered')->willReturn(false);

        $validateCommand = new ListCommand(
            null,
            $this->getRepositoryProphecy($packages)->reveal(),
            $this->getPackagistRepositoryProphecy($packagistPackages)->reveal()
        );
        $validateCommand->run($inputProphecy->reveal(), $this->output);

        $output = $this->output->fetch();
        foreach ($expected as $needle) {
            $this->assertContains($needle, $output);
        }
    }
}
