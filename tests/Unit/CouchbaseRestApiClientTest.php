<?php

namespace Moon\CouchbaseRestClient\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Moon\CouchbaseRestClient\CouchbaseRestApiClient;
use Moon\CouchbaseRestClient\ParallelQueryQueue;
use Moon\CouchbaseRestClient\Tests\TestCase;
use Mockery as m;
use Exception;
use Moon\CouchbaseRestClient\ViewPaginator;
use Moon\CouchbaseRestClient\ViewQueryUrlBuilder;

class CouchbaseRestApiClientTest extends TestCase
{
    /**
     * @param MockInterface $clientMock
     * @return CouchbaseRestApiClient
     */
    public function getInstance(MockInterface $clientMock)
    {
        return new CouchbaseRestApiClient('host', 'username', 'password', 'bucketName', $clientMock);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_n1ql_host_is_not_set()
    {
        $this->setExpectedException(Exception::class);
        $mock = m::mock(Client::class);
        $instance = $this->getInstance($mock);
        $instance->queryN1ql('select * from..');
    }

    /**
     * @test
     */
    public function it_returns_bucket_name()
    {
        $mock = m::mock(Client::class);
        $instance = $this->getInstance($mock);
        $this->assertEquals('bucketName', $instance->getBucketName());
    }

    /**
     * @test
     */
    public function it_queries_n1ql_synchronously()
    {
        $mock = m::mock(Client::class);
        $mock->shouldReceive('post')->once()->andReturnUsing(function($host, $params) {
            $this->assertEquals('http://host', $host);
            $this->assertArrayHasKey('form_params', $params);
            $this->assertArrayHasKey('headers', $params);
            $this->assertArrayHasKey('pretty', $params['form_params']);
            $this->assertArrayHasKey('statement', $params['form_params']);
            $this->assertArrayHasKey('Authorization', $params['headers']);

            $this->assertEquals('false', $params['form_params']['pretty']);
            $this->assertEquals('select * from testBucket', $params['form_params']['statement']);
            $this->assertEquals('Basic '.base64_encode('username:password'), $params['headers']['Authorization']);
            return new Response(200, [], json_encode('result'));
        });
        $instance = $this->getInstance($mock);
        $instance->setN1qlHost('http://host');
        $result = $instance->queryN1ql('select * from testBucket');
        $this->assertEquals('result', $result);
    }

    /**
     * @test
     */
    public function it_queries_n1ql_asynchronously()
    {
        $mock = m::mock(Client::class);
        $mock->shouldReceive('postAsync')->once()->andReturnUsing(function($host, $params) {
            $this->assertEquals('http://host', $host);
            $this->assertArrayHasKey('form_params', $params);
            $this->assertArrayHasKey('headers', $params);
            $this->assertArrayHasKey('pretty', $params['form_params']);
            $this->assertArrayHasKey('statement', $params['form_params']);
            $this->assertArrayHasKey('Authorization', $params['headers']);

            $this->assertEquals('false', $params['form_params']['pretty']);
            $this->assertEquals('select * from testBucket', $params['form_params']['statement']);
            $this->assertEquals('Basic '.base64_encode('username:password'), $params['headers']['Authorization']);
        });
        $instance = $this->getInstance($mock);
        $instance->setN1qlHost('http://host');
        $instance->queryN1qlAsync('select * from testBucket');
    }

    /**
     * @test
     */
    public function it_queries_view()
    {
        $mock = m::mock(Client::class);
        $mock->shouldReceive('get')->once()->andReturnUsing(function($viewUrl) {
            $expected = 'https://username:password@host/bucketName/_design/designDocument/_view/viewName?reduce=false&pretty=false&';
            $this->assertEquals($expected, $viewUrl);
            return new Response(200, [], json_encode('result'));
        });
        $instance = $this->getInstance($mock);
        $instance->setN1qlHost('http://host');
        $viewQueryBuilder = new ViewQueryUrlBuilder('username', 'password', 'designDocument', 'viewName');
        $result = $instance->queryView($viewQueryBuilder);
        $this->assertEquals('result', $result);
    }

    /**
     * @test
     */
    public function it_creates_viewQueryBuilder_with_username_and_password_from_itself()
    {
        $mock = m::mock(Client::class);
        $instance = $this->getInstance($mock);
        $viewQueryBuilder = $instance->createViewQueryBuilder('designDocument', 'viewName');
        $url = $viewQueryBuilder->build('host', 'bucketName');
        $this->assertNotFalse(strpos($url, 'https://username:password@host'));
    }

    /**
     * @test
     */
    public function it_creates_viewPaginator()
    {
        $mock = m::mock(Client::class);
        $instance = $this->getInstance($mock);
        $viewPaginator = $instance->createViewPaginator('documentName', 'viewName');
        $this->assertInstanceOf(ViewPaginator::class, $viewPaginator);
    }

    /**
     * @test
     */
    public function it_creates_parallelQueryQueue()
    {
        $mock = m::mock(Client::class);
        $instance = $this->getInstance($mock);
        $viewPaginator = $instance->createParallelQueryQueue();
        $this->assertInstanceOf(ParallelQueryQueue::class, $viewPaginator);
    }
}
