# QUERABILIS
## Simple PHP Queue Library
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
### Installation
```bash
$ composer require initx/querabilis
```
### Usage

#### Add
```php
use Initx\Envelope;
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');

$envelope = new Envelope('Your Payload');

$queue->add($envelope);
```
#### Retrieve
