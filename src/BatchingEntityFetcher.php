<?php

namespace Wikibase\EntityStore;

use BatchingIterator\BatchingFetcher;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\Lib\Store\EntityLookup;
use Wikibase\Lib\Store\StorageException;

/**
 * @since 0.1
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityFetcher implements BatchingFetcher {

	private $entityIdFetcher;
	private $entityLookup;
	private $onEntitySkipped;

	/**
	 * @param BatchingFetcher $entityIdFetcher
	 * @param EntityLookup $entityLookup
	 * @param callable|null $onEntitySkipped with two parameters, EntityId $entityId and string $reasonMessage
	 */
	public function __construct( BatchingFetcher $entityIdFetcher, EntityLookup $entityLookup, $onEntitySkipped = null ) {
		$this->entityIdFetcher = $entityIdFetcher;
		$this->entityLookup = $entityLookup;
		$this->onEntitySkipped = $onEntitySkipped;
	}

	/**
	 * @see BatchingFetcher::fetchNext
	 *
	 * @param int $maxFetchCount
	 *
	 * @return Entity[]
	 */
	public function fetchNext( $maxFetchCount ) {
		do {
			$entitiesToFetch = $this->entityIdFetcher->fetchNext( $maxFetchCount );
			$entities = $this->retrieveEntities( $entitiesToFetch );
		}
		while ( !empty( $entitiesToFetch ) && empty( $entities ) );

		return $entities;
	}

	private function retrieveEntities( array $entityIds ) {
		$entities = array();

		foreach ( $entityIds as $entityId ) {
			$entity = $this->retrieveEntity( $entityId );

			if ( $entity !== null ) {
				$entities[] = $entity;
			}
		}

		return $entities;
	}

	private function retrieveEntity( EntityId $entityId ) {
		try {
			$entity = $this->entityLookup->getEntity( $entityId );
		}
		catch ( StorageException $ex ) {
			$this->reportSkippedEntity( $entityId, $ex->getMessage() );
			return null;
		}

		if ( $entity === null ) {
			$this->reportSkippedEntity( $entityId, 'No such entity in the store' );
		}

		return $entity;
	}

	private function reportSkippedEntity( EntityId $entityId, $reasonMessage ) {
		if ( $this->onEntitySkipped !== null ) {
			call_user_func( $this->onEntitySkipped, $entityId, $reasonMessage );
		}
	}

}
