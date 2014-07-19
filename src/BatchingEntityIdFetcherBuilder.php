<?php

namespace Wikibase\EntityStore;

use BatchingIterator\Fetchers\MultipleBatchingFetcher;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\EntityPerPage;

/**
 * @since 0.2
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class BatchingEntityIdFetcherBuilder {

	private $entityPerPageTable;
	private $previousId;

	private $fetchers = array();

	public function __construct( EntityPerPage $entityPerPageTable, EntityId $previousId = null ) {
		$this->entityPerPageTable = $entityPerPageTable;
		$this->previousId = $previousId;

		$this->addFetcherForEntityType( 'item' );
		$this->addFetcherForEntityType( 'property' );
	}

	private function addFetcherForEntityType( $entityType ) {
		$this->fetchers[] = new TypedEntityIdFetcher(
			$this->entityPerPageTable,
			$entityType,
			$this->getPreviousIdForEntityType( $entityType )
		);
	}

	private function getPreviousIdForEntityType( $entityType ) {
		if ( $this->previousId !== null && $this->previousId->getEntityType() === $entityType ) {
			return $this->previousId;
		}

		return null;
	}

	public function getFetcher() {
		return new MultipleBatchingFetcher( $this->fetchers );
	}

}