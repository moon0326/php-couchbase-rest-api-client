<?php

namespace Moon\CouchbaseRestClient;

class CouchbaseDsn
{
    private $username;
    private $password;
    private $bucketName;
    private $viewHost;
    private $n1qlHost;

    /**
     * CouchbaseDsn constructor.
     * @param $viewHost
     * @param $username
     * @param $password
     * @param $bucketName
     */
    public function __construct($viewHost, $username, $password, $bucketName)
    {
        $this->username = $username;
        $this->password = $password;
        $this->bucketName = $bucketName;
        $this->viewHost = $viewHost;
    }

    public function setN1qlHost($n1qlHost)
    {
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
     * @return mixed
     */
    public function getViewHost()
    {
        return $this->viewHost;
    }

    /**
     * @return mixed
     */
    public function getN1qlHost()
    {
        return $this->n1qlHost;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
}
