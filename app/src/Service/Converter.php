<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use League\HTMLToMarkdown\HtmlConverter;

class Converter extends BasicService
{
    private $logger;
    private $filesystem;
    private $kernel;

    public function __construct(LoggerInterface $logger, KernelInterface $kernel, Filesystem $filesystem)
    {
        $this->logger = $logger;

        $this->filesystem = $filesystem;

        $this->kernel = $kernel;
    }

    /**
     *
     * @param array $items
     * @return bool
     */
    public function convertToMarkDown(array $items) : bool
    {
        if(0 == count($items))
        {
          return false;
        }

        $time_start = microtime(true);

        $output = new ConsoleOutput();

        $converter = new HtmlConverter();

        $parseUrl = parse_url($this->domain);

        $path = sprintf('%s/var/markdown/%s', $this->kernel->getProjectDir(), (isset($parseUrl['host']) ? $parseUrl['host'] : $parseUrl['path']));

        $this->logger->debug("Path: {path}", [
            'path' => $path,
        ]);

        if(!$this->filesystem->exists($path))
        {
          $this->filesystem->mkdir($path);
        }

        $output->writeln(PHP_EOL);
        $output->writeln('<info>Loop over the items</info>' . PHP_EOL);

        // Progress bar
        $progressBar = new ProgressBar($output, count($items));
        $progressBar->start();

        // Loop over the items
        foreach($items as $item)
        {
          $title = sprintf("<h1>%s</h1>", $item['title']['rendered']);
          $titleMD = $converter->convert($title);

          $content = $item['content']['rendered'];
          $contentMD = $converter->convert($content);

          $excerpt = $item['excerpt']['rendered'];
          $excerptMD = $converter->convert($excerpt);

          $slug = $item['slug'];

          $filepath = sprintf('%s/%s.md', $path, $slug);

          // Store on file
          $this->filesystem->appendToFile($filepath, $titleMD . PHP_EOL);
          // $this->filesystem->appendToFile($filepath, $excerptMD . PHP_EOL);
          $this->filesystem->appendToFile($filepath, strip_tags($contentMD) . PHP_EOL);

          $this->logger->debug("slug {slug}", [
              'slug' => $slug,
          ]);

          $progressBar->advance();

          /*$this->logger->debug("Title {title} | Content: {content} | Excerpt: {excerpt}", [
              'title' => $titleMD,
              'content' => $contentMD,
              'excerpt' => $excerptMD,
          ]);*/
        }

        $progressBar->finish();

        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start) / 60;

        $this->logger->debug("Execution time (mins): {mins}", [
            'mins' => $execution_time,
        ]);

        return true;
    }
}
