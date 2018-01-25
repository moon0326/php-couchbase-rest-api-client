<?php

namespace Moon\CouchbaseRestClient;

class ViewPaginator implements \Iterator
{
    private $totalRows = 0;
    private $totalPages = 0;
    private $currentPage = 0;
    private $reduce = false;
    private $prepared = false;

    /**
     * @var
     */
    private $itemsPerPage;
    private $startKeyDocId;

    /**
     * @var CouchbaseRestApiClient
     */
    private $couchbaseRestApi;
    private $designDocument;
    private $viewName;

    private $startKey;
    private $endKey;

    /**
     * DocumentKeysExporter constructor.
     * @param CouchbaseRestApiClient $couchbaseRestApi
     * @param $designDocument
     * @param $viewName
     * @param $itemsPerPage
     */
    public function __construct(CouchbaseRestApiClient $couchbaseRestApi, $designDocument, $viewName, $itemsPerPage = 2000)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->couchbaseRestApi = $couchbaseRestApi;
        $this->designDocument = $designDocument;
        $this->viewName = $viewName;
    }

    public function setStartKey($startKey)
    {
        $this->startKey = $startKey;
    }

    public function setEndKey($endKey)
    {
        $this->endKey = $endKey;
    }

    public function getTotalItems()
    {
        return $this->totalRows;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function prepare()
    {
        $this->count();
        $this->totalPages = (int) ceil($this->totalRows / $this->itemsPerPage);
        $this->prepared = true;
    }

    private function count()
    {
        $count = $this->getResponse(1, 0, true, $this->startKey, $this->endKey);
        $this->totalRows = $count[0]->value;
        $this->reduce = false;
    }

    public function getResponse($limit, $skip, $reduce = false, $startKey = null, $endKey = null, $groupLevel = null)
    {
        $queryBuilder = $this->couchbaseRestApi->createViewQueryBuilder(
            $this->designDocument,
            $this->viewName
        );

        $queryBuilder->limit($limit)
            ->skip($skip)
            ->reduce($reduce)
            ->range($startKey, $endKey);

        if ($groupLevel) {
            $queryBuilder->groupLevel($groupLevel);
        }

        if ($this->startKeyDocId) {
            $queryBuilder->startKeyDocId($this->startKeyDocId);
        }

        $response = $this->couchbaseRestApi->queryView($queryBuilder);

        if (isset($response->rows)) {
            return $response->rows;
        } else {
            return $response;
        }
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        if ($this->currentPage === 0) {
            $skip = $this->itemsPerPage * $this->currentPage;
        } else {
            $skip = 0;
        }

        $items = $this->getResponse($this->itemsPerPage, $skip, false, $this->startKey, $this->endKey);
        if ($items) {
            $lastItem = end($items);
            $this->startKey = $lastItem->key;
            $this->startKeyDocId = $lastItem->id;
        }

        return $items;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->currentPage;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->currentPage;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if (!$this->prepared) {
            $this->prepare();
        }
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->currentPage = 0;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }
}