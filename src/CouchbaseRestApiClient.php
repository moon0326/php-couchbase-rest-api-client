<?php

namespace Moon\CouchbaseRestClient;

use GuzzleHttp\Client;
use Exception;

/**
 * Couchbase REST API wrapper
 * Supports only two endpoint at the moment
 * - query view
 * - query n1ql
 *
 * Class CouchbaseRestApi
 * @package Hue\CouchbaseEtl
 */
class CouchbaseRestApiClient
{
    private $username;
    private $password;
    private $bucketName;
    /**
     * @var Client
     */
    private $client;
    private $viewHost;
    private $n1qlHost;

    /**
     * CouchbaseRestApi constructor.
     * @param $host
     * @param $username
     * @param $password
     * @param $bucketName
     * @param null $n1qlHost
     */
    public function __construct($host, $username, $password, $bucketName, $n1qlHost = null)
    {
        $this->viewHost = $host;
        $this->username = $username;
        $this->password = $password;
        $this->bucketName = $bucketName;
        $this->client = new Client();
        $this->n1qlHost = $n1qlHost;
    }

    /**
     * @return mixed
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }

    /**
     * @param $host
     */
    public function setN1qlHost($host)
    {
        $parsedUrl = parse_url($host);
        if (array_key_exists('path', $parsedUrl)) {
            $this->n1qlHost = $host;
        } else {
            $this->n1qlHost = $host . '/service/query';
        }
    }

    /**
     * @param $queryString
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws Exception
     */
    public function queryN1qlAsync($queryString)
    {
        if ($this->n1qlHost === null) {
            throw new Exception("n1ql host is empty. Please set n1ql host by calling setN1qlHost method");
        }
        return $this->client->postAsync($this->n1qlHost, [
            'form_params' => [
                'pretty' => false,
                'statement' => $queryString
            ],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            ]
        ]);
    }

    /**
     * @param $queryString
     * @return mixed
     * @throws Exception
     */
    public function queryN1ql($queryString)
    {
        if ($this->n1qlHost === null) {
            throw new Exception("n1ql host is empty. Please set n1ql host by calling setN1qlHost method");
        }

        $request = $this->client->post($this->n1qlHost, [
            'form_params' => [
                'statement' => $queryString
            ],
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
            ]
        ]);

        $response = (string) $request->getBody();
        return json_decode($response);
    }

    /**
     * @param ViewQueryUrlBuilder $viewQueryUrlBuilder
     * @return mixed
     */
    public function queryView(ViewQueryUrlBuilder $viewQueryUrlBuilder)
    {
        $url = $viewQueryUrlBuilder->build($this->viewHost, $this->bucketName);
        $request = $this->client->get($url);
        $response = (string) $request->getBody();
        return json_decode($response);
    }

    /**
     * @param $designDocument
     * @param $viewName
     * @return ViewQueryUrlBuilder
     */
    public function createViewQueryBuilder($designDocument, $viewName)
    {
        return new ViewQueryUrlBuilder($this->username, $this->password, $designDocument, $viewName);
    }

    /**
     * @param $designDocument
     * @param $viewName
     * @return ViewPaginator
     */
    public function createViewPaginator($designDocument, $viewName)
    {
        return new ViewPaginator($this, $designDocument, $viewName);
    }

    /**
     * @param int $concurrency
     * @return ParallelQueryQueue
     */
    public function createParallelQueryQueue($concurrency = 3)
    {
        return new ParallelQueryQueue($this, $concurrency);
    }
}
