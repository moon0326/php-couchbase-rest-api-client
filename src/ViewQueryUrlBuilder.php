<?php

namespace Moon\CouchbaseRestClient;

/**
 * Builders Couchbase View Query URL query string
 *
 * Class ViewQueryBuilder
 * @package Hue\CouchbaseEtl
 */
class ViewQueryUrlBuilder
{
    private $designDocument;
    private $viewName;

    private $parameters = [
        'group' => '',
        'key' => '',
        'keys' => '',
        'limit' => 0,
        'skip' => '0',
        'reduce' => 'false',
        'start_key' => '',
        'end_key' => '',
        'group_level' => '',
        'startkey_docid' => '',
        'pretty' => 'false'
    ];
    private $username;
    private $password;

    /**
     * ViewQueryBuilder constructor.
     * @param $username
     * @param $password
     * @param $designDocument
     * @param $viewName
     */
    public function __construct($username, $password, $designDocument, $viewName)
    {
        $this->designDocument = $designDocument;
        $this->viewName = $viewName;
        $this->username = $username;
        $this->password = $password;
    }

    public function newCopy()
    {
        return new self($this->username, $this->password, $this->designDocument, $this->viewName);
    }

    private function encodeURIComponent($str) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
        return strtr(rawurlencode($str), $revert);
    }

    public function group()
    {
        $this->parameters['group'] = true;
        return $this;
    }

    public function key($key)
    {
        $this->parameters['key'] = json_encode($key);
        return $this;
    }

    public function keys(array $keys)
    {
        $this->parameters['keys'] = json_encode($keys);
        return $this;
    }

    public function limit($limit)
    {
        $this->parameters['limit'] = $limit;
        return $this;
    }

    public function skip($skip)
    {
        $this->parameters['skip'] = $skip;
        return $this;
    }

    public function reduce($boolean)
    {
        $this->parameters['reduce'] = json_encode($boolean);
        return $this;
    }

    public function startKey($startKey)
    {
        $this->parameters['startkey'] = json_encode($startKey);
        return $this;
    }

    public function endKey($endKey)
    {
        $this->parameters['endkey'] = json_encode($endKey);
        return $this;
    }

    public function startKeyDocId($startKeyDocId)
    {
        $this->parameters['startkey_docid'] = $startKeyDocId;
        return $this;
    }

    public function range($startKey, $endKey)
    {
        $this->startKey($startKey)->endKey($endKey);
        return $this;
    }

    public function groupLevel($groupLevel)
    {
        $this->parameters['group_level'] = $groupLevel;
    }

    public function build($host, $bucketName)
    {
        $credentials = $this->username.':'.$this->password;
        $this->parameters = array_filter($this->parameters);
        $url = implode('/', [
            $host,
            $bucketName,
            '_design',
            $this->designDocument,
            '_view',
            $this->viewName
        ]);

        // manually wire up query string
        // http_build_query won't work as Couchbase expects encodeURI from js
        $queryString = '';
        foreach ($this->parameters as $key => $parameter) {
            $parameter = $this->encodeURIComponent($parameter);
            $queryString .= "{$key}={$parameter}&";
        }

        return "http://".$credentials.'@'.$url."?".$queryString;
    }
}
