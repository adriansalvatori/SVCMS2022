<?php

namespace AmeliaBooking\Infrastructure\WP\SettingsService;

use AmeliaBooking\Application\Services\Location\CurrentLocation;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsStorageInterface;

/**
 * Class SettingsStorage
 *
 * @package AmeliaBooking\Infrastructure\WP\SettingsService
 */
class SettingsStorage implements SettingsStorageInterface
{
    /** @var array|mixed */
    private $settingsCache;

    /** @var CurrentLocation */
    private $locationService;

    private static $wpSettings = [
        'dateFormat'     => 'date_format',
        'timeFormat'     => 'time_format',
        'startOfWeek'    => 'start_of_week',
        'timeZoneString' => 'timezone_string',
        'gmtOffset'      => 'gmt_offset'
    ];

    /**
     * SettingsStorage constructor.
     */
    public function __construct()
    {
        $this->locationService = new CurrentLocation();
        $this->settingsCache = json_decode(get_option('amelia_settings'), true);
        foreach (self::$wpSettings as $ameliaSetting => $wpSetting) {
            $this->settingsCache['wordpress'][$ameliaSetting] = get_option($wpSetting);
        }

        DateTimeService::setTimeZone($this->getAllSettings());
    }

    /**
     * @param $settingCategoryKey
     * @param $settingKey
     *
     * @return mixed
     */
    public function getSetting($settingCategoryKey, $settingKey)
    {
        return isset($this->settingsCache[$settingCategoryKey][$settingKey]) ?
            $this->settingsCache[$settingCategoryKey][$settingKey] : null;
    }

    /**
     * @param $settingCategoryKey
     *
     * @return mixed
     */
    public function getCategorySettings($settingCategoryKey)
    {
        return isset($this->settingsCache[$settingCategoryKey]) ?
            $this->settingsCache[$settingCategoryKey] : null;
    }

    /**
     * @return array|mixed|null
     */
    public function getAllSettings()
    {
        $settings = [];

        if (null !== $this->settingsCache) {
            foreach ((array)$this->settingsCache as $settingsCategoryName => $settingsCategory) {
                if ($settingsCategoryName !== 'daysOff') {
                    foreach ((array)$settingsCategory as $settingName => $settingValue) {
                        $settings[$settingName] = $settingValue;
                    }
                }
            }

            return $settings;
        }

        return null;
    }

    /**
     * @return array|mixed|null
     */
    public function getAllSettingsCategorized()
    {
        return isset($this->settingsCache) ? $this->settingsCache : null;
    }

