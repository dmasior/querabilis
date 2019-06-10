# Changelog

## 0.6
Changed
- Move dependencies from require to suggest of composer.json to make package flexible
- Deep refactor after including slevomat coding standards

## 0.5
Added
- AMQP driver

Changed
- refactor each driver methods remove() and element() - extract to trait

## 0.4.1
Added
- Beanstalkd driver ( credits https://github.com/Zae )

## 0.4
Added
- Changelog

Changed
- namespace Tests to Initx\Querabilis\Tests and Initx to Initx\Querabilis due to possible future compatibility issues

## 0.3.2
Changed
- queue interface method add(e) return true on success
- minor code style fixes
- readme update

## 0.3.1
Added
- code style and quality checks (phpcs, psalm)
Changed
- refactor needed by new cs / quality checks

## 0.3
Added
- InMemoryQueue driver

## 0.2
Added
- AWS SQS driver
- Missing queue interface throws annotations

Changed
- Refactor Filesystem driver

## 0.1
- version 0.1, initial release
