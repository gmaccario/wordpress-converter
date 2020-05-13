<?php
namespace App\Command;

use App\Service\DataProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

class WPExportPostsToMarkdownCommand extends Command
{
    use LockableTrait;

    protected $logger;
    protected $dataProvider;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:wp-export-posts';

    public function __construct(LoggerInterface $logger, DataProvider $dataProvider)
    {
        $this->logger = $logger;

        $this->dataProvider = $dataProvider;

        parent::__construct();
    }

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

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->logger->debug('Execute {commandName}', [
            'commandName' => self::$defaultName
        ]);

        if (!$this->lock(get_class($this) . getenv('APP_ENV')))
        {
          $output->writeln('<fg=red>The command is already running in another process.</>');

          return 1;
        }

        $results = $this->dataProvider->getPostsFromWordPress();

        $output->writeln("<fg=green>EXPORT!</>");

        return 0;
    }
}
