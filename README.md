## Simple PHP Queue
#### Compliant with JAVA Queue interface
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
[![Code Coverage](https://scrutinizer-ci.com/g/initx/querabilis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/initx/querabilis/?branch=master)
## Installation
```bash
$ composer require initx/querabilis
```
## Usage
#### Driver
```php
use Initx\Querabilis\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');
```
#### Push to queue
```php
use Initx\Querabilis\Envelope;

$envelope = new Envelope('Payload goes here');

$queue->add($envelope);
```
#### Pull form queue
```php
$envelope = $queue->remove();

$envelope->getPayload(); // "Payload goes here"
```
### Currently supported drivers
- Amazon SQS
- Redis (Predis)
- AMQP
- Beanstalkd
- Filesystem
- In memory

Each driver implements Queue interface.

### Summary of Queue interface

##### Insert
- `add(e)` - inserts an element if possible, otherwise throwing exception
- `offer(e)` - inserts an element if possible, otherwise returning false

##### Remove
- `remove()` - remove and return head of queue, otherwise throwing exception
- `poll()` - remove and return head of queue, otherwise returning null

##### Examine
- `element()` - return but do not remove head of queue, otherwise throwing exception
- `peek()` - return but do not remove head of queue, otherwise returning null

### More examples
##### Redis (Predis) driver
```php
use Predis\Client;
use Initx\Querabilis\Driver\RedisQueue;

$client = new Client(['host' => '127.0.0.1']);
$queue = new RedisQueue($client, 'queueName');
```
##### AWS SQS driver
```php
use Aws\Sqs\SqsClient;
use Initx\Querabilis\Driver\SqsQueue;

$client = new SqsClient(your_sqs_client_config);
$queue = new SqsQueue($client, 'queueName');
```

##### In memory driver
```php
use Initx\Querabilis\Driver\InMemoryQueue;

$queue = new InMemoryQueue();
```

##### Beanstalkd driver
```php
use Pheanstalk\Pheanstalk;
use Initx\Querabilis\Driver\BeanstalkdQueue;

$client = Pheanstalk::create([your_beanstalkd_config]);
$queue = new BeanstalkdQueue($client);
```

##### AMQP driver
```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Initx\Querabilis\Driver\AmqpQueue;

$connection = new AMQPStreamConnection([your_amqp_config]);
$queue = new AmqpQueue($connection, 'queueName', 'exchange');
```
