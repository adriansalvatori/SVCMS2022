<?php

use Objectiv\Plugins\Checkout\Factories\BumpFactory;
use Objectiv\Plugins\Checkout\Managers\PlanManager;

/**
 * @return bool
 */
function cfw_is_thank_you_view_order_page_active(): bool {
	return cfw_is_thank_you_page_active() && PlanManager::can_access_feature( 'override_view_order_template', PlanManager::PLUS );
}
/**
 * @throws Exception
 */
function cfw_get_all_order_bumps(): array {
	return BumpFactory::get_all();
}
