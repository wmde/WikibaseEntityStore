<?php

namespace Wikibase\EntityStore\Tests\Fixtures;

use BatchingIterator\InMemoryBatchingFetcher;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityStore\BatchingEntityFetcher;
use Wikibase\Lib\Store\EntityLookup;
use Wikibase\StorageException;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class InMemoryEntityLookup implements EntityLookup {

	private $entities;
	private $idsForWhichToThrowException;

	/**
	 * @param Entity[] $entities
	 * @param EntityId[] $idsForWhichToThrowException
	 */
	public function __construct( array $entities, array $idsForWhichToThrowException ) {
		foreach ( $entities as $entity ) {
			$this->entities[$entity->getId()->getSerialization()] = $entity;
		}

		$this->idsForWhichToThrowException = $idsForWhichToThrowException;
	}

	/**
	 * @see EntityLookup::getEntity
	 *
	 * @param EntityId $entityId
	 *
	 * @throws StorageException
	 * @return Entity|null
	 */
	public function getEntity( EntityId $entityId ) {
		if ( in_array( $entityId, $this->idsForWhichToThrowException ) ) {
			throw new StorageException( 'The id is in idsForWhichToThrowException' );
		}

		if ( array_key_exists( $entityId->getSerialization(), $this->entities ) ) {
			return $this->entities[$entityId->getSerialization()];
		}

		return null;
	}

	/**
	 * @see EntityLookup::getEntity
	 *
	 * @param EntityId $entityId
	 *
	 * @throws StorageException
	 * @return bool
	 */
	public function hasEntity( EntityId $entityId ) {
		throw new StorageException( 'No implemented' );
	}

}