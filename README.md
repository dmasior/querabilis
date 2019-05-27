## Simple PHP Queue
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
[![Code Coverage](https://scrutinizer-ci.com/g/initx/querabilis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/initx/querabilis/?branch=master)
### Installation
```bash
$ composer require initx/querabilis
```
### Usage
#### Driver
```php
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');
```
#### Add to queue
```php
use Initx\Envelope;

$envelope = new Envelope('Payload goes here');

$queue->add($envelope);
```
#### Grab element from head of queue
```php
$envelope = $queue->remove();

$envelope->getPayload(); // "Payload goes here"
```
