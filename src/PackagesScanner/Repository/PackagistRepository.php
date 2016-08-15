<?php
namespace IchHabRecht\PackagesScanner\Repository;

class PackagistRepository extends Repository
{
    /**
     * @var string
     */
    private $repositoryUrl = 'https://packagist.org/';

    public function __construct()
    {
        parent::__construct($this->repositoryUrl);
    }
}
