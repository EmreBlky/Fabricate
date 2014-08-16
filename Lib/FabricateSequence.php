<?php

/**
 * Fabricator Sequence Class 
 */
class FabricateSequence {

/**
 * @var integer
 */
	private $sequence;

/**
 * Construct with sequence start
 *
 * @param integer $start number of starting
 */
	public function __construct($start) {
		$this->sequence = $start;
	}

/**
 * Get current sequence number
 *
 * @return integer 
 */
	public function current() {
		return $this->sequence;
	}

/**
 * Increment sequence number
 *
 * @return integer incremented number
 */
	public function next() {
		return ++$this->sequence;
	}
}