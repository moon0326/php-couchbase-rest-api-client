<?php

namespace Moon\CouchbaseRestClient;

use GuzzleHttp\Client;
use Exception;

class N1qlClient
{
    /**
     * @var Client
     */
    private $client;
    private $n1qlHost;
    private $username;
    private $password;
    private $bucketName;

    /**
     * N1qlQuerier constructor.
     * @param Client $client
     * @param $n1qlHost
     * @param $username
     * @param $password
     * @param $bucketName
     */
    public function __construct(Client $client, $n1qlHost, $username, $password, $bucketName)
    {
        $this->client = $client;
        $this->n1qlHost = $n1qlHost;
        $this->username = $username;
        $this->password = $password;
        $this->bucketName = $bucketName;
    }

    public function getBucketName()
    {
        return $this->bucketName;
    }

    public function queryN1qlAsync($queryString)
    {
        if ($this->n1qlHost === null) {
            throw new Exception("n1ql host is empty. Please check COUCHBASE_N1Ql_ENDPOINT or --n1ql-endpoint option value.");
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

    public function queryN1ql($queryString)
    {
        if ($this->n1qlHost === null) {
            throw new Exception("n1ql host is empty. Please check COUCHBASE_N1Ql_ENDPOINT or --n1ql-endpoint option value.");
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
}
