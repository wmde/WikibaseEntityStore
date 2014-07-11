<?php

namespace Wikibase\EntityStore\Tests;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityStore\BatchingEntityIdFetcher;

/**
 * @covers Wikibase\EntityStore\BatchingEntityIdFetcher
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityIdFetcherTest extends \PHPUnit_Framework_TestCase {

	public function testEntityPerPageIsCalledAndResultIsReturned() {
		$entityType = 'kittens';
		$entityId = new ItemId( 'Q42' );
		$batchSize = 5;

		$expectedReturnValue = array(
			new ItemId( 'Q43' ),
			new ItemId( 'Q44' ),
			new ItemId( 'Q45' ),
		);

		$entityPerPageTable = $this->getMock( 'Wikibase\EntityPerPage' );

		$entityPerPageTable->expects( $this->once() )
			->method( 'listEntities' )
			->with(
				$this->equalTo( $entityType ),
				$this->equalTo( $batchSize ),
				$this->equalTo( $entityId )
			)
			->will( $this->returnValue( $expectedReturnValue ) );

		$idFetcher = new BatchingEntityIdFetcher(
			$entityPerPageTable,
			$entityType,
			$entityId
		);

		$fetchedIds = $idFetcher->fetchNext( $batchSize );
		$this->assertSame( $expectedReturnValue, $fetchedIds );
		$this->assertEquals( current( $expectedReturnValue ), current( $fetchedIds ) );
	}

}
