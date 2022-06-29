<?php

namespace AmeliaBooking\Infrastructure\WP\WPMenu;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\WP\Integrations\WooCommerce\WooCommerceService;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;

/**
 * Renders menu pages
 */
class SubmenuPageHandler
{
    /** @var SettingsService $settingsService */
    private $settingsService;

    /**
     * SubmenuPageHandler constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Submenu page render function
     *
     * @param $page
     */
    public function render($page)
    {
        if ($page !== 'wpamelia-customize-new') {
            $this->renderOld($page);
        } else {
            $this->renderNew($page);
        }
    }

    private function renderOld($page)
    {
        if ($this->settingsService->getSetting('activation', 'enablePolyfill')) {
            wp_enqueue_script('amelia_polyfill', 'https://polyfill.io/v2/polyfill.js?features=Intl.~locale.en');
        }

        // Enqueue Scripts
        wp_enqueue_script(
            'amelia_booking_scripts',
            AMELIA_URL . 'public/js/backend/amelia-booking.js',
            [],
            AMELIA_VERSION
        );

        if ($page === 'wpamelia-locations' || $page === 'wpamelia-settings') {
            $gmapApiKey = $this->settingsService->getSetting('general', 'gMapApiKey');

            wp_enqueue_script(
                'google_maps_api',
                "https://maps.googleapis.com/maps/api/js?key={$gmapApiKey}&libraries=places"
            );
        }


        wp_enqueue_script(
            'papaparse',
            "https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.1/papaparse.min.js"
        );


        if ($page === 'wpamelia-notifications') {
            wp_enqueue_script('amelia_paddle', 'https://cdn.paddle.com/paddle/paddle.js');
        }

        // Enqueue Styles
        wp_enqueue_style(
            'amelia_booking_styles',
            AMELIA_URL . 'public/css/backend/amelia-booking.css',
            [],
            AMELIA_VERSION
        );

        // WordPress enqueue
        wp_enqueue_media();

        wp_localize_script(
            'amelia_booking_scripts',
            'wpAmeliaLanguages',
            HelperService::getLanguages()
        );

        $wcSettings = $this->settingsService->getSetting('payments', 'wc');

        if ($wcSettings['enabled'] && WooCommerceService::isEnabled()) {
            wp_localize_script(
                'amelia_booking_scripts',
                'wpAmeliaWcProducts',
                WooCommerceService::getInitialProducts()
            );
        }

        // Strings Localization
        switch ($page) {
            case ('wpamelia-locations'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getLocationStrings(),
                        BackendStrings::getCommonStrings()
                    )
                );

                break;
            case ('wpamelia-services'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getSettingsStrings(),
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getServiceStrings(),
                        BackendStrings::getBookableStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getAppointmentStrings(),
                        BackendStrings::getRecurringStrings()
                    )
                );

                break;
            case ('wpamelia-employees'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getEmployeeStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getScheduleStrings()
                    )
                );

                break;
            case ('wpamelia-customers'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getCustomerStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getScheduleStrings(),
                        BackendStrings::getImportStrings()
                    )
                );

                break;
            case ('wpamelia-finance'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getFinanceStrings(),
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getEventStrings()
                    )
                );

                break;
            case ('wpamelia-appointments'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getNotificationsStrings(),
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getCustomerStrings(),
                        BackendStrings::getAppointmentStrings(),
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getRecurringStrings()
                    )
                );

                break;

            case ('wpamelia-events'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getSettingsStrings(),
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getCustomerStrings(),
                        BackendStrings::getAppointmentStrings(),
                        BackendStrings::getEventStrings(),
                        BackendStrings::getBookableStrings(),
                        BackendStrings::getRecurringStrings()
                    )
                );

                break;

            case ('wpamelia-dashboard'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getAppointmentStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getCustomerStrings(),
                        BackendStrings::getDashboardStrings(),
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getRecurringStrings(),
                        BackendStrings::getNotificationsStrings()
                    )
                );

                break;
            case ('wpamelia-calendar'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getEntityFormStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getAppointmentStrings(),
                        BackendStrings::getUserStrings(),
                        BackendStrings::getCustomerStrings(),
                        BackendStrings::getCalendarStrings(),
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getEventStrings(),
                        BackendStrings::getBookableStrings(),
                        BackendStrings::getRecurringStrings()
                    )
                );

                break;
            case ('wpamelia-notifications'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getPaymentStrings(),
                        BackendStrings::getNotificationsStrings()
                    )
                );

                break;

            case ('wpamelia-smsnotifications'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getNotificationsStrings()
                    )
                );

                break;
            case ('wpamelia-settings'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getFinanceStrings(),
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getScheduleStrings(),
                        BackendStrings::getSettingsStrings(),
                        BackendStrings::getNotificationsStrings()
                    )
                );

                break;
            case ('wpamelia-customize'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getCustomizeStrings()
                    )
                );

                break;
            case ('wpamelia-cf'):
                wp_localize_script(
                    'amelia_booking_scripts',
                    'wpAmeliaLabels',
                    array_merge(
                        BackendStrings::getCommonStrings(),
                        BackendStrings::getCustomizeStrings()
                    )
                );

                break;
        }

        // Settings Localization
        wp_localize_script(
            'amelia_booking_scripts',
            'wpAmeliaSettings',
            $this->settingsService->getFrontendSettings()
        );

        wp_localize_script(
            'amelia_booking_scripts',
            'localeLanguage',
            [AMELIA_LOCALE]
        );

        wp_localize_script(
            'amelia_booking_scripts',
            'wpAmeliaTimeZone',
            [DateTimeService::getTimeZone()->getName()]
        );

        include AMELIA_PATH . '/view/backend/view.php';
    }

    private function renderNew($page)
    {
        $scriptId = AMELIA_DEV ? 'amelia_booking_scripts_dev_vite' : 'amelia_booking_script_index';

        if (AMELIA_DEV) {
            wp_enqueue_script(
                'amelia_booking_scripts_dev_vite',
                'http://localhost:3000/@vite/client',
                [],
                null,
                false
            );

            wp_enqueue_script(
                'amelia_booking_scripts_dev_main',
                'http://localhost:3000/src/assets/js/admin/admin.js',
                [],
                null,
                true
            );
        } else {
            wp_enqueue_script(
                $scriptId,
                AMELIA_URL . 'v3/public/assets/admin.caab8801.js',
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
            'wpAmeliaLanguages',
            HelperService::getLanguages()
        );

        // Settings Localization
        wp_localize_script(
            $scriptId,
            'wpAmeliaSettings',
            $this->settingsService->getFrontendSettings()
        );

        // Labels
        wp_localize_script(
            $scriptId,
            'wpAmeliaLabels',
            array_merge(
                BackendStrings::getCommonStrings(),
                BackendStrings::getSettingsStrings(),
                BackendStrings::getCustomizeStrings()
            )
        );

        wp_localize_script(
            $scriptId,
            'localeLanguage',
            [AMELIA_LOCALE]
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

        include AMELIA_PATH . '/view/backend/view-new.php';
    }

}
