<?php

namespace Moon\CouchbaseRestClient\Tests\Unit;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Mockery as m;
use Moon\CouchbaseRestClient\CouchbaseRestApiClient;
use Moon\CouchbaseRestClient\ParallelQueryQueue;
use Moon\CouchbaseRestClient\Tests\TestCase;

class ParallelQueryQueueTest extends TestCase
{

    public function getInstance($concurrency = 3, m\MockInterface $mock = null)
    {
        if (!$mock) {
            $mock = m::mock(CouchbaseRestApiClient::class);
            $mock->shouldReceive('queryN1qlAsync');
        }
        return new ParallelQueryQueue($mock, $concurrency);
    }

    /**
     * @test
     * prepared means that totalPages and pushed queries are chunked into multiple arrays
     * for parallel processing.
     */
    public function it_prepares()
    {
        $instance = $this->getInstance(3);
        $instance->push("query1");
        $instance->push("query2");
        $instance->push("query3");
        $this->assertEquals(0, $instance->getTotalPages());

        $instance = $this->getInstance(3);
        $instance->push("query1");
        $instance->push("query2");
        $instance->push("query3");
        $instance->push("query4");
        $this->assertEquals(1, $instance->getTotalPages());
    }

    /**
     * @test
     */
    public function it_executes_queries()
    {
        $mock = m::mock(CouchbaseRestApiClient::class);
        $returnUsing = function () {
            $promise = new Promise(function() use (&$promise) {
                $result = (object) [
                    'results'=>  uniqid()
                ];
                $response = new Response(200, [], json_encode($result));
                return $promise->resolve($response);
            });
            return $promise;
        };

        $mock->shouldReceive('queryN1qlAsync')->andReturnUsing($returnUsing);
        $mock->shouldReceive('queryN1qlAsync')->andReturnUsing($returnUsing);
        $mock->shouldReceive('queryN1qlAsync')->andReturnUsing($returnUsing);
        $mock->shouldReceive('queryN1qlAsync')->andReturnUsing($returnUsing);

        $instance = $this->getInstance(3, $mock);
        $instance->push("query1");
        $instance->push("query2");
        $instance->push("query3");
        $instance->push("query3");
        $results = [];
        $results = array_merge($results, $instance->next());
        $results = array_merge($results, $instance->next());
        $this->assertCount(4, $results);
        $this->assertCount(4, array_unique($results));
    }
}
