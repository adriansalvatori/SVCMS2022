<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\ShortcodeService;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;

/**
 * Class CabinetCustomerShortcodeService
 *
 * @package AmeliaBooking\Infrastructure\WP\ShortcodeService
 */
class CabinetCustomerShortcodeService extends AmeliaShortcodeService
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
                'trigger'      => '',
                'counter'      => self::$counter,
                'appointments' => null,
                'events'       => null
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

        ob_start();
        include AMELIA_PATH . '/view/frontend/cabinet-customer.inc.php';
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