    /**
     * Return settings for frontend
     *
     * @return array|mixed
     */
    public function getFrontendSettings()
    {
        $phoneCountryCode = $this->getSetting('general', 'phoneDefaultCountryCode');

        $capabilities = [];
        $additionalCapabilities = [];
        if (is_admin()) {
            $currentScreenId = get_current_screen()->id;
            $currentScreen = substr($currentScreenId, strrpos($currentScreenId, '-') + 1);

            $capabilities = [
                'canRead'        => current_user_can('amelia_read_' . $currentScreen),
                'canReadOthers'  => current_user_can('amelia_read_others_' . $currentScreen),
                'canWrite'       => current_user_can('amelia_write_' . $currentScreen),
                'canWriteOthers' => current_user_can('amelia_write_others_' . $currentScreen),
                'canDelete'      => current_user_can('amelia_delete_' . $currentScreen),
                'canWriteStatus' => current_user_can('amelia_write_status_' . $currentScreen),
            ];

            $additionalCapabilities = [
                'canWriteCustomers' => current_user_can('amelia_write_customers'),
            ];
        }

        $wpUser = wp_get_current_user();

        $userType = 'customer';

        if (in_array('administrator', $wpUser->roles, true) || is_super_admin($wpUser->ID)) {
            $userType = 'admin';
        } elseif (in_array('wpamelia-manager', $wpUser->roles, true)) {
            $userType = 'manager';
        } elseif (in_array('wpamelia-provider', $wpUser->roles, true)) {
            $userType = 'provider';
        }

        return [
            'capabilities'           => $capabilities,
            'additionalCapabilities' => $additionalCapabilities,
            'daysOff'                => $this->getCategorySettings('daysOff'),
            'general'                => [
                'itemsPerPage'                           => $this->getSetting('general', 'itemsPerPage'),
                'appointmentsPerPage'                    => $this->getSetting('general', 'appointmentsPerPage'),
                'servicesPerPage'                        => $this->getSetting('general', 'servicesPerPage'),
                'customersFilterLimit'                   => $this->getSetting('general', 'customersFilterLimit'),
                'phoneDefaultCountryCode'                => $phoneCountryCode === 'auto' ?
                    $this->locationService->getCurrentLocationCountryIso() : $phoneCountryCode,
                'timeSlotLength'                         => $this->getSetting('general', 'timeSlotLength'),
                'serviceDurationAsSlot'                  => $this->getSetting('general', 'serviceDurationAsSlot'),
                'defaultAppointmentStatus'               => $this->getSetting('general', 'defaultAppointmentStatus'),
                'gMapApiKey'                             => $this->getSetting('general', 'gMapApiKey'),
                'addToCalendar'                          => $this->getSetting('general', 'addToCalendar'),
                'requiredPhoneNumberField'               => $this->getSetting('general', 'requiredPhoneNumberField'),
                'requiredEmailField'                     => $this->getSetting('general', 'requiredEmailField'),
                'numberOfDaysAvailableForBooking'        => $this->getSetting('general', 'numberOfDaysAvailableForBooking'),
                'minimumTimeRequirementPriorToBooking'   =>
                    $this->getSetting('general', 'minimumTimeRequirementPriorToBooking'),
                'minimumTimeRequirementPriorToCanceling' =>
                    $this->getSetting('general', 'minimumTimeRequirementPriorToCanceling'),
                'minimumTimeRequirementPriorToRescheduling' =>
                    $this->getSetting('general', 'minimumTimeRequirementPriorToRescheduling'),
                'showClientTimeZone'                     => $this->getSetting('general', 'showClientTimeZone'),
                'redirectUrlAfterAppointment'            => $this->getSetting('general', 'redirectUrlAfterAppointment'),
                'customFieldsUploadsPath'                => $this->getSetting('general', 'customFieldsUploadsPath'),
                'runInstantPostBookingActions'           => $this->getSetting('general', 'runInstantPostBookingActions'),
                'sortingPackages'                        => $this->getSetting('general', 'sortingPackages'),
                'sortingServices'                        => $this->getSetting('general', 'sortingServices'),
                'googleRecaptcha'                              => [
                    'enabled'   => $this->getSetting('general', 'googleRecaptcha')['enabled'],
                    'invisible' => $this->getSetting('general', 'googleRecaptcha')['invisible'],
                    'siteKey'   => $this->getSetting('general', 'googleRecaptcha')['siteKey'],
                ],
                'usedLanguages' => $this->getSetting('general', 'usedLanguages'),
            ],
            'googleCalendar'         =>
                $this->getSetting('googleCalendar', 'clientID') && $this->getSetting('googleCalendar', 'clientSecret'),
            'outlookCalendar'        =>
                $this->getSetting('outlookCalendar', 'clientID') && $this->getSetting('outlookCalendar', 'clientSecret'),
            'zoom'                   => [
                'enabled' => $this->getSetting('zoom', 'enabled') && $this->getSetting('zoom', 'apiKey') && $this->getSetting('zoom', 'apiSecret')
            ],
            'facebookPixel'          => $this->getCategorySettings('facebookPixel'),
            'googleTag'              => $this->getCategorySettings('googleTag'),
            'lessonSpace'            => [
                'enabled' => $this->getSetting('lessonSpace', 'enabled') && $this->getSetting('zoom', 'apiKey')
            ],
            'notifications'          => [
                'senderName'       => $this->getSetting('notifications', 'senderName'),
                'senderEmail'      => $this->getSetting('notifications', 'senderEmail'),
                'notifyCustomers'  => $this->getSetting('notifications', 'notifyCustomers'),
                'sendAllCF'        => $this->getSetting('notifications', 'sendAllCF'),
                'cancelSuccessUrl' => $this->getSetting('notifications', 'cancelSuccessUrl'),
                'cancelErrorUrl'   => $this->getSetting('notifications', 'cancelErrorUrl'),
                'smsSignedIn'      => $this->getSetting('notifications', 'smsSignedIn'),
                'bccEmail'         => $this->getSetting('notifications', 'bccEmail'),
                'bccSms'           => $this->getSetting('notifications', 'bccSms')
            ],
            'payments'               => [
                'currency'                   => $this->getSetting('payments', 'symbol'),
                'currencyCode'               => $this->getSetting('payments', 'currency'),
                'priceSymbolPosition'        => $this->getSetting('payments', 'priceSymbolPosition'),
                'priceNumberOfDecimals'      => $this->getSetting('payments', 'priceNumberOfDecimals'),
                'priceSeparator'             => $this->getSetting('payments', 'priceSeparator'),
                'hideCurrencySymbolFrontend' => $this->getSetting('payments', 'hideCurrencySymbolFrontend'),
                'defaultPaymentMethod'       => $this->getSetting('payments', 'defaultPaymentMethod'),
                'onSite'                     => $this->getSetting('payments', 'onSite'),
                'coupons'                    => $this->getSetting('payments', 'coupons'),
                'payPal'                     => [
                    'enabled'         => $this->getSetting('payments', 'payPal')['enabled'],
                    'sandboxMode'     => $this->getSetting('payments', 'payPal')['sandboxMode'],
                    'testApiClientId' => $this->getSetting('payments', 'payPal')['testApiClientId'],
                    'liveApiClientId' => $this->getSetting('payments', 'payPal')['liveApiClientId'],
                ],
                'stripe'                     => [
                    'enabled'            => $this->getSetting('payments', 'stripe')['enabled'],
                    'testMode'           => $this->getSetting('payments', 'stripe')['testMode'],
                    'livePublishableKey' => $this->getSetting('payments', 'stripe')['livePublishableKey'],
                    'testPublishableKey' => $this->getSetting('payments', 'stripe')['testPublishableKey']

                ],
                'wc'                         => [
                    'enabled'      => $this->getSetting('payments', 'wc')['enabled'],
                    'productId'    => $this->getSetting('payments', 'wc')['productId'],
                    'page'         => $this->getSetting('payments', 'wc')['page'],
                    'onSiteIfFree' => $this->getSetting('payments', 'wc')['onSiteIfFree']
                ],
                'mollie'                     => [
                    'enabled'   => $this->getSetting('payments', 'mollie')['enabled'],
                ],
                'razorpay'                     => [
                    'enabled'   => $this->getSetting('payments', 'razorpay')['enabled'],
                ],
            ],
            'role'                   => $userType,
            'weekSchedule'           => $this->getCategorySettings('weekSchedule'),
            'wordpress'              => [
                'dateFormat'  => $this->getSetting('wordpress', 'dateFormat'),
                'timeFormat'  => $this->getSetting('wordpress', 'timeFormat'),
                'startOfWeek' => (int)$this->getSetting('wordpress', 'startOfWeek'),
                'timezone'    => $this->getSetting('wordpress', 'timeZoneString'),
            ],
            'labels'                 => [
                'enabled' => $this->getSetting('labels', 'enabled')
            ],
            'activation'             => [
                'enableTrafftBanner'     => $this->getSetting('activation', 'enableTrafftBanner'),
                'showActivationSettings' => $this->getSetting('activation', 'showActivationSettings'),
                'stash'                  => $this->getSetting('activation', 'stash'),
                'disableUrlParams'       => $this->getSetting('activation', 'disableUrlParams'),
            ],
            'roles'                  => [
                'allowAdminBookAtAnyTime'     => $this->getSetting('roles', 'allowAdminBookAtAnyTime'),
                'allowConfigureSchedule'      => $this->getSetting('roles', 'allowConfigureSchedule'),
                'allowConfigureDaysOff'       => $this->getSetting('roles', 'allowConfigureDaysOff'),
                'allowConfigureSpecialDays'   => $this->getSetting('roles', 'allowConfigureSpecialDays'),
                'allowConfigureServices'      => $this->getSetting('roles', 'allowConfigureServices'),
                'allowWriteAppointments'      => $this->getSetting('roles', 'allowWriteAppointments'),
                'automaticallyCreateCustomer' => $this->getSetting('roles', 'automaticallyCreateCustomer'),
                'inspectCustomerInfo'         => $this->getSetting('roles', 'inspectCustomerInfo'),
                'allowCustomerReschedule'     => $this->getSetting('roles', 'allowCustomerReschedule'),
                'allowCustomerDeleteProfile'  => $this->getSetting('roles', 'allowCustomerDeleteProfile'),
                'allowWriteEvents'            => $this->getSetting('roles', 'allowWriteEvents'),
                'customerCabinet'             => [
                    'enabled'        => $this->getSetting('roles', 'customerCabinet')['enabled'],
                    'loginEnabled'   => $this->getSetting('roles', 'customerCabinet')['loginEnabled'],
                    'tokenValidTime' => $this->getSetting('roles', 'customerCabinet')['tokenValidTime'],
                    'pageUrl'        => $this->getSetting('roles', 'customerCabinet')['pageUrl'],
                ],
                'providerCabinet'             => [
                    'enabled'        => $this->getSetting('roles', 'providerCabinet')['enabled'],
                    'loginEnabled'   => $this->getSetting('roles', 'providerCabinet')['loginEnabled'],
                    'tokenValidTime' => $this->getSetting('roles', 'providerCabinet')['tokenValidTime'],
                ],
            ],
            'customization'          => $this->getCategorySettings('customization'),
            'customizedData'         => $this->getCategorySettings('customizedData'),
            'appointments'           => $this->getCategorySettings('appointments'),
            'slotDateConstraints'    => [
                'minDate' => DateTimeService::getNowDateTimeObject()
                    ->modify("+{$this->getSetting('general', 'minimumTimeRequirementPriorToBooking')} seconds")
                    ->format('Y-m-d H:i:s'),
                'maxDate' => DateTimeService::getNowDateTimeObject()
                    ->modify("+{$this->getSetting('general', 'numberOfDaysAvailableForBooking')} day")
                    ->format('Y-m-d H:i:s')
            ],
            'company'                => [
                'email' => $this->getSetting('company', 'email'),
            ]
        ];
    }

