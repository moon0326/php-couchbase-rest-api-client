<?php

namespace Moon\CouchbaseRestClient;

/**
 * Builds Couchbase view query REST URL
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

    public function setGroup()
    {
        $this->parameters['group'] = true;
        return $this;
    }

    public function setKey($key)
    {
        $this->parameters['key'] = json_encode($key);
        return $this;
    }

    public function setKeys(array $keys)
    {
        $this->parameters['keys'] = json_encode($keys);
        return $this;
    }

    public function setLimit($limit)
    {
        $this->parameters['limit'] = $limit;
        return $this;
    }

    public function setSkip($skip)
    {
        $this->parameters['skip'] = $skip;
        return $this;
    }

    public function setReduce($boolean)
    {
        $this->parameters['reduce'] = json_encode($boolean);
        return $this;
    }

    public function setStartKey($startKey)
    {
        $this->parameters['startkey'] = json_encode($startKey);
        return $this;
    }

    public function setEndKey($endKey)
    {
        $this->parameters['endkey'] = json_encode($endKey);
        return $this;
    }

    public function setStartKeyDocId($startKeyDocId)
    {
        $this->parameters['startkey_docid'] = $startKeyDocId;
        return $this;
    }

    public function setRange($startKey, $endKey)
    {
        $this->setStartKey($startKey)->setEndKey($endKey);
        return $this;
    }

    public function setGroupLevel($groupLevel)
    {
        $this->parameters['group_level'] = $groupLevel;
    }

    public function build($host, $bucketName)
    {
        $credentials = $this->username.':'.$this->password;
        $this->parameters = array_filter($this->parameters);

        $parsedUrl = parse_url($host);

        if (!isset($parsedUrl['scheme'])) {
            $parsedUrl['scheme'] = 'https';
            $host = $parsedUrl['path'];
        } else {
            $host = $parsedUrl['host'];
        }

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

        return "{$parsedUrl['scheme']}://".$credentials.'@'.$url."?".$queryString;
    }
}