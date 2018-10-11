#BDSpider
======================

## Usage
```shell
composer require bdspider/bdspider
```

## example
```php
require __DIR__ . '/vendor/autoload.php';

use BDSpider\BDSpider;

$res = BDSpider::search('laravel');
print_r($res);
```
