<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\ShortcodeService;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\WP\Integrations\WooCommerce\WooCommerceService;
use AmeliaBooking\Infrastructure\WP\SettingsService\SettingsStorage;

/**
 * Class CabinetEmployeeShortcodeService
 *
 * @package AmeliaBooking\Infrastructure\WP\ShortcodeService
 */
class CabinetEmployeeShortcodeService extends AmeliaShortcodeService
{
    /**
     * @param array $atts
     * @return string
     * @throws InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public static function shortcodeHandler($atts)
    {
        $atts = shortcode_atts(
            [
                'trigger'        => '',
                'counter'        => self::$counter,
                'appointments'   => null,
                'events'         => null,
                'profile-hidden' => null
            ],
            $atts
        );

        self::prepareScriptsAndStyles();

        // Enqueue Styles
        wp_enqueue_style(
            'amelia_booking_styles_quill',
            AMELIA_URL . 'public/css/frontend/quill.css',
            [],
            AMELIA_VERSION
        );

        $settingsService = new SettingsService(new SettingsStorage());

        $wcSettings = $settingsService->getSetting('payments', 'wc');

        if ($wcSettings['enabled'] && WooCommerceService::isEnabled()) {
            wp_localize_script(
                'amelia_booking_scripts',
                'wpAmeliaWcProducts',
                WooCommerceService::getInitialProducts()
            );
        }

        ob_start();
        include AMELIA_PATH . '/view/frontend/cabinet-employee.inc.php';
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
