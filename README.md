## Simple PHP Queue
#### Based on JAVA Queue API
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
[![Code Coverage](https://scrutinizer-ci.com/g/initx/querabilis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/initx/querabilis/?branch=master)
## Installation
```bash
$ composer require initx/querabilis
```
## Usage
#### Driver
```php
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');
```
#### Push to queue
```php
use Initx\Envelope;

$envelope = new Envelope('Payload goes here');

$queue->add($envelope);
```
#### Pull form queue
```php
$envelope = $queue->remove();

$envelope->getPayload(); // "Payload goes here"
```
#### Summary of methods
|         	| Throws exception 	| Returns special value 	|
|---------	|------------------	|-----------------------	|
| Insert  	| add(e)           	| offer(e)              	|
| Remove  	| remove()         	| poll()                	|
| Examine 	| element()        	| peek()                	|
#### Currently supported drivers
- Filesystem
