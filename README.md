# Couchbase REST API Client
Couchbase REST API wrapper to easily access view and n1ql queries without installing Couchbase C and PHP SDK.

## Installation

```
composer require moon/couchbaserestclient
```

## Advantages

- Access Couchbase wihtout installing C and PHP SDK.
- Supports parallel query processing by using multi-curl.

Accessing Couchbase without installing C and PHP SDK is nice, but it is really not a big advantage as you don't really
uninstall C and PHP SDKs once you install them. Couchbase has batch operations (https://developer.couchbase.com/documentation/server/current/sdk/php/document-operations.html#topic_eqq_rmd_yv__batching), which
let you insert/get multiple docs even in PHP. However, You can't send multiple n1ql queries in parallel in the PHP SDK. One thing I find very useful with Couchbase REST API approach is that you can send multiple
update requests in parallel, which is not possible (or hard) with the official PHP SDK.

## Disadvantage
- Couchbase Rest API endpoint may not perform as good as its official SDK, which takes advantage of its optimized protocol.

## Setup

### Usage

Create an instance of ```CouchbaseRestApiClient```
```php
$client = new CouchbaseRestApiClient("http://couchbase:8091", "username", "password");
$client->setN1qlHost("http://couchbase:8093/service/query");
```

#### Querying View
```php
$paginator = $client->createViewPaginator('designDocumentName', 'viewName');
$paginator->setStartKey("startKey");
foreach ($paginator as $page) {
    var_dump($page);
}
```

#### Querying N1QL

##### Synchronous Query
```php
$query = "select * from testBucket use keys ['testKey']";
$response = $client->queryN1ql($query);
var_dump($response);
```

##### Parallel Query
You can send multiple queries to Couchbase by using ```ParallelQueryQueue```.
```php
// Let's send 30 queries at once
$parallelQueryQueue = $client->createParallelQueryQueue(30);

// queue 90 queries
for($i=0; $i<=89; $i++) {
    $key = "testKey".$i;
    $parallelQueryQueue->queue("update myBucket use keys ['{$key}'] set index=$i";
}

// run
while($responses = $parallelQueryQueue->next()) {

}
```
