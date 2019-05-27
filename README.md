# QUERABILIS
## Simple PHP Queue Library
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
[![Code Coverage](https://scrutinizer-ci.com/g/initx/querabilis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/initx/querabilis/?branch=master)
### Installation
```bash
$ composer require initx/querabilis
```
### Usage

#### Add to queue
```php
use Initx\Envelope;
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');

$envelope = new Envelope('Querabilis!');

$queue->add($envelope);
```
#### Remove from queue
```php
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');

// Remove element from head of queue
$envelope = $queue->remove();

$envelope->getPayload(); // "Querabilis!"
```
