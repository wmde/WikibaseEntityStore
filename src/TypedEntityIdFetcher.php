<?php

namespace Wikibase\EntityStore;

use BatchingIterator\BatchingFetcher;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\EntityPerPage;

/**
 * @since 0.2
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TypedEntityIdFetcher implements BatchingFetcher {

	private $entityPerPageTable;
	private $entityType;
	private $initialPreviousId;

	private $previousId;

	/**
	 * @param EntityPerPage $entityPerPageTable
	 * @param string $entityType
	 * @param EntityId|null $previousId Fetch entity ids that come after the provided one. Null to start at the first one.
	 */
	public function __construct( EntityPerPage $entityPerPageTable, $entityType, EntityId $previousId = null ) {
		$this->entityPerPageTable = $entityPerPageTable;
		$this->entityType = $entityType;
		$this->initialPreviousId = $previousId;
		$this->rewind();
	}

	/**
	 * @see BatchingFetcher::fetchNext
	 *
	 * @param int $maxFetchCount
	 *
	 * @return EntityId[]
	 */
	public function fetchNext( $maxFetchCount ) {
		$ids = $this->entityPerPageTable->listEntities(
			$this->entityType,
			$maxFetchCount,
			$this->previousId
		);

		$this->previousId = end( $ids );
		reset( $ids );

		return $ids;
	}

	/**
	 * @see BatchingFetcher::rewind
	 */
	public function rewind() {
		$this->previousId = $this->initialPreviousId;
	}

}
