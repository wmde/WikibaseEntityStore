<?php

namespace Wikibase\EntityStore\Tests;

use BatchingIterator\InMemoryBatchingFetcher;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityStore\BatchingEntityFetcher;
use Wikibase\EntityStore\Tests\Fixtures\InMemoryEntityLookup;

/**
 * @covers Wikibase\EntityStore\BatchingEntityFetcher
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityFetcherTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var BatchingEntityFetcher
	 */
	private $fetcher;

	public function setUp() {
		$idFetcher = $this->newIdFetcher();

		$entityLookup = $this->newEntityLookup();

		$this->fetcher = new BatchingEntityFetcher(
			$idFetcher,
			$entityLookup
		);
	}

	private function newIdFetcher() {
		return new InMemoryBatchingFetcher( array(
			new ItemId( 'Q1' ),
			new ItemId( 'Q2' ),
			new ItemId( 'Q3' ),
			new ItemId( 'Q4' ),
			new ItemId( 'Q5' ),
			new ItemId( 'Q6' ),
			new ItemId( 'Q7' ),
			new ItemId( 'Q8' ),
		) );
	}

	private function newEntityLookup() {
		return new InMemoryEntityLookup(
			array(
				$this->newItemWithId( 'Q1' ),
				$this->newItemWithId( 'Q2' ),
				// Q3 is missing
				$this->newItemWithId( 'Q4' ),
				$this->newItemWithId( 'Q5' ),
				// Q6 results in an error
				$this->newItemWithId( 'Q7' ),
				// Q8 is missing
			),
			array(
				new ItemId( 'Q6' ),
			)
		);
	}

	private function newItemWithId( $id ) {
		$item = Item::newEmpty();
		$item->setId( new ItemId( $id ) );
		return $item;
	}

	public function testFetchingOfTwoExistingEntities() {
		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q1' ),
				new ItemId( 'Q2' ),
			),
			$this->fetcher->fetchNext( 2 )
		);
	}

	public function testNonExistingEntitiesGetSkipped() {
		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q1' ),
				new ItemId( 'Q2' ),
				new ItemId( 'Q4' ),
				new ItemId( 'Q5' ),
			),
			$this->fetcher->fetchNext( 5 )
		);
	}

	public function testContinuationWorksEvenWhenEntityDoesNotExist() {
		$this->fetcher->fetchNext( 3 );

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q4' ),
				new ItemId( 'Q5' ),
			),
			$this->fetcher->fetchNext( 2 )
		);
	}

	public function testFetchErrorCausesEntityToBeSkipped() {
		$this->fetcher->fetchNext( 4 );

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q5' ),
				new ItemId( 'Q7' ),
			),
			$this->fetcher->fetchNext( 3 )
		);
	}

	public function testMissingEntitiesAndFetchErrorsCauseOnSkippedFunctionToBeCalled() {
		$onEntitySkippedCalls = array();

		$fetcher = new BatchingEntityFetcher(
			$this->newIdFetcher(),
			$this->newEntityLookup(),
			function( EntityId $id, $reasonMessage ) use ( &$onEntitySkippedCalls ) {
				$onEntitySkippedCalls[] = array( $id, $reasonMessage );
			}
		);

		$fetcher->fetchNext( 10 );

		$this->assertEquals(
			array(
				array( new ItemId( 'Q3' ), 'No such entity in the store' ),
				array( new ItemId( 'Q6' ), 'The id is in idsForWhichToThrowException' ),
				array( new ItemId( 'Q8' ), 'No such entity in the store' ),
			),
			$onEntitySkippedCalls
		);
	}

	public function testWhenAllEntitiesInBatchAreSkipped_aNewFetchCallIsMade() {
		$this->fetcher->fetchNext( 2 );

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q4' ),
			),
			$this->fetcher->fetchNext( 1 )
		);
	}

	public function testEmptyArrayIsReturnedOnCallsAfterLastData() {
		$this->fetcher->fetchNext( 10 );
		$this->assertSame( array(), $this->fetcher->fetchNext( 10 ) );
		$this->assertSame( array(), $this->fetcher->fetchNext( 10 ) );
	}

	/**
	 * @param ItemId[] $expectedIds
	 * @param Item[] $items
	 */
	private function assertAreItemsWithIds( array $expectedIds, array $items ) {
		$this->assertContainsOnlyInstancesOf( 'Wikibase\DataModel\Entity\Item', $items );

		$actualIds = array();

		foreach ( $items as $item ) {
			$actualIds[] = $item->getId();
		}

		$this->assertEquals( $expectedIds, $actualIds );
	}

}
