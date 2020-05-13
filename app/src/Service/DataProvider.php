<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class DataProvider
{
    private $logger;
    private $domain;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->domain = $_ENV['APP_WP_DOMAIN'];
    }

    /**
     *
     * 1. Get the total
     * 2. Loop over the posts per page based on X-WP-Total header
     * 3. Push items into an array
     *
     * @return array
     */
    public function getPostsFromWordPress() : array
    {
        $time_start = microtime(true);

        $items = array();

        $totalPosts = $this->getTotalPosts();

        $posts = intval($totalPosts['wp-total-posts']);
        $pages = intval($totalPosts['wp-total-pages']);

        $perPage = intval(ceil($posts / $pages));

        $this->logger->debug('Total posts: {totalPosts} | Total pages: {totalPages} | Per page: {perPage}', [
            'totalPosts' => $posts,
            'totalPages' => $pages,
            'perPage' => $perPage,
        ]);

        for($i=1; $i<=$pages; $i++)
        {
          $client = HttpClient::create();

          $url = sprintf('%s/wp-json/wp/v2/posts?page=%s', $this->domain, $i);

          $this->logger->debug($url);

          $response = $client->request('GET', $url);

          $statusCode = $response->getStatusCode();

          if($statusCode == 200)
          {
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'

            // $content = $response->getContent();
            // $content = '{"id":521583, "name":"symfony-docs", ...}'

            array_push($items, $response->toArray());
            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
          }
        }

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

        return array(
          'wp-total-posts' => $response->getHeaders()['x-wp-total'][0],
          'wp-total-pages' => $response->getHeaders()['x-wp-totalpages'][0],
        );
      }

      return array();
    }
}
