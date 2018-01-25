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

    public function partitionArray($items, $perPage) {
        $listLength = count($items);
        if ($perPage > $listLength) {
            return [$items];
        }
        $partitionLength = floor($listLength / $perPage);
        $partrem = $listLength % $perPage;
        $partition = array();
        $mark = 0;
        for ($px = 0; $px < $perPage; $px++) {
            $incr = ($px < $partrem) ? $partitionLength + 1 : $partitionLength;
            $partition[$px] = array_slice( $items, $mark, $incr );
            $mark += $incr;
        }

        return $partition;
    }

    private function prepare()
    {
        $this->queryPages = array_chunk($this->queryStrings, $this->concurrency);
        $this->totalPages = count($this->queryStrings);
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

        if (!isset($this->queryPages[$this->currentPage])) {
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