    /**
     * @param $settingCategoryKey
     * @param $settingKey
     * @param $settingValue
     *
     * @return mixed|void
     */
    public function setSetting($settingCategoryKey, $settingKey, $settingValue)
    {
        $this->settingsCache[$settingCategoryKey][$settingKey] = $settingValue;
        $settingsCopy = $this->settingsCache;
        unset($settingsCopy['wordpress']);
        update_option('amelia_settings', json_encode($settingsCopy));
    }

    /**
     * @param $settingCategoryKey
     * @param $settingValues
     *
     * @return mixed|void
     */
    public function setCategorySettings($settingCategoryKey, $settingValues)
    {
        $this->settingsCache[$settingCategoryKey] = $settingValues;
        $settingsCopy = $this->settingsCache;
        unset($settingsCopy['wordpress']);
        update_option('amelia_settings', json_encode($settingsCopy));
    }

    /**
     * @param array $settings
     *
     * @return mixed|void
     */
    public function setAllSettings($settings)
    {
        foreach ($settings as $settingCategoryKey => $settingValues) {
            $this->settingsCache[$settingCategoryKey] = $settingValues;
        }
        $settingsCopy = $this->settingsCache;
        unset($settingsCopy['wordpress']);
        update_option('amelia_settings', json_encode($settingsCopy));
    }
}
