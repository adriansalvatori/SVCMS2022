<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\ShortcodeService;

use AmeliaBooking\Application\Services\Cache\CacheApplicationService;
use AmeliaBooking\Application\Services\Stash\StashApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\WP\SettingsService\SettingsStorage;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Interop\Container\Exception\ContainerException;

/**
 * Class StepBookingShortcodeService
 *
 * @package AmeliaBooking\Infrastructure\WP\ShortcodeService
 */
class StepBookingShortcodeService
{
    public static $counter = 1000;

    /**
     * @param array $params
     * @return string
     */
    public static function shortcodeHandler($params)
    {
        $params = shortcode_atts(
            [
                'trigger'  => '',
                'show'     => '',
                'category' => null,
                'service'  => null,
                'employee' => null,
                'location' => null,
                'counter'  => self::$counter
            ],
            $params
        );

        self::prepareScriptsAndStyles();

        ob_start();
        include AMELIA_PATH . '/view/frontend/step-booking.inc.php';
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Prepare scripts and styles
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public static function prepareScriptsAndStyles()
    {
        $container = null;

        self::$counter++;

        if (self::$counter > 1001) {
            return;
        }

        $settingsService = new SettingsService(new SettingsStorage());

        if ($settingsService->getSetting('payments', 'payPal')['enabled'] === true) {
            wp_enqueue_script('amelia_paypal_script', 'https://www.paypalobjects.com/api/checkout.js');
        }

        if ($settingsService->getSetting('payments', 'stripe')['enabled'] === true) {
            wp_enqueue_script('amelia_stripe_script', 'https://js.stripe.com/v3/');
        }

        if ($settingsService->getSetting('payments', 'razorpay')['enabled'] === true) {
            wp_enqueue_script('amelia_razorpay_script', 'https://checkout.razorpay.com/v1/checkout.js');
        }

        $scriptId = AMELIA_DEV ? 'amelia_booking_scripts_dev_vite' : 'amelia_booking_script_index';

        if (AMELIA_DEV) {
            wp_enqueue_script(
                $scriptId,
                'http://localhost:3000/@vite/client',
                [],
                null,
                false
            );

            wp_enqueue_script(
                'amelia_booking_scripts_dev_main',
                'http://localhost:3000/src/assets/js/public/public.js',
                [],
                null,
                true
            );
        } else {
            wp_enqueue_script(
                $scriptId,
                AMELIA_URL . 'v3/public/assets/public.4446c7a4.js',
                [],
                AMELIA_VERSION,
                true
            );
        }

        wp_localize_script(
            $scriptId,
            'localeLanguage',
            [AMELIA_LOCALE]
        );

        wp_localize_script(
            $scriptId,
            'wpAmeliaSettings',
            $settingsService->getFrontendSettings()
        );

        // Strings Localization
        wp_localize_script(
            $scriptId,
            'wpAmeliaLabels',
            FrontendStrings::getAllStrings()
        );

        wp_localize_script(
            $scriptId,
            'wpAmeliaUrls',
            [
                'wpAmeliaUseUploadsAmeliaPath' => AMELIA_UPLOADS_FILES_PATH_USE,
                'wpAmeliaPluginURL'            => AMELIA_URL,
                'wpAmeliaPluginAjaxURL'        => AMELIA_ACTION_URL
            ]
        );

        if (!empty($_GET['ameliaCache']) || !empty($_GET['ameliaWcCache'])) {
            $container = $container ?: require AMELIA_PATH . '/src/Infrastructure/ContainerConfig/container.php';

            /** @var CacheApplicationService $cacheAS */
            $cacheAS = $container->get('application.cache.service');

            try {
                $cacheData = !empty($_GET['ameliaCache']) ?
                    $cacheAS->getCacheByName($_GET['ameliaCache']) : $cacheAS->getWcCacheByName($_GET['ameliaWcCache']);

                wp_localize_script(
                    $scriptId,
                    'ameliaCache',
                    [$cacheData ? json_encode($cacheData) : '']
                );
            } catch (QueryExecutionException $e) {
            }
        }

        if ($settingsService->getSetting('activation', 'stash')) {
            $container = $container ?: require AMELIA_PATH . '/src/Infrastructure/ContainerConfig/container.php';

            /** @var StashApplicationService $stashAS */
            $stashAS = $container->get('application.stash.service');

            wp_localize_script(
                $scriptId,
                'ameliaEntities',
                $stashAS->getStash()
            );
        }
    }

    /**
     * @param string $tag
     * @param string $handle
     * @param string $src
     *
     * @return string
     */
    public static function prepareScripts($tag, $handle, $src)
    {
        switch ($handle) {
            case ('amelia_booking_scripts_dev_vite'):
            case ('amelia_booking_scripts_dev_main'):
                return "<script type='module' src='{$src}'></script>";

            case ('amelia_booking_script_index'):
                return "<script type='module' crossorigin src='{$src}'></script>";

            case ('amelia_booking_script_vendor'):
                return "<link rel='modulepreload' href='{$src}'>";

            default:
                return $tag;
        }
    }

    /**
     * @param string $tag
     * @param string $handle
     * @param string $href
     *
     * @return string
     */
    public static function prepareStyles($tag, $handle, $href)
    {
        switch ($handle) {
            case ('amelia_booking_style_index'):
            case ('amelia_booking_style_vendor'):
                return "<link rel='stylesheet' href='{$href}'>";

            default:
                return $tag;
        }
    }
}
