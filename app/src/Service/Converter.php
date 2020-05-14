<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class Converter
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     *
     * @return bool
     */
    public function convertToMarkDown() : bool
    {
        $time_start = microtime(true);






        $time_end = microtime(true);

        $execution_time = ($time_end - $time_start) / 60;

        $this->logger->debug("Execution time (mins): {mins}", [
            'mins' => $execution_time,
        ]);

        return true;
    }
}
