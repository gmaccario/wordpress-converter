<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WPExportPostsToMarkdownCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:wp-export-posts';

    /*public function __construct()
    {
        parent::__construct();
    }*/

    protected function configure()
    {
      $this
          // the short description shown while running "php bin/console list"
          ->setDescription('Export WordPress posts to markdown.')

          // the full command description shown when running the command with
          // the "--help" option
          ->setHelp('This command allows you to export WordPress posts to markdown files.')
          // ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
        echo "EXPORT!";

        return 0;
    }
}
