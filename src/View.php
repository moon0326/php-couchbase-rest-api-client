<?php

namespace Moon\CouchbaseRestClient;

class View
{
    private $designDocumentName;
    private $viewName;

    /**
     * View constructor.
     * @param $designDocumentName
     * @param $viewName
     */
    public function __construct($designDocumentName, $viewName)
    {
        $this->designDocumentName = $designDocumentName;
        $this->viewName = $viewName;
    }

    public function getDesignDocumentName()
    {
        return $this->designDocumentName;
    }

    public function getViewName()
    {
        return $this->viewName;
    }
}
