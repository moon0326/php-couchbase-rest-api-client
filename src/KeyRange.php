<?php

namespace Moon\CouchbaseRestClient;

class KeyRange
{
    private $startKey;
    private $endKey;

    /**
     * KeyRange constructor.
     * @param array $startKey
     * @param array $endKey
     */
    public function __construct(array $startKey, array $endKey)
    {
        $this->startKey = $startKey;
        $this->endKey = $endKey;
    }

    public static function createFromForm($form)
    {
        $startKey = [$form];
        $endKey = [$form, new \stdClass()];

        return new static($startKey, $endKey);
    }

    public function createFromStartKey(array $startKey)
    {
        $endKey = [$startKey[0], new \stdClass()];
        return new static($startKey, $endKey);
    }

    public function getStartKey()
    {
        if (count($this->startKey) === 0) {
            return null;
        }

        return $this->startKey;
    }

    public function getEndKey()
    {
        if (count($this->endKey) === 0) {
            return null;
        }

        return $this->endKey;
    }
}
