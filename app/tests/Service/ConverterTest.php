<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConverterTest extends KernelTestCase
{
    // @var UserPropertyService
    private $service;

    public function setUp()
    {
        self::bootKernel();

        $this->service = self::$kernel->getContainer()->get(\App\Service\Converter::class);
    }

    public function testConvertToMarkDown()
    {
        $items = array();

        $output = $this->service->convertToMarkDown($items);

        $this->assertTrue($output);
        //$this->assertIsArray($output);
        //$this->assertGreaterThan(0, count($output));

        // ...
    }
}
