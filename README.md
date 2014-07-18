# Wikibase EntityStore

[![Build Status](https://secure.travis-ci.org/wmde/WikibaseEntityStore.png?branch=master)](http://travis-ci.org/wmde/WikibaseEntityStore)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/WikibaseEntityStore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/WikibaseEntityStore/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/WikibaseEntityStore/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/WikibaseEntityStore/?branch=master)

On [Packagist](https://packagist.org/packages/wikibase/entity-store):
[![Latest Stable Version](https://poser.pugx.org/wikibase/entity-store/version.png)](https://packagist.org/packages/wikibase/entity-store)
[![Download count](https://poser.pugx.org/wikibase/entity-store/d/total.png)](https://packagist.org/packages/wikibase/entity-store)

**Wikibase EntityStore** provides persistence services for [Wikibase](http://wikiba.se/) entities.

## Tests

You can run the PHPUnit tests by changing into the `tests/phpunit` directory of your MediaWiki
install and running

    php phpunit.php -c ../../extensions/WikibaseEntityStore/

## Release notes

### Version 0.1 (2014-07-18)

* Initial release with
    * `BatchingEntityFetcher`
    * `BatchingEntityIdFetcher`

## Links

* [Wikibase EntityStore on Packagist](https://packagist.org/packages/wikibase/entity-store)
* [Wikibase EntityStore on TravisCI](https://travis-ci.org/wmde/WikibaseEntityStore)
* [Wikibase EntityStore on ScrutinizerCI](https://scrutinizer-ci.com/g/wmde/WikibaseEntityStore)
