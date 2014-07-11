<?php

namespace Wikibase\EntityStore;

use BatchingIterator\BatchingFetcher;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\EntityPerPage;

/**
 * @since 0.1
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityIdFetcher implements BatchingFetcher {

	private $entityPerPageTable;
	private $entityType;
	private $previousId;

	/**
	 * @param EntityPerPage $entityPerPageTable
	 * @param string $entityType
	 * @param EntityId|null $previousId Fetch entity ids that come after the provided one. Null to start at the first one.
	 */
	public function __construct( EntityPerPage $entityPerPageTable, $entityType, EntityId $previousId = null ) {
		$this->entityPerPageTable = $entityPerPageTable;
		$this->entityType = $entityType;
		$this->previousId = $previousId;
	}

	/**
	 * @see BatchingFetcher::fetchNext
	 *
	 * @param int $maxFetchCount
	 *
	 * @return Entity[]
	 */
	public function fetchNext( $maxFetchCount ) {
		$ids = $this->entityPerPageTable->listEntities(
			$this->entityType,
			$maxFetchCount,
			$this->previousId
		);

		$this->previousId = $this->getLastElement( $ids );

		return $ids;
	}

	private function getLastElement( array $array ) {
		end( $array );
		return current( $array );
	}

}
