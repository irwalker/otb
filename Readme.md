# OTB - IW

An application that sorts a given list of jobs according to their dependencies.

- [Requirements](#requirements)
- [Setup](#setup)
- [Running Tests](#running-tests)
- [Technology Decisions](#technology-decisions)
- [Development Notes](#development-notes)

## Requirements
You must have the following installed for this to work:
 * [php (7.3+)](https://www.php.net/downloads.php)
 * [composer](https://getcomposer.org/download/)

### Optional
 * [xdebug for code coverage statistics](https://xdebug.org/docs/install)

## Setup

To install dependencies and get class loading working:

```
composer update
```

## Running Tests

This application is tested using the PHPUnit test suite to run tests:

```
./vendor/bin/phpunit
```

To generate code coverage statistics:

```
./vendor/bin/phpunit --coverage-text=./tests/coverage.txt
```

## Technology Decisions

Built using PHP as this is the language I've been using the most for the past few years.

## Development Notes

### Initial Plan
* Step 1: Get unit tests loaded, failing
* Step 2: Get an unordered array of the input string loaded. I've assumed that I can't rely on splitting on "\n", i.e. a=>b=>c is valid. Probably lends itself to recursion... but my brain
* Step 3: Get simple case (no dependencies) returning in expected format
* Step 4: Extend simple case
* Step 5: Add check for self-referencing
* Step 6: Add check for circular dependency. Realised that if we are asked to move a dependency into a breaking order it means a circular reference must exist
* Step 7: Add code coverage check

### Big Refactor
* Encapsulated array representation of sorted jobs into JobCollection.
* JobCollection knows how to sort itself
* Addded two output options to JobCollection; array or string output
* Sorting now done recursively. Made sense once everything wasn't just arrays.
