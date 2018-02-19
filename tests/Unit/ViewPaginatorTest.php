<?php

namespace Moon\CouchbaseRestClient\Tests\Unit;

use Moon\CouchbaseRestClient\CouchbaseRestApiClient;
use Moon\CouchbaseRestClient\Tests\TestCase;
use Moon\CouchbaseRestClient\ViewPaginator;
use Mockery as m;
use Moon\CouchbaseRestClient\ViewQueryUrlBuilder;

class ViewPaginatorTest extends TestCase
{

    /**
     * @param m\MockInterface|null $mock
     * @param int $itemsPerPage
     * @return ViewPaginator
     */
    public function getInstance(m\MockInterface $mock = null, $itemsPerPage)
    {
        return new ViewPaginator($mock, 'designDocument', 'viewName', $itemsPerPage);
    }

    public function test_total_pages_and_items()
    {
        $mock = m::mock(CouchbaseRestApiClient::class);
        $queryBuilder = new ViewQueryUrlBuilder('username', 'password', 'designDocument', 'viewName');
        $mock->shouldReceive('createViewQueryBuilder')->andReturn($queryBuilder);
        $mock->shouldReceive('queryView')->andReturnUsing(function(ViewQueryUrlBuilder $value) {
            $expected = 'https://username:password@host/bucketName/_design/designDocument/_view/viewName?limit=1&reduce=true&pretty=false&';
            $this->assertEquals($expected, $value->build('host', 'bucketName'));
            return [
                (object) [
                    'value' => 4
                ]
            ];
        });
        $instance = $this->getInstance($mock, 3);
        $this->assertEquals(4, $instance->getTotalItems());
        $this->assertEquals(2, $instance->getTotalPages());
    }

    public function test_getting_items()
    {
        $mock = m::mock(CouchbaseRestApiClient::class);
        $queryBuilder = new ViewQueryUrlBuilder('username', 'password', 'designDocument', 'viewName');
        $mock->shouldReceive('createViewQueryBuilder')->andReturn($queryBuilder);
        // Return for count
        $countRequestResult = [
            (object) [
                'value' => 3
            ]
        ];

        $items = [
            (object) [
                'id' => 1,
                'key' => '1',
                'value' => 'value'
            ],
            (object) [
                'id' => 2,
                'key' => '2',
                'value' => 'value'
            ],
            (object) [
                'id' => 3,
                'key' => '3',
                'value' => 'value'
            ]
        ];
        $firstRequestResult = [$items[0], $items[1]];
        $secondRequestResult = [$items[2]];
        $mock->shouldReceive('queryView')->once()->andReturn($countRequestResult);
        $mock->shouldReceive('queryView')->once()->andReturn($firstRequestResult);
        $mock->shouldReceive('queryView')->once()->andReturnUsing(function(ViewQueryUrlBuilder $builder) use ($secondRequestResult) {
            // Unlike the 1st request, 2nd request should have 'starkey_docid'
            $query = $builder->build('host', 'bucketName');
            $this->assertNotFalse(strpos($query, 'startkey_docid=2'));
            return $secondRequestResult;
        });

        $instance = $this->getInstance($mock, 2);
        $results = [];
        foreach ($instance as $result) {
            $results = array_merge($results, $result);
        }

        $this->assertCount(3, $results);
        $this->assertEquals($items[0], $results[0]);
        $this->assertEquals($items[1], $results[1]);
        $this->assertEquals($items[2], $results[2]);
    }
}
