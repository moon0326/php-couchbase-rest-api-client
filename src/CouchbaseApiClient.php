<?php

namespace Moon\CouchbaseRestClient;

/**
 * Interface CouchbaseApiClient
 * @package Hue\CouchbaseEtl\Couchbase
 */
interface CouchbaseApiClient
{
    public function queryN1qlAsync($query);
    public function queryN1ql($query);
    public function queryView(ViewQueryUrlBuilder $viewQueryUrlBuilder);
    public function getBucketName();
}
