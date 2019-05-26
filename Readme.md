# On The Beach Code Challenge - IW

My code challenge for On The Beach

## Requirements
You must have the following installed for this to work:
 * php
 * composer

## Setup

composer update

## Running

./vendor/bin/phpunit

To get code coverage working, you will need the php XDebug extension installed. See: https://xdebug.org/docs/install

Then run /vendor/bin/phpunit --coverage-text=./tests/coverage.txt


## Some notes on thinking throughout

* Step 1: Get unit tests loaded, failing
* Step 2: Get an unordered array of the input string loaded. I've assumed that I can't rely on splitting on "\n", i.e. a=>b=>c is valid. Probably lends itself to recursion... but my brain
* Step 3: Get simple case (no dependencies) returning in expected format
* Step 4: Extend simple case
* Add check for self-referencing
* Add check for circular dependency. Realised that if we are asked to move a dependency into a breaking order it means a circular reference must exist
* Added code coverage check - 100%

### Big Refactor ...

* Encapsulated array representation of sorted jobs into JobCollection.
* JobCollection knows how to sort itself
* Addded two output options to JobCollection; array or string output
* Sorting now done recursively. Made sense once everything wasn't just arrays.