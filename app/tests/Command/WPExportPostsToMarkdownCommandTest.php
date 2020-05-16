<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class WPExportPostsToMarkdownCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:wp-converter');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            // pass arguments to the helper
             'wp-domain' => 'https://www.sonymusic.com',
             'conversion-type' => 'posts-to-markdown',
             'page' => 'page=20',

            // prefix the key with two dashes when passing options,
            // e.g: '--some-option' => 'option_value',
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('successfully', $output);

        // ...
    }
}
