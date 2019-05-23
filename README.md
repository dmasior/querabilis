# QUERABILIS
## Simple PHP Queue Library
[![Build Status](https://travis-ci.org/initx/querabilis.svg?branch=master)](https://travis-ci.org/initx/querabilis)
### Installation
```
$ composer require initx/querabilis
```
### Usage
```
$queue = new FilesystemQueue('./queue');
$queue->add($envelope);
```
