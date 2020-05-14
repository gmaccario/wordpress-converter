<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use League\HTMLToMarkdown\HtmlConverter;

class Converter
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param array $items
     * @return bool
     */
    public function convertToMarkDown(array $items) : bool
    {
        $time_start = microtime(true);

        // @todo as a service in the constructor?
        $converter = new HtmlConverter();

        // Loop over the items
        foreach($items as $item)
        {
          $title = sprintf("<h1>%s</h1>", $item['title']['rendered']);
          $titleMD = $converter->convert($title);

          $content = $item['content']['rendered'];
          $contentMD = $converter->convert($content);

          $excerpt = $item['excerpt']['rendered'];
          $excerptMD = $converter->convert($excerpt);

          //var_dump($title, $titleMD, $content, $contentMD, $excerpt, $excerptMD);

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
