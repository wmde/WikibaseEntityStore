<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( is_readable( $path = __DIR__ . '/../vendor/autoload.php' ) ) {
	print( "\nUsing the local vendor autoloader ...\n\n" );
} elseif ( is_readable( $path = __DIR__ . '/../../../vendor/autoload.php' ) ) {
	print( "\nUsing the MediaWiki vendor autoloader ...\n\n" );
} else {
	die( 'You need to install this package with Composer before you can run the tests' );
}

$loader = require $path;

$loader->addPsr4( 'Wikibase\\EntityStore\\Tests\\Fixtures\\', __DIR__ . '/fixtures/' );