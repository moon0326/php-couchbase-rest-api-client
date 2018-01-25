<?php

namespace Moon\CouchbaseRestClient;

use GuzzleHttp\Promise;

class ParallelQueryQueue
{
    /**
     * @var CouchbaseApiClient
     */
    private $client;
    private $queryStrings = [];
    private $ready = false;

    /**
     * @var int
     */
    private $concurrency;
    private $currentPage = 0;
    private $totalPages = 0;
    private $queryPages;

    /**
     * ParallelQueryQueue constructor.
     * @param CouchbaseApiClient $client
     * @param int $concurrency
     */
    public function __construct(CouchbaseApiClient $client, $concurrency = 3)
    {
        $this->client = $client;
        $this->concurrency = $concurrency;
    }

    public function push($queryString)
    {
        $this->queryStrings[] = $queryString;
    }

    private function prepare()
    {
        $this->queryPages = array_chunk($this->queryStrings, $this->concurrency);
        $this->totalPages = count($this->queryPages) - 1;
        $this->ready = true;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function next()
    {
        if (!$this->ready) {
            $this->prepare();
        }

        if ($this->currentPage > $this->totalPages) {
            return null;
        }

        $promises = [];
        foreach ($this->queryPages[$this->currentPage] as $query) {
            $promises[] = $this->client->queryN1qlAsync($query);
        }

        $results = [];
        foreach (Promise\unwrap($promises) as $key => $result) {
            $results[] = json_decode((string) $result->getBody())->results;
        }

        $this->currentPage++;
        return $results;
    }

    public function reset()
    {
        $this->currentPage = 0;
    }
}
