<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DataProvider extends BasicService
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * 1. Get the total
     * 2. Loop over the posts per page based on X-WP-Total header
     * 3. Push items into an array
     *
     * @return array
     */
    public function getPostsFromWordPress(string $page = null) : array
    {
      if(!empty($page))
      {
        return $this->getPostsFromWordPressPerPage($page);
      }

      return $this->getPostsFromWordPressAll();
    }

    /**
     *
     * 1. Get the total
     * 2. Loop over the posts per page based on X-WP-Total header
     * 3. Push items into an array
     *
     * @return array
     */
    private function getPostsFromWordPressAll() : array
    {
        $time_start = microtime(true);

        $items = array();

        $output = new ConsoleOutput();

        $totalPosts = $this->getTotalPosts();

        if(!isset($totalPosts['wp-total-posts']))
        {
          $output->writeln('<fg=red>The website doesn\'t look like a WordPress website.</>' . PHP_EOL);

          $this->logger->debug("The website doesn't look like a WordPress website.");

          return $items;
        }

        $posts = intval($totalPosts['wp-total-posts']);
        $pages = intval($totalPosts['wp-total-pages']);

        $perPage = intval(ceil($posts / $pages));

        $this->logger->debug('Total posts: {totalPosts} | Total pages: {totalPages} | Per page: {perPage}', [
            'totalPosts' => $posts,
            'totalPages' => $pages,
            'perPage' => $perPage,
        ]);

        $output->writeln('<info>Loop over the pages</info>' . PHP_EOL);

        // Progress bar
        $progressBar = new ProgressBar($output, $pages);
        $progressBar->start();

        // Loop over the pages
        for($i=1; $i<=$pages; $i++)
        {
          $headers = null;

          $client = HttpClient::create();

          $url = sprintf('%s/wp-json/wp/v2/posts?page=%s', $this->domain, $i);

          $this->logger->debug($url);

          try {

            $response = $client->request('GET', $url);

            $headers = $response->getHeaders();

            $statusCode = $response->getStatusCode();

          } catch (\Throwable $e) {

             array_push($this->errors, array(
              'page' => $i,
              // 'status' => $response->getStatusCode(),
              'url' => $url,
              'error' => $e,
             ));

             $progressBar->advance();

             continue;
          }

          if($statusCode == 200)
          {
            $contentType = $headers['content-type'][0];
            // $contentType = 'application/json'

            // $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'

            // Items per page
            $posts = $response->toArray();
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
            foreach($posts as $pageItem)
            {
              array_push($items, $pageItem);
            }
          }

          $progressBar->advance();
        }

        $progressBar->finish();

        $this->logger->debug("Erros", [
            'errors' => json_encode($this->errors),
        ]);

        $this->logger->debug("Total Items fetched: {totalFetched}", [
            'totalFetched' => count($items),
        ]);

        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start) / 60;

        $this->logger->debug("Execution time (mins): {mins}", [
            'mins' => $execution_time,
        ]);

        return $items;
    }

    /**
     *
     * @return array
     */
    private function getPostsFromWordPressPerPage(string $page) : array
    {
      $time_start = microtime(true);

      $items = array();

      $output = new ConsoleOutput();

      $output->writeln('<info>Inspect the page</info>' . PHP_EOL);

      // Progress bar
      $progressBar = new ProgressBar($output, 1);
      $progressBar->start();

      // Inspect the page
      $headers = null;

      $client = HttpClient::create();

      $url = sprintf('%s/wp-json/wp/v2/posts?%s', $this->domain, $page);

      $this->logger->debug($url);

      try {

        $response = $client->request('GET', $url);

        $headers = $response->getHeaders();

        $statusCode = $response->getStatusCode();

      } catch (\Throwable $e) {

         array_push($this->errors, array(
          'page' => $page,
          // 'status' => $response->getStatusCode(),
          'url' => $url,
          'error' => $e,
         ));
      }

      if($statusCode == 200)
      {
        $contentType = $headers['content-type'][0];
        // $contentType = 'application/json'

        // $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'

        // Items per page
        $posts = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        foreach($posts as $pageItem)
        {
          array_push($items, $pageItem);
        }
      }

      $progressBar->advance();

      $progressBar->finish();

      $this->logger->debug("Erros", [
          'errors' => json_encode($this->errors),
      ]);

      $this->logger->debug("Total Items fetched: {totalFetched}", [
          'totalFetched' => count($items),
      ]);

      $time_end = microtime(true);

      $execution_time = ($time_end - $time_start) / 60;

      $this->logger->debug("Execution time (mins): {mins}", [
          'mins' => $execution_time,
      ]);

      return $items;
    }

    private function getTotalPosts() : array
    {
      $client = HttpClient::create();

      $response = $client->request('GET', sprintf('%s%s', $this->domain, '/wp-json/wp/v2/posts?page=1'));

      $statusCode = $response->getStatusCode();

      if($statusCode == 200)
      {
        $this->logger->debug(json_encode($response->getHeaders()));

        if(!isset($response->getHeaders()['x-wp-total']))
        {
          return array();
        }
        else {
          return array(
            'wp-total-posts' => $response->getHeaders()['x-wp-total'][0],
            'wp-total-pages' => $response->getHeaders()['x-wp-totalpages'][0],
          );
        }
      }

      return array();
    }
}
