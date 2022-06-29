<?php

namespace AmeliaBooking\Infrastructure\WP\Integrations\WooCommerce;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\BookingCancellationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Booking\Event\EventTicket;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageFactory;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\DepositType;
use AmeliaBooking\Domain\ValueObjects\String\PaymentStatus;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentEditedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\BookingEditedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\PackageCustomerUpdatedEventHandler;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Interop\Container\Exception\ContainerException;

/**
 * Class WooCommerceService
 *
 * @package AmeliaBooking\Infrastructure\WP\Integrations\WooCommerce
 */
class WooCommerceService
{
    /** @var Container $container */
    public static $container;

    /** @var SettingsService $settingsService */
    public static $settingsService;

    /** @var array $checkout_info */
    protected static $checkout_info = [];

    /** @var boolean $isProcessing */
    protected static $isProcessing = false;

    const AMELIA = 'ameliabooking';

    /**
     * Init
     *
     * @param $settingsService
     */
    public static function init($settingsService)
    {
        self::setContainer(require AMELIA_PATH . '/src/Infrastructure/ContainerConfig/container.php');
        self::$settingsService = $settingsService;

        add_action('woocommerce_before_cart_contents', [self::class, 'beforeCartContents'], 10, 0);
        add_filter('woocommerce_get_item_data', [self::class, 'getItemData'], 10, 2);
        add_filter('woocommerce_cart_item_price', [self::class, 'cartItemPrice'], 10, 3);
        add_filter('woocommerce_checkout_get_value', [self::class, 'checkoutGetValue'], 10, 2);

        if (self::isEnabled() && version_compare(wc()->version, '3.0', '>=')) {
            add_action('woocommerce_checkout_create_order_line_item', [self::class, 'checkoutCreateOrderLineItem'], 10, 4);
        } else {
            add_filter('woocommerce_add_order_item_meta', [self::class, 'addOrderItemMeta'], 10, 3);
        }

        add_filter('woocommerce_order_item_meta_end', [self::class, 'orderItemMeta'], 10, 3);
        add_filter('woocommerce_after_order_itemmeta', [self::class, 'orderItemMeta'], 10, 3);

        $wcSettings = self::$settingsService->getCategorySettings('payments')['wc'];

        if (empty($wcSettings['rules']['appointment']) &&
            empty($wcSettings['rules']['package']) &&
            empty($wcSettings['rules']['event'])
        ) {
            add_action('woocommerce_order_status_completed', [self::class, 'orderStatusChanged'], 10, 1);
            add_action('woocommerce_order_status_on-hold', [self::class, 'orderStatusChanged'], 10, 1);
            add_action('woocommerce_order_status_processing', [self::class, 'orderStatusChanged'], 10, 1);
        } else {
            add_action("woocommerce_order_status_pending", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_on-hold", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_processing", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_completed", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_cancelled", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_refunded", [self::class, 'orderStatusChanged'], 10, 1);
            add_action("woocommerce_order_status_failed", [self::class, 'orderStatusChanged'], 10, 1);
        }

        add_filter('woocommerce_thankyou', [self::class, 'redirectAfterOrderReceived'], 10, 2);

        add_action('woocommerce_before_checkout_process', [self::class, 'beforeCheckoutProcess'], 10, 1);
        add_filter('woocommerce_before_calculate_totals', [self::class, 'beforeCalculateTotals'], 10, 3);
    }

    /**
     * @param $cart_obj
     *
     */
    public static function beforeCalculateTotals($cart_obj) {
        $wooCommerceCart = self::getWooCommerceCart();

        if (!$wooCommerceCart) {
            return;
        }

        foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
            if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
                $product_price = self::getReservationPaymentAmount($wc_item[self::AMELIA]);

                /** @var \WC_Product $wc_item ['data'] */
                $wc_item['data']->set_price($product_price >= 0 ? $product_price : 0);
            }
        }
    }

    /**
     * Set Amelia Container
     *
     * @param $container
     */
    public static function setContainer($container)
    {
        self::$container = $container;
    }

    /**
     * Get cart page
     *
     * @param string $locale
     * @return string
     */
    public static function getPageUrl($locale = '')
    {
        $locale = $locale ? explode('_', $locale) : null;

        switch (self::$settingsService->getCategorySettings('payments')['wc']['page']) {
            case 'checkout':
                if (!empty($locale[0]) &&
                    function_exists('icl_object_id') &&
                    ($url = apply_filters('wpml_permalink', get_permalink(get_option('woocommerce_checkout_page_id')), $locale[0], true))
                ) {
                    return $url;
                }

                return wc_get_checkout_url();
            case 'cart':
                if (!empty($locale[0]) &&
                    function_exists('icl_object_id') &&
                    ($url = apply_filters('wpml_permalink', get_permalink(get_option('woocommerce_cart_page_id')), $locale[0], true))
                ) {
                    return $url;
                }

                return wc_get_cart_url();
            default:
                $locale = defined(AMELIA_LOCALE) ? explode('_', AMELIA_LOCALE) : null;

                if (!empty($locale[0]) &&
                    function_exists('icl_object_id') &&
                    ($url = apply_filters('wpml_permalink', get_permalink(get_option('woocommerce_cart_page_id')), $locale[0], true))
                ) {
                    return $url;
                }

                return wc_get_cart_url();
        }
    }

    /**
     * Get WooCommerce Cart
     */
    private static function getWooCommerceCart()
    {
        return wc()->cart;
    }

    /**
     * Is WooCommerce enabled
     *
     * @return string
     */
    public static function isEnabled()
    {
        return class_exists('WooCommerce');
    }

    /**
     * Get product id from settings
     *
     * @return int
     */
    private static function getProductIdFromSettings()
    {
        return self::$settingsService->getCategorySettings('payments')['wc']['productId'];
    }

    /**
     * Validate appointment booking
     *
     * @param array $data
     *
     * @return bool
     */
    private static function validateBooking($data)
    {
        try {
            $errorMessage = '';

            if ($data) {
                /** @var CommandResult $result */
                $result = new CommandResult();

                /** @var ReservationServiceInterface $reservationService */
                $reservationService = self::$container->get('application.reservation.service')->get($data['type']);

                $data['bookings'][0]['customFields'] =
                    $data['bookings'][0]['customFields'] && is_array($data['bookings'][0]['customFields'])
                        ? json_encode($data['bookings'][0]['customFields']) : '';

                $reservation = $reservationService->getNew(true, false, true);

                /** @var AppointmentRepository $appointmentRepo */
                $reservationService->processBooking($result, $data, $reservation, false);

                if ($result->getResult() === CommandResult::RESULT_ERROR) {
                    if (isset($result->getData()['emailError'])) {
                        $errorMessage = FrontendStrings::getCommonStrings()['email_exist_error'];
                    }

                    if (isset($result->getData()['couponUnknown'])) {
                        $errorMessage = FrontendStrings::getCommonStrings()['coupon_unknown'];
                    }

                    if (isset($result->getData()['couponInvalid'])) {
                        $errorMessage = FrontendStrings::getCommonStrings()['coupon_invalid'];
                    }

                    if (isset($result->getData()['customerAlreadyBooked'])) {
                        switch ($data['type']) {
                            case (Entities::APPOINTMENT):
                            case (Entities::PACKAGE):
                                $errorMessage = FrontendStrings::getCommonStrings()['customer_already_booked_app'];

                                break;

                            case (Entities::EVENT):
                                $errorMessage = FrontendStrings::getCommonStrings()['customer_already_booked_ev'];

                                break;
                        }
                    }

                    if (isset($result->getData()['timeSlotUnavailable'])) {
                        $errorMessage = FrontendStrings::getCommonStrings()['time_slot_unavailable'];
                    }

                    return $errorMessage ?
                        "$errorMessage (<strong>{$data['serviceName']}</strong>). " : '';
                }

                return '';
            }
        } catch (ContainerException $e) {
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get existing, or new created product id
     *
     * @param array $params
     * @return array
     */
    public static function getAllProducts($params)
    {
        $params = array_merge(['post_type' => 'product', 'posts_per_page' => -1], $params);

        $products = [];

        foreach (get_posts($params) as $product) {
            $products[] = [
                'id'   => $product->ID,
                'name' => $product->post_title,
            ];
        }

        return $products;
    }

    /**
     * Get initial products
     *
     * @return array
     */
    public static function getInitialProducts()
    {
        $products = self::getAllProducts(
            [
                'posts_per_page' => 50,
            ]
        );

        $product = self::getAllProducts(
            [
                'include' => self::getProductIdFromSettings()
            ]
        );

        if ($product && !in_array($product[0]['id'], array_column($products, 'id'))) {
            $products[] = $product[0];
        }

        return $products;
    }

    /**
     * Save appointment booking
     *
     * @param array $data
     * @param mixed $orderItem
     *
     * @return CustomerBooking|null
     */
    private static function saveBooking($data, $orderItem)
    {
        try {
            /** @var ReservationServiceInterface $reservationService */
            $reservationService = self::$container->get('application.reservation.service')->get($data['type']);

            $reservation = $reservationService->getNew(false, false, false);

            $result = $reservationService->processRequest($data, $reservation, true);

            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = self::$container->get('domain.payment.repository');

            /** @var Collection $payments */
            $payments = $paymentRepository->getByEntityId($data['payment']['wcOrderItemId'], 'wcOrderId');

            /** @var Payment $payment */
            foreach ($payments->getItems() as $payment) {
                $paymentRepository->updateFieldById(
                    $payment->getId()->getValue(),
                    $orderItem->get_total() + $orderItem->get_total_tax(),
                    'amount'
                );
            }

            $reservationService->runPostBookingActions($result);
        } catch (ContainerException $e) {
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Get existing, or new created product id
     *
     * @param $postId
     *
     * @return int|\WP_Error
     */
    public static function getIdForExistingOrNewProduct($postId)
    {
        if (!in_array($postId, array_column(self::getAllProducts([]), 'id'))) {
            $params = [
                'post_title'   => FrontendStrings::getCommonStrings()['wc_product_name'],
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'product',
            ];

            if (function_exists('get_current_user')) {
                $params['post_author'] = get_current_user();
            }

            $postId = wp_insert_post($params);


            wp_set_object_terms($postId, 'simple', 'product_type');
            wp_set_object_terms($postId, ['exclude-from-catalog', 'exclude-from-search'], 'product_visibility');
            update_post_meta($postId, '_visibility', 'hidden');
            update_post_meta($postId, '_stock_status', 'instock');
            update_post_meta($postId, 'total_sales', '0');
            update_post_meta($postId, '_downloadable', 'no');
            update_post_meta($postId, '_virtual', 'yes');
            update_post_meta($postId, '_regular_price', 0);
            update_post_meta($postId, '_sale_price', '');
            update_post_meta($postId, '_purchase_note', '');
            update_post_meta($postId, '_featured', 'no');
            update_post_meta($postId, '_weight', '');
            update_post_meta($postId, '_length', '');
            update_post_meta($postId, '_width', '');
            update_post_meta($postId, '_height', '');
            update_post_meta($postId, '_sku', '');
            update_post_meta($postId, '_product_attributes', array());
            update_post_meta($postId, '_sale_price_dates_from', '');
            update_post_meta($postId, '_sale_price_dates_to', '');
            update_post_meta($postId, '_price', 0);
            update_post_meta($postId, '_sold_individually', 'yes');
            update_post_meta($postId, '_manage_stock', 'no');
            update_post_meta($postId, '_backorders', 'no');
            update_post_meta($postId, '_stock', '');
        }

        return $postId;
    }

    /**
     * Fetch entity if not in cache
     *
     * @param $data
     *
     * @return array
     */
    private static function getEntity($data)
    {
        if (!Cache::get($data)) {
            self::populateCache([$data]);
        }

        return Cache::get($data);
    }

    /**
     * @param float $paymentAmount
     * @param array $bookableData
     * @param int   $persons
     *
     * @return float
     */
    private static function calculateDepositAmount($paymentAmount, $bookableData, $persons)
    {
        if ($bookableData['depositPayment'] !== DepositType::DISABLED) {
            switch ($bookableData['depositPayment']) {
                case DepositType::FIXED:
                    if ($bookableData['depositPerPerson']) {
                        if ($paymentAmount > $persons * $bookableData['deposit']) {
                            return $persons * $bookableData['deposit'];
                        }
                    } else {
                        if ($paymentAmount > $bookableData['deposit']) {
                            return $bookableData['deposit'];
                        }
                    }

                    break;

                case DepositType::PERCENTAGE:
                    $depositAmount = round($paymentAmount / 100 * $bookableData['deposit'], 2);

                    if ($paymentAmount > $depositAmount) {
                        return $depositAmount;
                    }

                    break;
            }
        }

        return $paymentAmount;
    }

    /**
     * Get payment amount for reservation
     *
     * @param $wcItemAmeliaCache
     *
     * @return float
     */
    private static function getReservationPaymentAmount($wcItemAmeliaCache)
    {
        $bookableData = self::getEntity($wcItemAmeliaCache);

        $paymentAmount = 0;

        switch ($wcItemAmeliaCache['type']) {
            case (Entities::APPOINTMENT):
                $paymentAmount = self::getBookingPaymentAmount($wcItemAmeliaCache, $bookableData);

                if ($wcItemAmeliaCache['bookings'][0]['deposit']) {
                    $paymentAmount = self::calculateDepositAmount(
                        $paymentAmount,
                        $bookableData['bookable'],
                        $wcItemAmeliaCache['bookings'][0]['persons']
                    );
                }

                foreach ($wcItemAmeliaCache['recurring'] as $index => $recurringReservation) {
                    $recurringBookable = self::getEntity(
                        array_merge(
                            $wcItemAmeliaCache,
                            $recurringReservation
                        )
                    );

                    if ($index < $bookableData['bookable']['recurringPayment']) {
                        $recurringPaymentAmount = self::getBookingPaymentAmount(
                            array_merge(
                                $wcItemAmeliaCache,
                                [
                                    'couponId' => $wcItemAmeliaCache['recurring'][$index]['couponId']
                                ]
                            ),
                            $recurringBookable
                        );

                        if ($wcItemAmeliaCache['recurring'][$index]['deposit']) {
                            $recurringPaymentAmount = self::calculateDepositAmount(
                                $recurringPaymentAmount,
                                $recurringBookable['bookable'],
                                $wcItemAmeliaCache['bookings'][0]['persons']
                            );
                        }

                        $paymentAmount += $recurringPaymentAmount;
                    }
                }

                break;

            case (Entities::EVENT):
                $paymentAmount = self::getBookingPaymentAmount($wcItemAmeliaCache, $bookableData);

                if ($wcItemAmeliaCache['bookings'][0]['deposit']) {
                    $personsCount = $wcItemAmeliaCache['bookings'][0]['persons'];

                    if (!empty($wcItemAmeliaCache['bookings'][0]['ticketsData'])) {
                        $personsCount = 0;

                        foreach ($wcItemAmeliaCache['bookings'][0]['ticketsData'] as $ticketData) {
                            $personsCount += $ticketData['persons'] ? (int)$ticketData['persons'] : 0;
                        }
                    }

                    $paymentAmount = self::calculateDepositAmount(
                        $paymentAmount,
                        $bookableData['bookable'],
                        $personsCount
                    );
                }

                break;

            case (Entities::PACKAGE):
                $paymentAmount = self::getPackagePaymentAmount($wcItemAmeliaCache, $bookableData);

                if ($wcItemAmeliaCache['deposit']) {
                    $paymentAmount = self::calculateDepositAmount(
                        $paymentAmount,
                        $bookableData['bookable'],
                        1
                    );
                }

                break;
        }

        $paymentAmount = apply_filters('amelia_get_modified_price', $paymentAmount, $wcItemAmeliaCache, $bookableData);

        return $paymentAmount;
    }

    /**
     * Get payment amount for booking
     *
     * @param $wcItemAmeliaCache
     * @param $booking
     *
     * @return float
     */
    private static function getBookingPaymentAmount($wcItemAmeliaCache, $booking)
    {
        $extras = [];

        foreach ((array)$wcItemAmeliaCache['bookings'][0]['extras'] as $extra) {
            $extras[] = [
                'price'           => $booking['extras'][$extra['extraId']]['price'],
                'aggregatedPrice' => $booking['extras'][$extra['extraId']]['aggregatedPrice'],
                'quantity'        => $extra['quantity']
            ];
        }

        $price = (float)$booking['bookable']['price'] *
            ($booking['bookable']['aggregatedPrice'] ? $wcItemAmeliaCache['bookings'][0]['persons'] : 1);

        if (!empty($wcItemAmeliaCache['bookings'][0]['ticketsData'])) {
            $ticketSumPrice = 0;

            foreach ($wcItemAmeliaCache['bookings'][0]['ticketsData'] as $ticketData) {
                $ticketPrice = $booking['bookable']['customTickets'][$ticketData['eventTicketId']]['dateRangePrice'] !== null ?
                    $booking['bookable']['customTickets'][$ticketData['eventTicketId']]['dateRangePrice'] :
                    $booking['bookable']['customTickets'][$ticketData['eventTicketId']]['price'];

                $ticketSumPrice += $ticketData['persons'] ?
                    (int)$ticketData['persons'] * $ticketPrice : 0;
            }

            $price = $ticketSumPrice;
        }

        foreach ($extras as $extra) {
            // if extra is not set (NULL), use service aggregated price value (compatibility with old version)
            $isExtraAggregatedPrice = $extra['aggregatedPrice'] === null ? $booking['bookable']['aggregatedPrice'] :
                $extra['aggregatedPrice'];

            $price += (float)$extra['price'] *
                ($isExtraAggregatedPrice ? $wcItemAmeliaCache['bookings'][0]['persons'] : 1) *
                $extra['quantity'];
        }

        if ($wcItemAmeliaCache['couponId'] && isset($booking['coupons'][$wcItemAmeliaCache['couponId']])) {
            $subtraction = $price / 100 *
                ($wcItemAmeliaCache['couponId'] ? $booking['coupons'][$wcItemAmeliaCache['couponId']]['discount'] : 0) +
                ($wcItemAmeliaCache['couponId'] ? $booking['coupons'][$wcItemAmeliaCache['couponId']]['deduction'] : 0);

            return round($price - $subtraction, 2);
        }

        return $price;
    }

    /**
     * Get payment amount for package
     *
     * @param $wcItemAmeliaCache
     * @param $booking
     *
     * @return float
     */
    private static function getPackagePaymentAmount($wcItemAmeliaCache, $booking)
    {
        $price = (float)$booking['bookable']['price'];

        if (!$booking['bookable']['calculatedPrice'] && $booking['bookable']['discount']) {
            $subtraction = $price / 100 * $booking['bookable']['discount'];

            return round($price - $subtraction, 2);
        }

        return $price;
    }

    /**
     * Fetch entities from DB and set them into cache
     *
     * @param array  $ameliaEntitiesIds
     */
    private static function populateCache($ameliaEntitiesIds)
    {
        $appointmentEntityIds = [];

        $eventEntityIds = [];

        $packageEntityIds = [];

        foreach ($ameliaEntitiesIds as $ids) {
            switch ($ids['type']) {
                case (Entities::APPOINTMENT):
                    $appointmentEntityIds[] = [
                        'serviceId'  => $ids['serviceId'],
                        'providerId' => $ids['providerId'],
                        'couponId'   => !empty($ids['couponId']) ? $ids['couponId'] : null,
                    ];

                    if (!empty($ids['package'])) {
                        foreach ($ids['package'] as $packageIds) {
                            $appointmentEntityIds[] = [
                                'serviceId'  => $packageIds['serviceId'],
                                'providerId' => $packageIds['providerId'],
                                'couponId'   => null
                            ];
                        }
                    }

                    break;

                case (Entities::EVENT):
                    $eventEntityIds[] = [
                        'eventId'    => $ids['eventId'],
                        'couponId'   => $ids['couponId'],
                    ];
                    break;

                case (Entities::PACKAGE):
                    $packageEntityIds[] = [
                        'packageId'    => $ids['packageId'],
                    ];
                    break;
            }
        }

        if ($appointmentEntityIds) {
            self::fetchAppointmentEntities($appointmentEntityIds);
        }

        if ($eventEntityIds) {
            self::fetchEventEntities($eventEntityIds);
        }

        if ($packageEntityIds) {
            self::fetchPackageEntities($packageEntityIds);
        }
    }

    /**
     * Fetch entities from DB and set them into cache
     *
     * @param $ameliaEntitiesIds
     */
    private static function fetchEventEntities($ameliaEntitiesIds)
    {
        try {
            /** @var EventRepository $eventRepository */
            $eventRepository = self::$container->get('domain.booking.event.repository');

            /** @var Collection $events */
            $events = $eventRepository->getWithCoupons($ameliaEntitiesIds);

            $bookings = [];

            foreach ((array)$events->keys() as $eventKey) {
                /** @var Event $event */
                $event = $events->getItem($eventKey);

                $bookings[$eventKey] = [
                    'bookable'   => [
                        'type'             => Entities::EVENT,
                        'name'             => $event->getName()->getValue(),
                        'translations'     => $event->getTranslations() ? $event->getTranslations()->getValue() : null,
                        'description'      => $event->getDescription() ? $event->getDescription()->getValue() : null,
                        'price'            => $event->getPrice()->getValue(),
                        'aggregatedPrice'  => true,
                        'recurringPayment' => 0,
                        'locationId'       => $event->getLocationId() ? $event->getLocationId()->getValue() : null,
                        'customLocation'   => $event->getCustomLocation() ? $event->getCustomLocation()->getValue() : null,
                        'providers'        => $event->getProviders()->length() ? $event->getProviders()->toArray() : [],
                        'depositPayment'   => $event->getDepositPayment()->getValue(),
                        'deposit'          => $event->getDeposit()->getValue(),
                        'depositPerPerson' => $event->getDepositPerPerson()->getValue(),
                        'customTickets'    => [],
                    ],
                    'coupons'   => []
                ];

                /** @var Collection $coupons */
                $coupons = $event->getCoupons();

                foreach ((array)$coupons->keys() as $couponKey) {
                    /** @var Coupon $coupon */
                    $coupon = $coupons->getItem($couponKey);

                    $bookings[$eventKey]['coupons'][$coupon->getId()->getValue()] = [
                        'deduction' => $coupon->getDeduction()->getValue(),
                        'discount'  => $coupon->getDiscount()->getValue(),
                    ];
                }

                /** @var EventApplicationService $eventApplicationService */
                $eventAS = self::$container->get('application.booking.event.service');

                if ($event->getCustomPricing()->getValue()) {
                    $event->setCustomTickets($eventAS->getTicketsPriceByDateRange($event->getCustomTickets()));
                }

                /** @var Collection $customTickets */
                $customTickets = $event->getCustomTickets();

                /** @var EventTicket $customTicket */
                foreach ($customTickets->getItems() as $customTicket) {
                    $bookings[$eventKey]['bookable']['customTickets'][$customTicket->getId()->getValue()] = [
                        'price'          => $customTicket->getPrice()->getValue(),
                        'dateRangePrice' => $customTicket->getDateRangePrice() ?
                            $customTicket->getDateRangePrice()->getValue() : null,
                    ];
                }
            }

            Cache::add(Entities::EVENT, $bookings);
        } catch (\Exception $e) {
        } catch (ContainerException $e) {
        }
    }

    /**
     * Fetch entities from DB and set them into cache
     *
     * @param $ameliaEntitiesIds
     */
    private static function fetchPackageEntities($ameliaEntitiesIds)
    {
        try {
            /** @var PackageRepository $packageRepository */
            $packageRepository = self::$container->get('domain.bookable.package.repository');

            /** @var Package $package */
            $package = $packageRepository->getById($ameliaEntitiesIds[0]['packageId']);

            $bookings = [];

            $bookings[$package->getId()->getValue()] = [
                'bookable'   => [
                    'type'             => Entities::PACKAGE,
                    'name'             => $package->getName()->getValue(),
                    'translations'     => $package->getTranslations() ? $package->getTranslations()->getValue() : null,
                    'description'      => $package->getDescription() ? $package->getDescription()->getValue() : null,
                    'price'            => $package->getPrice()->getValue(),
                    'discount'         => $package->getDiscount()->getValue(),
                    'calculatedPrice'  => $package->getCalculatedPrice()->getValue(),
                    'depositPayment'   => $package->getDepositPayment()->getValue(),
                    'deposit'          => $package->getDeposit()->getValue(),
                    'depositPerPerson' => null,
                ],
                'coupons'   => []
            ];

            Cache::add(Entities::PACKAGE, $bookings);
        } catch (\Exception $e) {
        } catch (ContainerException $e) {
        }
    }


    /**
     * Fetch entities from DB and set them into cache
     *
     * @param $ameliaEntitiesIds
     */
    private static function fetchAppointmentEntities($ameliaEntitiesIds)
    {
        try {
            /** @var ProviderRepository $providerRepository */
            $providerRepository = self::$container->get('domain.users.providers.repository');

            /** @var Collection $providers */
            $providers = $providerRepository->getWithServicesAndExtrasAndCoupons($ameliaEntitiesIds);

            $bookings = [];

            foreach ((array)$providers->keys() as $providerKey) {
                /** @var Provider $provider */
                $provider = $providers->getItem($providerKey);

                /** @var Collection $services */
                $services = $provider->getServiceList();

                foreach ((array)$services->keys() as $serviceKey) {
                    /** @var Service $service */
                    $service = $services->getItem($serviceKey);

                    /** @var Collection $extras */
                    $extras = $service->getExtras();

                    $bookings[$providerKey][$serviceKey] = [
                        'firstName' => $provider->getFirstName()->getValue(),
                        'lastName'  => $provider->getLastName()->getValue(),
                        'bookable'   => [
                            'type'             => Entities::APPOINTMENT,
                            'id'               => $service->getId()->getValue(),
                            'name'             => $service->getName()->getValue(),
                            'price'            => $service->getPrice()->getValue(),
                            'aggregatedPrice'  => $service->getAggregatedPrice()->getValue(),
                            'recurringPayment' => $service->getRecurringPayment() ?
                                $service->getRecurringPayment()->getValue() : null,
                            'duration'         => $service->getDuration()->getValue(),
                            'depositPayment'   => $service->getDepositPayment()->getValue(),
                            'deposit'          => $service->getDeposit()->getValue(),
                            'depositPerPerson' => $service->getDepositPerPerson()->getValue(),
                        ],
                        'coupons'   => [],
                        'extras'    => []
                    ];

                    foreach ((array)$extras->keys() as $extraKey) {
                        /** @var Extra $extra */
                        $extra = $extras->getItem($extraKey);

                        $bookings[$providerKey][$serviceKey]['extras'][$extra->getId()->getValue()] = [
                            'price'           => $extra->getPrice()->getValue(),
                            'name'            => $extra->getName()->getValue(),
                            'aggregatedPrice' => $extra->getAggregatedPrice() ? $extra->getAggregatedPrice()->getValue() : null,
                        ];
                    }

                    /** @var Collection $coupons */
                    $coupons = $service->getCoupons();

                    foreach ((array)$coupons->keys() as $couponKey) {
                        /** @var Coupon $coupon */
                        $coupon = $coupons->getItem($couponKey);

                        $bookings[$providerKey][$serviceKey]['coupons'][$coupon->getId()->getValue()] = [
                            'deduction' => $coupon->getDeduction()->getValue(),
                            'discount'  => $coupon->getDiscount()->getValue(),
                        ];
                    }
                }
            }

            Cache::add(Entities::APPOINTMENT, $bookings);
        } catch (\Exception $e) {
        } catch (ContainerException $e) {
        }
    }

    /**
     * Process data for amelia cart items
     *
     * @param bool $inspectData
     */
    private static function processCart($inspectData)
    {
        $wooCommerceCart = self::getWooCommerceCart();

        if (!$wooCommerceCart) {
            return;
        }

        $ameliaEntitiesIds = [];

        if (!Cache::getAll()) {
            foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
                if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
                    if ($inspectData && ($errorMessage = self::validateBooking($wc_item[self::AMELIA]))) {
                        wc_add_notice(
                            $errorMessage . FrontendStrings::getCommonStrings()['wc_appointment_is_removed'],
                            'error'
                        );
                        $wooCommerceCart->remove_cart_item($wc_key);
                    }

                    $ameliaEntitiesIds[] = $wc_item[self::AMELIA];
                }
            }

            if ($ameliaEntitiesIds) {
                self::populateCache($ameliaEntitiesIds);
            }
        }

        if (!WC()->is_rest_api_request()) {
            foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
                if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
                    $product_price = self::getReservationPaymentAmount($wc_item[self::AMELIA]);

                    /** @var \WC_Product $wc_item ['data'] */
                    $wc_item['data']->set_price($product_price >= 0 ? $product_price : 0);
                }
            }

            $wooCommerceCart->calculate_totals();
        }

        if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
            wc_print_notices();
        }
    }

    /**
     * Add appointment booking to cart
     *
     * @param array    $data
     * @param int|null $productId
     *
     * @return boolean
     * @throws \Exception
     */
    public static function addToCart($data, $productId)
    {
        $wooCommerceCart = self::getWooCommerceCart();

        if (!$wooCommerceCart) {
            return false;
        }

        foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
            if (isset($wc_item[self::AMELIA])) {
                $wooCommerceCart->remove_cart_item($wc_key);
            }
        }

        $wooCommerceCart->add_to_cart($productId ?: self::getProductIdFromSettings(), 1, '', [], [self::AMELIA => $data]);

        return true;
    }

    /**
     * Verifies the availability of all appointments that are in the cart
     */
    public static function beforeCartContents()
    {
        self::processCart(true);
    }

    /**
     * Get Booking Start in site locale
     *
     * @param $timeStamp
     *
     * @return string
     */
    private static function getBookingStartString ($timeStamp) {
        $wooCommerceSettings = self::$settingsService->getCategorySettings('wordpress');

        return date_i18n($wooCommerceSettings['dateFormat'] . ' ' . $wooCommerceSettings['timeFormat'], $timeStamp);
    }

    /**
     * Get Booking Start in site locale
     *
     * @param array $dateStrings
     * @param int   $utcOffset
     * @param string   $type
     *
     * @return array
     */
    private static function getDateInfo($dateStrings, $utcOffset, $type) {
        $clientZoneBookingStart = null;

        $timeInfo = [];

        foreach ($dateStrings as $dateString) {
            $start = self::getBookingStartString(
                \DateTime::createFromFormat('Y-m-d H:i', substr($dateString['start'], 0, 16))->getTimestamp()
            );

            $end = $dateString['end'] && $type === Entities::EVENT ? $end = self::getBookingStartString(
                \DateTime::createFromFormat('Y-m-d H:i', substr($dateString['end'], 0, 16))->getTimestamp()
            ) : '';

            $timeInfo[] = '<strong>' . FrontendStrings::getCommonStrings()['time_colon'] . '</strong> '
                . $start . ($end ? ' - ' . $end : '');
        }

        foreach ($dateStrings as $dateString) {
            if ($utcOffset !== null) {
                $clientZoneStart = self::getBookingStartString(
                    DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc(substr($dateString['start'], 0, 16)),
                        $utcOffset
                    )->getTimestamp()
                );

                $clientZoneEnd = $dateString['end'] && $type === Entities::EVENT ? self::getBookingStartString(
                    DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc(substr($dateString['end'], 0, 16)),
                        $utcOffset
                    )->getTimestamp()
                ) : '';

                $utcString = '(UTC' . ($utcOffset < 0 ? '-' : '+') .
                    sprintf('%02d:%02d', floor(abs($utcOffset) / 60), abs($utcOffset) % 60) . ')';

                $timeInfo[] = '<strong>' . FrontendStrings::getCommonStrings()['client_time_colon'] . '</strong> '
                    . $utcString . $clientZoneStart . ($clientZoneEnd ? ' - ' . $clientZoneEnd : '');
            }
        }

        return $timeInfo;
    }

    /**
     * Get package labels.
     *
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public static function getPackageLabels($data)
    {
        $packageInfo = [];

        foreach ($data['package'] as $bookingData) {
            /** @var array $packageBooking */
            $booking = self::getEntity(
                array_merge(['type' => Entities::APPOINTMENT], $bookingData)
            );

            $serviceId = $booking['bookable']['id'];

            $packageInfo[$serviceId]['name'] =
                '<strong>' .
                self::$settingsService->getCategorySettings('labels')['service'] .
                ':</strong> ' .
                $booking['bookable']['name'];

            $packageInfo[$serviceId]['data'][] =
                '<strong>' .
                self::$settingsService->getCategorySettings('labels')['employee'] .
                ':</strong> ' .
                $booking['firstName'] . ' ' . $booking['lastName'];

            $timeInfo = self::getDateInfo(
                [
                    [
                        'start' => $bookingData['bookingStart'],
                        'end'   => $bookingData['bookingEnd'],
                    ]
                ],
                $data['bookings'][0]['utcOffset'],
                $data['type']
            );

            $packageInfo[$serviceId]['data'][] = $timeInfo[0];
        }

        $result = ['<hr>', '<strong>' . FrontendStrings::getAllStrings()['package'] . ':</strong> ' . $data['name']];

        foreach ($packageInfo as $serviceId => $serviceData) {
            $result[] = $serviceData['name'];

            foreach ($serviceData['data'] as $value) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Get appointments labels.
     *
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public static function getAppointmentLabels($data)
    {
        /** @var array $booking */
        $booking = self::getEntity($data);

        $bookableName = !empty($booking['bookable']['name']) ? $booking['bookable']['name'] : $data['name'];

        $providerFullName = !empty($booking['firstName']) && !empty($booking['lastName']) ?
            $booking['firstName'] . ' ' . $booking['lastName'] : '';

        return array_merge(
            ['<hr>'],
            self::getDateInfo(
                $data['dateTimeValues'],
                $data['bookings'][0]['utcOffset'],
                $data['type']
            ),
            [
                '<strong>' . self::$settingsService->getCategorySettings('labels')['service']
                . ':</strong> ' . $bookableName,
                '<strong>' . self::$settingsService->getCategorySettings('labels')['employee']
                . ':</strong> ' . $providerFullName,
                '<strong>' . FrontendStrings::getCommonStrings()['total_number_of_persons'] . '</strong> '
                . $data['bookings'][0]['persons'],
            ]
        );
    }

    /**
     * Get event labels.
     *
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public static function getEventLabels($data)
    {
        /** @var array $booking */
        $booking = self::getEntity($data);

        $bookableName = !empty($booking['bookable']['name']) ? $booking['bookable']['name'] : $data['name'];

        $ticketsData = [];

        if (!empty($data['bookings'][0]['ticketsData'])) {
            foreach ($data['bookings'][0]['ticketsData'] as $item) {
                if ($item['persons']) {
                    $ticketsData[] = $item['persons'] . ' x ' . $item['name'];
                }
            }
        }

        return array_merge(
            ['<hr>'],
            self::getDateInfo(
                $data['dateTimeValues'],
                $data['bookings'][0]['utcOffset'],
                $data['type']
            ),
            [
                '<strong>' . FrontendStrings::getAllStrings()['event']
                . ':</strong> ' . $bookableName,
                !$ticketsData ? '<strong>' . FrontendStrings::getCommonStrings()['total_number_of_persons'] . '</strong> '
                . $data['bookings'][0]['persons'] :
                '<strong>' . BackendStrings::getCommonStrings()['event_tickets'] . ': ' . '</strong> '
                    . implode(', ', $ticketsData),
            ]
        );
    }

    /**
     * Get item data for cart.
     *
     * @param $other_data
     * @param $wc_item
     *
     * @return array
     * @throws \Exception
     * @throws ContainerException
     */
    public static function getItemData($other_data, $wc_item)
    {
        if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
            if (self::getWooCommerceCart()) {
                self::processCart(false);
            }

            /** @var array $booking */
            $booking = self::getEntity($wc_item[self::AMELIA]);

            $bookingType = $wc_item[self::AMELIA]['type'];

            $customFieldsInfo = [];

            foreach ((array)$wc_item[self::AMELIA]['bookings'][0]['customFields'] as $customField) {
                if (!array_key_exists('type', $customField) ||
                    (array_key_exists('type', $customField) && $customField['type'] !== 'file')
                ) {
                    if (isset($customField['value']) && is_array($customField['value'])) {
                        $customFieldsInfo[] = '' . $customField['label'] . ': ' . implode(', ', $customField['value']);
                    } elseif (isset($customField['value'])) {
                        $customFieldsInfo[] = '' . $customField['label'] . ': ' . $customField['value'];
                    }
                }
            }


            $extrasInfo = [];

            foreach ((array)$wc_item[self::AMELIA]['bookings'][0]['extras'] as $index => $extra) {
                if (empty($booking['extras'][$extra['extraId']]['name'])) {
                    $extrasInfo[] = 'Extra' . $index . ' (x' . $extra['quantity'] . ')';
                } else {
                    $extrasInfo[] = $booking['extras'][$extra['extraId']]['name'] . ' (x' . $extra['quantity'] . ')';
                }
            }

            $couponUsed = [];

            if (!empty($wc_item[self::AMELIA]['couponId'])) {
                $couponUsed = [
                    '<strong>' . FrontendStrings::getCommonStrings()['coupon_used'] . '</strong>'
                ];
            }

            $bookableInfo = [];

            $bookableLabel = '';

            switch ($bookingType) {
                case Entities::APPOINTMENT:
                    $bookableInfo = self::getAppointmentLabels($wc_item[self::AMELIA]);

                    $bookableLabel = FrontendStrings::getCommonStrings()['appointment_info'];

                    break;

                case Entities::PACKAGE:
                    $bookableInfo = self::getPackageLabels($wc_item[self::AMELIA]);

                    $bookableLabel = FrontendStrings::getCommonStrings()['package_info'];

                    break;

                case Entities::EVENT:
                    $bookableInfo = self::getEventLabels($wc_item[self::AMELIA]);

                    $bookableLabel = FrontendStrings::getCommonStrings()['event_info'];

                    break;
            }

            $recurringInfo = [];

            $recurringItems = !empty($wc_item[self::AMELIA]['recurring']) ? $wc_item[self::AMELIA]['recurring'] : [];

            foreach ($recurringItems as $index => $recurringReservation) {
                $recurringInfo[] = self::getDateInfo(
                    [
                        [
                            'start' => $recurringReservation['bookingStart'],
                            'end'   => null
                        ]
                    ],
                    $wc_item[self::AMELIA]['bookings'][0]['utcOffset'],
                    $wc_item[self::AMELIA]['type']
                );
            }

            $recurringInfo = $recurringInfo ? array_column($recurringInfo, 1) : null;

            /** @var SettingsService $settingsService */
            $settingsService = self::$container->get('domain.settings.service');

            $wcSettings = $settingsService->getSetting('payments', 'wc');

            $metaData = '';

            /** @var HelperService $helperService */
            $helperService = self::$container->get('application.helper.service');

            $description = !empty($wcSettings['checkoutData'][$bookingType]) ?
                trim($wcSettings['checkoutData'][$bookingType]) : '';

            if (!empty($wcSettings['checkoutData']['translations'][$bookingType])) {
                $description = $helperService->getBookingTranslation(
                    json_encode(['locale' => $wc_item[self::AMELIA]['locale']]),
                    json_encode($wcSettings['checkoutData']['translations']),
                    $bookingType
                ) ?: $description;
            }

            if ($booking && $description) {
                /** @var Appointment|Event $reservation */
                $reservation = null;

                /** @var PlaceholderService $placeholderService */
                $placeholderService = null;

                $reservationData = [];

                switch ($bookingType) {
                    case Entities::APPOINTMENT:
                        $placeholderService = self::$container->get('application.placeholder.appointment.service');

                        $reservation = AppointmentFactory::create($wc_item[self::AMELIA]);

                        $reservationData = $reservation->toArray();

                        $reservationData['recurring'] = [];

                        foreach ($wc_item[self::AMELIA]['recurring'] as $index => $recurringReservation) {
                            $reservationData['recurring'][] = [
                                'type'                => Entities::APPOINTMENT,
                                Entities::APPOINTMENT => array_merge(
                                    $reservationData,
                                    $recurringReservation,
                                    [
                                        'type' => Entities::APPOINTMENT,
                                        'bookings' => [
                                            array_merge(
                                                $wc_item[self::AMELIA]['bookings'][0],
                                                ['price' => 0]
                                            )
                                        ],
                                    ]
                                ),
                            ];
                        }

                        break;
                    case Entities::PACKAGE:
                        $placeholderService = self::$container->get('application.placeholder.package.service');

                        $reservation = PackageFactory::create(
                            array_merge(
                                $wc_item[self::AMELIA],
                                $booking['bookable']
                            )
                        );

                        $reservationData = $reservation->toArray();

                        $reservationData['customer'] = $wc_item[self::AMELIA]['customer'];

                        $reservationData['bookings'] = $wc_item[self::AMELIA]['bookings'];

                        $reservationData['recurring'] = [];

                        $info = json_encode(
                            [
                                'firstName' => $wc_item[self::AMELIA]['customer']['firstName'],
                                'lastName'  => $wc_item[self::AMELIA]['customer']['lastName'],
                                'phone'     => $wc_item[self::AMELIA]['customer']['phone'],
                                'locale'    => $wc_item[self::AMELIA]['locale'],
                                'timeZone'  => $wc_item[self::AMELIA]['timeZone'],
                            ]
                        );

                        foreach ($wc_item[self::AMELIA]['package'] as $index => $packageReservation) {
                            $reservationData['recurring'][] = [
                                'type'                => Entities::APPOINTMENT,
                                Entities::APPOINTMENT => array_merge(
                                    $packageReservation,
                                    [
                                        'type' => Entities::APPOINTMENT,
                                        'bookings' => [
                                            array_merge(
                                                $wc_item[self::AMELIA]['bookings'][0],
                                                [
                                                    'info'         => $info,
                                                    'utcOffset'    => $packageReservation['utcOffset'],
                                                    'price'        => 0,
                                                    'customFields' => $reservationData['bookings'][0]['customFields'] ?
                                                        json_encode($reservationData['bookings'][0]['customFields']) : ''
                                                ]
                                            )
                                        ],
                                    ]
                                ),
                            ];
                        }

                        $reservationData['bookings'][0]['info'] = $info;

                        break;
                    case Entities::EVENT:
                        $placeholderService = self::$container->get('application.placeholder.event.service');

                        $periods = [];

                        foreach ($wc_item[self::AMELIA]['dateTimeValues'] as $period) {
                            $periods[] = [
                                'periodStart' => $period['start'],
                                'periodEnd'   => $period['end'],
                            ];
                        }

                        $reservation = EventFactory::create(
                            array_merge(
                                self::getEntity($wc_item[self::AMELIA])['bookable'],
                                [
                                    'bookings' => [
                                        array_merge(
                                            $wc_item[self::AMELIA]['bookings'][0],
                                            ['status' => 'approved']
                                        )
                                    ],
                                    'periods'  => $periods
                                ]
                            )
                        );

                        $reservationData = $reservation->toArray();

                        break;
                }

                $reservationData['bookings'][0]['customFields'] =
                    $reservationData['bookings'][0]['customFields'] ? json_encode($reservationData['bookings'][0]['customFields']) : '';

                $reservationData['bookings'][0]['isChangedStatus'] = true;

                $reservationData['isForCustomer'] = true;

                $placeholderData = $placeholderService->getPlaceholdersData(
                    $reservationData,
                    0,
                    'email',
                    UserFactory::create($wc_item[self::AMELIA]['bookings'][0]['customer'])
                );

                $placeholderData['customer_firstName'] = $wc_item[self::AMELIA]['bookings'][0]['customer']['firstName'];

                $placeholderData['customer_lastName'] = $wc_item[self::AMELIA]['bookings'][0]['customer']['lastName'];

                $placeholderData['customer_fullName'] = $placeholderData['customer_firstName'] . ' ' . $placeholderData['customer_lastName'];

                $placeholderData['customer_email'] = $wc_item[self::AMELIA]['bookings'][0]['customer']['email'];

                $placeholderData['customer_phone'] = $wc_item[self::AMELIA]['bookings'][0]['customer']['phone'];

                $descriptionParts = strpos($description, '<p>') !== false ? explode('<p', $description) : [];

                foreach ($descriptionParts as $index => $part) {
                    if (($position = strpos($part, '%custom_field_')) !== false) {
                        $value = substr(
                            substr($part, $position + 14),
                            0
                        );

                        $id = substr($value, 0, strpos($value, '%'));

                        if (isset($placeholderData['custom_field_' . $id]) &&
                            !$placeholderData['custom_field_' . $id]
                        ) {
                            $descriptionParts[$index] = ' class="am-cf-empty"' . $descriptionParts[$index];
                        }
                    }
                }

                $description = $descriptionParts ? implode('<p', $descriptionParts) : $description;

                $metaData = $placeholderService->applyPlaceholders(
                    $description,
                    $placeholderData
                );
            }

            $other_data[] = [
                'name'  => $bookableLabel,
                'value' => $metaData ? $metaData : implode(
                    PHP_EOL . PHP_EOL,
                    array_merge(
                        $bookableInfo,
                        $extrasInfo ? array_merge(
                            [
                                '<strong>' . FrontendStrings::getCatalogStrings()['extras'] . ':</strong>'
                            ],
                            $extrasInfo
                        ) : [],
                        $customFieldsInfo ? array_merge(
                            [
                                '<strong>' . FrontendStrings::getCommonStrings()['custom_fields'] . ':</strong>'
                            ],
                            $customFieldsInfo
                        ) : [],
                        $couponUsed,
                        $recurringInfo ? array_merge(
                            [
                                '<strong>' . FrontendStrings::getBookingStrings()['recurring_appointments'] . ':</strong>'
                            ],
                            $recurringInfo
                        ) : []
                    )
                )
            ];
        }

        return $other_data;
    }

    /**
     * Get cart item price.
     *
     * @param $product_price
     * @param $wc_item
     * @param $cart_item_key
     *
     * @return mixed
     */
    public static function cartItemPrice($product_price, $wc_item, $cart_item_key)
    {
        if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
            $product_price = wc_price(self::getReservationPaymentAmount($wc_item[self::AMELIA]));
        }

        return $product_price >= 0 ? $product_price : 0;
    }

    /**
     * Assign checkout value from appointment.
     *
     * @param $null
     * @param $field_name
     *
     * @return string|null
     */
    public static function checkoutGetValue($null, $field_name)
    {
        $wooCommerceCart = self::getWooCommerceCart();

        if (!$wooCommerceCart) {
            return null;
        }

        /** @var SettingsService $settingsService */
        $settingsService = self::$container->get('domain.settings.service');

        if (!$settingsService->getSetting('payments', 'wc')['skipCheckoutGetValueProcessing']) {
            self::processCart(false);
        }

        if (empty(self::$checkout_info)) {
            if (!WC()->is_rest_api_request()) {
                foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
                    if (array_key_exists(self::AMELIA, $wc_item) && is_array($wc_item[self::AMELIA])) {
                        self::$checkout_info = apply_filters(
                            'amelia_checkout_data',
                            [
                                'billing_first_name' => $wc_item[self::AMELIA]['bookings'][0]['customer']['firstName'],
                                'billing_last_name'  => $wc_item[self::AMELIA]['bookings'][0]['customer']['lastName'],
                                'billing_email'      => $wc_item[self::AMELIA]['bookings'][0]['customer']['email'],
                                'billing_phone'      => $wc_item[self::AMELIA]['bookings'][0]['customer']['phone'],
                            ],
                            self::$container,
                            $wc_key
                        );

                        break;
                    }
                }
            }
        }

        if (array_key_exists($field_name, self::$checkout_info)) {
            return self::$checkout_info[$field_name];
        }

        return null;
    }

    /**
     * Add order item meta.
     *
     * @param $item_id
     * @param $values
     * @param $wc_key
     * @throws ContainerException
     */
    public static function addOrderItemMeta($item_id, $values, $wc_key)
    {
        if (isset($values[self::AMELIA]) && is_array($values[self::AMELIA])) {
            wc_update_order_item_meta(
                $item_id,
                self::AMELIA,
                array_merge(
                    $values[self::AMELIA],
                    [
                        'labels' => self::getLabels($values[self::AMELIA])
                    ]
                )
            );
        }
    }

    /**
     * Checkout Create Order Line Item.
     *
     * @param $item
     * @param $cart_item_key
     * @param $values
     * @param $order
     * @throws ContainerException
     */
    public static function checkoutCreateOrderLineItem($item, $cart_item_key, $values, $order)
    {
        if (isset($values[self::AMELIA]) && is_array($values[self::AMELIA])) {
            $item->update_meta_data(
                self::AMELIA,
                array_merge(
                    $values[self::AMELIA],
                    [
                        'labels' => self::getLabels($values[self::AMELIA])
                    ]
                )
            );
        }
    }

    /**
     * Update Order Item Meta data.
     *
     * @param int   $orderId
     * @param array $reservation
     * @throws ContainerException
     */
    public static function updateItemMetaData($orderId, $reservation)
    {
        $order = wc_get_order($orderId);

        if ($order) {
            foreach ($order->get_items() as $itemId => $orderItem) {
                $data = wc_get_order_item_meta($itemId, 'ameliabooking');

                if ($data && is_array($data)) {
                    wc_update_order_item_meta(
                        $itemId,
                        self::AMELIA,
                        array_merge(
                            $data,
                            [
                                'labels' => WooCommerceService::getLabels($reservation)
                            ]
                        )
                    );
                }
            }
        }
    }

    /**
     * Print appointment details inside order items in the backend.
     *
     * @param int $item_id
     * @throws ContainerException
     */
    public static function orderItemMeta($item_id)
    {
        $data = wc_get_order_item_meta($item_id, self::AMELIA);

        if (!empty($data['labels'])) {
            echo $data['labels'];
        } else {
            echo self::getLabels($data);
        }
    }

    /**
     * Get labels to print
     *
     * @param array $data
     * @return string
     * @throws ContainerException
     */
    public static function getLabels($data)
    {
        if ($data && is_array($data)) {
            $other_data = self::getItemData([], [self::AMELIA => $data]);

            $labels = strpos($other_data[0]['value'], '<br>') !== false ? preg_replace("/\r|\n/", '<br>', $other_data[0]['value']) : $other_data[0]['value'];

            $labels = str_replace('<p><br></p>', '<br>', $labels);

            return '<br/>' . $other_data[0]['name'] . '<br/>' . nl2br($labels);
        }
    }

    /**
     * Before checkout process
     *
     * @param $array
     *
     * @throws \Exception
     */
    public static function beforeCheckoutProcess($array)
    {
        $wooCommerceCart = self::getWooCommerceCart();

        if (!$wooCommerceCart) {
            return;
        }

        foreach ($wooCommerceCart->get_cart() as $wc_key => $wc_item) {
            if (isset($wc_item[self::AMELIA]) && is_array($wc_item[self::AMELIA])) {
                if ($errorMessage = self::validateBooking($wc_item[self::AMELIA])) {
                    $cartUrl = self::getPageUrl(!empty($wc_item[self::AMELIA]['locale']) ? $wc_item[self::AMELIA]['locale'] : '');
                    $removeAppointmentMessage = FrontendStrings::getCommonStrings()['wc_appointment_is_removed'];

                    throw new \Exception($errorMessage . "<a href='{$cartUrl}'>{$removeAppointmentMessage}</a>");
                }
            }
        }
    }

    /**
     * Manage bookings after checkout.
     *
     * @param $order_id
     */
    public static function orderStatusChanged($order_id)
    {
        $order = new \WC_Order($order_id);

        foreach ($order->get_items() as $item_id => $order_item) {
            $data = wc_get_order_item_meta($item_id, self::AMELIA);

            $isValid = $data && is_array($data) && !self::$isProcessing;

            $wcSettings = self::$settingsService->getSetting('payments', 'wc');

            if ($data && is_array($data) && isset($data['type'], $wcSettings['rules'][$data['type']])) {
                /** @var ReservationServiceInterface $reservationService */
                $reservationService = self::$container->get('application.reservation.service')->get($data['type']);

                $isValid = $reservationService->getWcStatus(
                    $data['type'],
                    $order->get_status(),
                    'booking',
                    isset($data['processed'])
                );
            }

            try {
                if ($isValid !== false && !isset($data['processed'])) {
                    self::$isProcessing = true;

                    $data['processed'] = true;

                    $data['taxIncluded'] = wc_prices_include_tax();

                    wc_update_order_item_meta($item_id, self::AMELIA, $data);

                    $data['payment']['wcOrderId'] = $order_id;

                    $data['payment']['wcOrderItemId'] = $order_id;

                    $data['payment']['orderStatus'] = $order->get_status();

                    $data['payment']['gatewayTitle'] = $order->get_payment_method_title();

                    $data['payment']['amount'] = 0;

                    $data['payment']['status'] = $order->get_payment_method() === 'cod' ?
                        PaymentStatus::PENDING : PaymentStatus::PAID;

                    /** @var SettingsService $settingsService */
                    $settingsService = self::$container->get('domain.settings.service');

                    $orderUserId = $order->get_user_id();

                    if ($orderUserId && $settingsService->getSetting('roles', 'automaticallyCreateCustomer')) {
                        $data['bookings'][0]['customer']['externalId'] = $order->get_user_id();
                    }

                    $customFields = $data['bookings'][0]['customFields'];

                    $data['bookings'][0]['customFields'] = $customFields ? json_encode($customFields) : null;

                    $booking = self::saveBooking($data, $order_item);

                    $data['bookings'][0]['customFields'] = $customFields;

                    // add created user to WooCommerce order if WooCommerce didn't created user but Amelia Customer has WordPress user
                    if (!$orderUserId &&
                        $booking !== null &&
                        $settingsService->getSetting('roles', 'automaticallyCreateCustomer') &&
                        !empty($booking['customer']['externalId'])
                    ) {
                        update_post_meta(
                            $order_id,
                            '_customer_user',
                            $booking['customer']['externalId']
                        );
                    }

                    wc_update_order_item_meta($item_id, self::AMELIA, $data);
                } elseif ($isValid !== false && isset($data['processed'], $data['payment']['wcOrderId'])) {
                    /** @var ReservationServiceInterface $reservationService */
                    $reservationService = self::$container->get('application.reservation.service')->get($data['type']);

                    /** @var PaymentRepository $paymentRepository */
                    $paymentRepository = self::$container->get('domain.payment.repository');

                    /** @var Collection $payments */
                    $payments = $paymentRepository->getByEntityId($order_id, 'wcOrderId');

                    /** @var Payment $payment */
                    foreach ($payments->getItems() as $payment) {
                        $paymentStatus =  $reservationService->getWcStatus(
                            $data['type'],
                            $order->get_status(),
                            'payment',
                            true
                        ) ?: PaymentStatus::PAID;

                        if ((!empty($data['bookings'][0]['deposit']) || !empty($data['deposit'])) &&
                            $paymentStatus === 'paid'
                        ) {
                            $paymentStatus = PaymentStatus::PARTIALLY_PAID;
                        }

                        $paymentRepository->updateFieldById(
                            $payment->getId()->getValue(),
                            $paymentStatus,
                            'status'
                        );

                        $requestedStatus = $reservationService->getWcStatus(
                            $data['type'],
                            $order->get_status(),
                            'booking',
                            true
                        );

                        switch ($data['type']) {
                            case (Entities::APPOINTMENT):
                                self::bookingAppointmentUpdated($payment, $requestedStatus);
                                break;

                            case (Entities::EVENT):
                                self::bookingEventUpdated($payment, $requestedStatus);
                                break;

                            case (Entities::PACKAGE):
                                self::bookingPackageUpdated($payment, $requestedStatus);
                                break;
                        }
                    }
                }
            } catch (ContainerException $e) {
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param Payment $payment
     * @param string  $requestedStatus
     *
     * @throws BookingCancellationException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public static function bookingAppointmentUpdated($payment, $requestedStatus)
    {
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = self::$container->get('application.reservation.service')->get(Entities::APPOINTMENT);

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = self::$container->get('domain.booking.customerBooking.repository');

        /** @var CustomerBooking $booking */
        $booking = $bookingRepository->getById($payment->getCustomerBookingId()->getValue());

        $bookingData = $reservationService->updateStatus($booking, $requestedStatus);

        $result = new CommandResult();

        $result->setData(
            [
                Entities::APPOINTMENT          => $bookingData[Entities::APPOINTMENT],
                'appointmentStatusChanged'     => $bookingData['appointmentStatusChanged'],
                'appointmentRescheduled'       => false,
                'bookingsWithChangedStatus'    => [$bookingData[Entities::BOOKING]],
                'appointmentEmployeeChanged'   => false,
                'appointmentZoomUserChanged'   => false,
                'appointmentZoomUsersLicenced' => false,
            ]
        );

        AppointmentEditedEventHandler::handle($result, self::$container);
    }

    /**
     * @param Payment $payment
     * @param string  $requestedStatus
     *
     * @throws BookingCancellationException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public static function bookingEventUpdated($payment, $requestedStatus)
    {
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = self::$container->get('application.reservation.service')->get(Entities::EVENT);

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = self::$container->get('domain.booking.customerBooking.repository');

        /** @var CustomerBooking $booking */
        $booking = $bookingRepository->getById($payment->getCustomerBookingId()->getValue());

        $bookingData = $reservationService->updateStatus($booking, $requestedStatus);

        $result = new CommandResult();

        $result->setData(
            [
                'type'                 => Entities::EVENT,
                Entities::EVENT        => $bookingData[Entities::EVENT],
                Entities::BOOKING      => $bookingData[Entities::BOOKING],
                'bookingStatusChanged' => true,
            ]
        );

        BookingEditedEventHandler::handle($result, self::$container);
    }

    /**
     * @param Payment $payment
     * @param string  $requestedStatus
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public static function bookingPackageUpdated($payment, $requestedStatus)
    {
        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = self::$container->get('domain.bookable.packageCustomer.repository');

        $packageCustomerRepository->updateFieldById(
            $payment->getPackageCustomerId()->getValue(),
            $requestedStatus,
            'status'
        );

        $result = new CommandResult();

        $result->setData(
            [
                'packageCustomerId' => $payment->getPackageCustomerId()->getValue(),
                'status'            => $requestedStatus,
            ]
        );

        PackageCustomerUpdatedEventHandler::handle($result, self::$container);
    }

    /**
     * @param $orderId
     */
    public static function redirectAfterOrderReceived($orderId)
    {
        $order = new \WC_Order($orderId);

        if (!$order->has_status('failed')) {
            foreach ($order->get_items() as $itemId => $orderItem) {
                $data = wc_get_order_item_meta($itemId, self::AMELIA);

                $wcSettings = self::$settingsService->getSetting('payments', 'wc');

                if ($data && is_array($data) &&
                    isset($data['processed'], $wcSettings['redirectPage']) &&
                    $wcSettings['redirectPage'] === 2
                ) {
                    $token = new Token();

                    $identifier = $orderId . '_' . $token->getValue() . '_' . $data['type'];

                    wp_safe_redirect($data['returnUrl'] . '?ameliaWcCache=' . $identifier);

                    exit;
                }
            }
        }
    }

    /**
     * @param $orderId
     *
     * @return array|null
     * @throws ContainerException
     */
    public static function getCacheData($orderId)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = self::$container->get('domain.payment.repository');

        $order = null;

        try {
            $order = new \WC_Order($orderId);
        } catch (\Exception $e) {
        }

        if ($order && !$order->has_status('failed')) {
            foreach ($order->get_items() as $itemId => $orderItem) {
                $data = wc_get_order_item_meta($itemId, self::AMELIA);

                if ($data && is_array($data) && isset($data['processed'])) {
                    try {
                        /** @var Collection $payments */
                        $payments = $paymentRepository->getByEntityId($orderId, 'wcOrderId');

                        /** @var Payment $payment */
                        $payment = $payments->length() ? $payments->getItem(0) : null;

                        if ($payment) {
                            /** @var ReservationServiceInterface $reservationService */
                            $reservationService = self::$container->get('application.reservation.service')->get(
                                $payment->getEntity()->getValue()
                            );

                            $reservationData = $reservationService->getReservationByPayment($payment)->getData();

                            if ($payment->getEntity()->getValue() === Entities::PACKAGE) {
                                $reservationData = array_merge(
                                    $reservationData,
                                    [
                                        'type'    => Entities::PACKAGE,
                                        'package' => array_merge(
                                            $reservationData[Entities::APPOINTMENT] && $reservationData[Entities::BOOKING] ? [
                                                [
                                                    'type'                     => Entities::APPOINTMENT,
                                                    Entities::APPOINTMENT      => $reservationData[Entities::APPOINTMENT],
                                                    Entities::BOOKING          => $reservationData[Entities::BOOKING],
                                                    'appointmentStatusChanged' => $reservationData['appointmentStatusChanged'],
                                                    'utcTime'                  => $reservationData['utcTime']
                                                ]
                                            ] : [],
                                            $reservationData['recurring']
                                        )
                                    ]
                                );

                                $reservationData['appointmentStatusChanged'] = false;

                                $reservationData['recurring'] = [];

                                unset($reservationData[Entities::APPOINTMENT]);
                                unset($reservationData[Entities::BOOKING]);
                            }

                            $cacheData = json_decode($data['cacheData'], true);

                            if (!empty($cacheData['request']['state']['appointment']['bookings'][0]['customer']) &&
                                !empty($data['bookings'][0]['customer'])
                            ) {
                                $cacheData['request']['state']['appointment']['bookings'][0]['customer'] =
                                    $data['bookings'][0]['customer'];
                            }

                            return array_merge(
                                $cacheData,
                                [
                                    'response' => $reservationData,
                                    'status'   => 'paid'
                                ]
                            );
                        }
                    } catch (InvalidArgumentException $e) {
                    } catch (QueryExecutionException $e) {
                    }
                }
            }
        }

        return null;
    }
}
