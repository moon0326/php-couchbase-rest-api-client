WIP

# Couchbase REST API Client
Couchbase REST API wrapper to easily access view and n1ql queries without installing Couchbase C and PHP SDK.

## Advantages

- Access Couchbase wihtout installing C and PHP SDK.
- Supports parallel query processing by using multi-curl. A must-have feature that is missing from the official PHP SDK.

## Disadvantage
- Couchbase Rest API endpoint may not perform as good as protocol used by the official SDK.

## Setup

## Usage

Create an instance of ```CouchbaseRestApiClient```
```php
$client = new CouchbaseRestApiClient("http://couchbase:8091", "username", "password");
```

### Querying View
```php
$paginator = $client->createViewPaginator('designDocumentName', 'viewName');
$paginator->setStartKey("startKey");
foreach ($paginator as $page) {
    var_dump($page);
}
```

### Querying N1QL

#### Synchronous Query
```php
$query = "select * from testBucket use keys ['testKey']";
$response = $client->queryN1ql($query);
var_dump($response);
```

#### Parallel Query
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
