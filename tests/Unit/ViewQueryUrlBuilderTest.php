<?php

namespace Moon\CouchbaseRestClient\Tests\Unit;

use Moon\CouchbaseRestClient\Tests\TestCase;
use Moon\CouchbaseRestClient\ViewQueryUrlBuilder;

class ViewQueryUrlBuilderTest extends TestCase
{
    /**
     * @var ViewQueryUrlBuilder
     */
    private $instance;

    public function setUp()
    {
        $this->instance = new ViewQueryUrlBuilder("username", "password", "designDocument", "viewName");
    }

    /**
     * @test
     * Given no additional parameters provided
     * When build gets called
     * Then it should build the default view url without any parameters present
     */
    public function it_builds_default_url()
    {
        $url = $this->instance->build("https://test.com", "default");
        $expected = 'https://username:password@test.com/default/_design/designDocument/_view/viewName?reduce=false&pretty=false&';
        $this->assertEquals($expected, $url);
    }

    /**
     * @test
     */
    public function it_builds_with_parameters()
    {
        $this->instance->key('test');
        $this->instance->reduce(false);
        $url = $this->instance->build('https://test.com', 'default');
        $expected = 'https://username:password@test.com/default/_design/designDocument/_view/viewName?key=%22test%22&reduce=false&pretty=false&';
        $this->assertEquals($expected, $url);
    }

    /**
     * @test
     * Given a url without a scheme
     * When it is passed to build method
     * Then the build method should use the default https scheme
     */
    public function it_builds_with_default_https_scheme_if_not_provided()
    {
        $url = $this->instance->build("test.com", "default");
        $parsedUrl = parse_url($url);
        $this->assertArrayHasKey('scheme', $parsedUrl);
        $this->assertEquals('https', $parsedUrl['scheme']);
    }

    /**
     * @test
     */
    public function it_sets_group_level()
    {
        $this->instance->groupLevel(1);
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "group_level=1"));
    }

    /**
     * @test
     */
    public function it_sets_group()
    {
        $this->instance->group();
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "group=1"));
    }


    /**
     * @test
     */
    public function it_sets_key()
    {
        $this->instance->key('key');
        $url = $this->instance->build("test.com", "default");
        // values get escaped %22 = "
        $this->assertNotFalse(strpos($url, "key=%22key%22"));
    }

    /**
     * @test
     */
    public function it_sets_keys()
    {
        $this->instance->keys(['a','b']);
        $url = $this->instance->build("test.com", "default");
        // %5B%22a%22%2C%22b%22%5D = ["a","b"]
        $this->assertNotFalse(strpos($url, "keys=%5B%22a%22%2C%22b%22%5D"));
    }


    /**
     * @test
     */
    public function it_sets_limit()
    {
        $this->instance->limit(1);
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "limit=1"));
    }


    /**
     * @test
     */
    public function it_sets_skip()
    {
        $this->instance->skip(1);
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "skip=1"));
    }


    /**
     * @test
     */
    public function it_sets_reduce()
    {
        $this->instance->reduce(true);
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "reduce=true"));
    }


    /**
     * @test
     */
    public function it_sets_start_key()
    {
        $this->instance->startKey('test');
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "startkey=%22test%22"));
    }


    /**
     * @test
     */
    public function it_sets_end_key()
    {
        $this->instance->endKey('test');
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "endkey=%22test%22"));
    }


    /**
     * @test
     */
    public function it_sets_start_key_doc_id()
    {
        $this->instance->startKeyDocId('test');
        $url = $this->instance->build("test.com", "default");
        // notice that we don't escape startkey_docid
        // don't ask me. That's how CB works :)
        $this->assertNotFalse(strpos($url, "startkey_docid=test"));
    }


    /**
     * @test
     */
    public function it_sets_both_start_key_and_end_key()
    {
        $this->instance->range('start', 'end');
        $url = $this->instance->build("test.com", "default");
        $this->assertNotFalse(strpos($url, "startkey=%22start%22&endkey=%22end%22"));
    }
}
