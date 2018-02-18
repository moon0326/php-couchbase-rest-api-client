<?php

namespace Moon\CouchbaseRestClient\Tests\Unit;

use Moon\CouchbaseRestClient\Tests\TestCase;
use Moon\CouchbaseRestClient\ViewQueryUrlBuilder;

class ViewPaginatorTest extends TestCase
{
    /**
     * @var ViewQueryUrlBuilder
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new ViewQueryUrlBuilder("username", "password", "designDocument", "viewName");
    }

}
