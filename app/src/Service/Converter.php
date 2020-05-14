<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use League\HTMLToMarkdown\HtmlConverter;

class Converter
{
    private $logger;

    private $filesystem;

    private $kernel;

    public function __construct(KernelInterface $kernel, LoggerInterface $logger, Filesystem $filesystem)
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

        // @todo as a service in the constructor?
        $converter = new HtmlConverter();

        $path = sprintf('%s/var/markdown', $this->kernel->getProjectDir());

        if(!$this->filesystem->exists($path))
        {
          $this->filesystem->mkdir($path);
        }

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
          $this->filesystem->appendToFile($filepath, $titleMD);
          $this->filesystem->appendToFile($filepath, $contentMD);
          $this->filesystem->appendToFile($filepath, $excerptMD);

          $this->logger->debug("Title {title} | Content: {content} | Excerpt: {excerpt}", [
              'title' => $titleMD,
              'content' => $contentMD,
              'excerpt' => $excerptMD,
          ]);
        }

        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start) / 60;

        $this->logger->debug("Execution time (mins): {mins}", [
            'mins' => $execution_time,
        ]);

        return true;
    }
}
