<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DataProviderTest extends KernelTestCase
{
    // @var UserPropertyService
    private $service;

    public function setUp()
    {
        self::bootKernel();

        $this->service = self::$kernel->getContainer()->get(\App\Service\DataProvider::class);
    }

    public function testGetPostsFromWordPress()
    {
        $this->service->setDomain('https://www.sonymusic.com');

        $output = $this->service->getPostsFromWordPress();

        $this->assertIsArray($output);
        $this->assertGreaterThan(0, count($output));

        // ...
    }
}
