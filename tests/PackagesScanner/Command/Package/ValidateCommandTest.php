<?php
namespace IchHabRecht\PackagesScanner\Test\Command\Package;

use IchHabRecht\PackagesScanner\Command\Package\ValidateCommand;
use IchHabRecht\PackagesScanner\Test\Command\AbstractCommandTestCase;

class ValidateCommandTest extends AbstractCommandTestCase
{
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
        $validateCommand = new ValidateCommand(null, $this->getRepositoryProphecy($packages)->reveal());
        $validateCommand->run($this->getInputProphecy()->reveal(), $this->output);

        $this->assertContains($expected, $this->output->fetch());
    }
}
