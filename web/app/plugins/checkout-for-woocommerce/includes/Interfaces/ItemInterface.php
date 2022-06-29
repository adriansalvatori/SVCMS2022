<?php

namespace Objectiv\Plugins\Checkout\Interfaces;

use WC_Product;

interface ItemInterface {
	public function get_thumbnail(): string;
	public function get_quantity(): float;
	public function get_title(): string;
	public function get_url(): string;
	public function get_subtotal(): string;
	public function get_row_class(): string;
	public function get_item_key(): string;
	public function get_raw_item();
	public function get_product(): WC_Product;
	public function get_data(): array;
	public function get_formatted_data(): string;
}
