<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Services\Payment;

use AmeliaBooking\Domain\Services\Settings\SettingsService;

/**
 * Class AbstractPaymentService
 *
 * @package AmeliaBooking\Domain\Services\Payment
 */
class AbstractPaymentService
{
    /**
     * @var SettingsService $settingsService
     */
    protected $settingsService;

    /**
     * PayPalService constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(
        SettingsService $settingsService
    ) {
        $this->settingsService = $settingsService;
    }
}
