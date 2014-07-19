<?php

namespace Wikibase\EntityStore\Tests\Integration;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityStore\BatchingEntityFetcher;
use Wikibase\EntityStore\BatchingEntityIdFetcherBuilder;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers Wikibase\EntityStore\BatchingEntityFetcher
 *
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityFetcherTest extends \MediaWikiTestCase {

	public function setUp() {
		parent::setUp();

		$this->insertEntities();
	}

	private function insertEntities() {
		$entityStore = WikibaseRepo::getDefaultInstance()->getStore()->getEntityStore();

		foreach ( range( 1000070, 1000075 ) as $itemId ) {
			$item = Item::newEmpty();
			$item->setId( $itemId );
			$entityStore->saveEntity( $item, __CLASS__, $GLOBALS['wgUser'] );
		}
	}

	private function getFetcherThatContinuesFrom( EntityId $previousId = null ) {
		$repo = WikibaseRepo::getDefaultInstance();

		$fetcherBuilder = new BatchingEntityIdFetcherBuilder(
			$repo->getStore()->newEntityPerPage(),
			$previousId
		);

		return new BatchingEntityFetcher(
			$fetcherBuilder->getFetcher(),
			$repo->getEntityLookup()
		);
	}

	public function testWhenFetchingLessEntitiesThenExist_fullBatchOfEntitiesIsReturned() {
		$entities = $this->getFetcherThatContinuesFrom( null )->fetchNext( 5 );

		$this->assertCount( 5, $entities );
		$this->assertContainsOnlyInstancesOf( 'Wikibase\DataModel\Entity\Item', $entities );
	}

	public function testStartingPositionIsHeldIntoAccountAndIsIncremented() {
		$fetcher = $this->getFetcherThatContinuesFrom( new ItemId( 'Q1000070' ) );

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q1000071' ),
				new ItemId( 'Q1000072' ),
			),
			$fetcher->fetchNext( 2 )
		);

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q1000073' ),
				new ItemId( 'Q1000074' ),
			),
			$fetcher->fetchNext( 2 )
		);
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

	public function testRewindSetsThePositionBackToTheInitialValue() {
		$fetcher = $this->getFetcherThatContinuesFrom( new ItemId( 'Q1000070' ) );

		$fetcher->fetchNext( 5 );
		$fetcher->rewind();

		$this->assertAreItemsWithIds(
			array(
				new ItemId( 'Q1000071' ),
				new ItemId( 'Q1000072' ),
			),
			$fetcher->fetchNext( 2 )
		);
	}

}
