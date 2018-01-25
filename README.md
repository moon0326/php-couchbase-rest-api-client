# Couchbase REST API Client
Couchbase REST API wrapper to easily access view and n1ql queries.

## Setup

```php
$client = new CouchbaseRestApiClient("http://couchbase:8091", "username", "password");
```

#### Querying View
```
$paginator = $client->createViewPaginator('designDocumentName', 'viewName');
$paginator->setStartKey("startKey");
foreach ($paginator as $page) {
    var_dump($page);

}
```

#### Querying N1QL

