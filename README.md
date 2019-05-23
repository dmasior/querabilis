# QUERABILIS
## Simple PHP Queue Library
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
### Installation
```
$ composer require initx/querabilis
```
### Usage
```
use Initx\Envelope;
use Initx\PlainPayload;
use Initx\Driver\FilesystemQueue;

$queue = new FilesystemQueue('./queue');

$envelope = new Envelope('title', new PlainPayload('Your payload'));

$queue->add($envelope);
```
