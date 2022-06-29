<?php

namespace Objectiv\Plugins\Checkout\Model;

class MatchedVariationResult {
	private $variation_id;
	private $variation_data;

	public function __construct( $variation_id = null, $variation_data = null ) {
		$this->variation_id   = $variation_id;
		$this->variation_data = $variation_data;
	}

	public function get_id(): ?int {
		return $this->variation_id;
	}

	public function get_attributes(): ?array {
		return $this->variation_data;
	}
}