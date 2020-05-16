<?php
namespace App\Command;

use App\Service\DataProvider;
use App\Service\Converter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\LockableTrait;

class WordPressConverterCommand extends Command
{
    use LockableTrait;

    protected $logger;

    protected $dataProvider;

    protected $converter;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:wp-converter';

    public function __construct(LoggerInterface $logger, DataProvider $dataProvider, Converter $converter)
    {
        $this->logger = $logger;

        $this->dataProvider = $dataProvider;

        $this->converter = $converter;

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

          ->addArgument('wp-domain', InputArgument::REQUIRED, 'WordPress domain')
          // conversionType: posts-to-markdown, ...
          ->addArgument('conversion-type', InputArgument::REQUIRED, 'Conversion type: posts-to-markdown, ...')
          ->addArgument('page', InputArgument::OPTIONAL, 'Page to inspect (page=19).')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->logger->debug('======================================');
        $this->logger->debug('Execute {commandName}', [
            'commandName' => self::$defaultName
        ]);

        // Get input arguments
        $conversionType = $input->getArgument('conversion-type');
        $wpDomain = $this->addScheme($input->getArgument('wp-domain'));
        $page = $input->getArgument('page');

        $this->dataProvider->setDomain($wpDomain);
        $this->converter->setDomain($wpDomain);

        if (!$this->lock(get_class($this) . getenv('APP_ENV')))
        {
          $output->writeln('<fg=red>The command is already running in another process.</>');

          return 1;
        }

        $items = array();

        switch($conversionType)
        {
            case 'posts-to-markdown':
              $items = $this->dataProvider->getPostsFromWordPress($page);
              break;
            default:
              break;
        }

        if(count($this->dataProvider->getErrors()) > 0)
        {
          $output->writeln(PHP_EOL);
          $output->writeln('<fg=red>Error occurred, check the log (if activated!).</>');

          foreach($this->dataProvider->getErrors() as $index => $error)
          {
            $this->logger->debug('Error occurred at page  {page} | {url} | {error}', [
                'page' => $error['page'],
                'url' => $error['url'],
                'error' => json_encode($error),
            ]);
          }
        }

        // @todo
        // $this->listener->store($items)

        if(count($items) > 0)
        {
          $this->converter->convertToMarkDown($items);

          $output->writeln(PHP_EOL);
          $output->writeln(sprintf("<fg=green>Exported %s items successfully!</>", count($items)));
        }
        else{
          $output->writeln(PHP_EOL);
          $output->writeln(sprintf("<fg=cyan>No items to export.</>", count($items)));
        }

        return 0;
    }

    private function addScheme(string $url, string $scheme = 'http://') : string
    {
      return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
    }
}
