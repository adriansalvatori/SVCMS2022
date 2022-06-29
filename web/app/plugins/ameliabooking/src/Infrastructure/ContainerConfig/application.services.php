<?php
/**
 * Assembling application services:
 * Instantiating application services and injecting the Infrastructure layer implementations
 */

use AmeliaBooking\Application\Services\Bookable\AbstractPackageApplicationService;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Application\Services\Cache\CacheApplicationService;
use AmeliaBooking\Application\Services\Coupon\CouponApplicationService;
use AmeliaBooking\Application\Services\Gallery\GalleryApplicationService;
use AmeliaBooking\Application\Services\Location\LocationApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\Reservation\ReservationService;
use AmeliaBooking\Application\Services\TimeSlot\TimeSlotService;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Application\Services\User\ProviderApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Entity\Booking\Reservation;
use AmeliaBooking\Infrastructure\Common\Container;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Customer service
 *
 * @param Container $c
 *
 * @return UserApplicationService
 */
$entries['application.user.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\User\UserApplicationService($c);
};

/**
 * Provider service
 *
 * @param Container $c
 *
 * @return ProviderApplicationService
 */
$entries['application.user.provider.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\User\ProviderApplicationService($c);
};

/**
 * Customer service
 *
 * @param Container $c
 *
 * @return CustomerApplicationService
 */
$entries['application.user.customer.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\User\CustomerApplicationService($c);
};

/**
 * Location service
 *
 * @return \AmeliaBooking\Application\Services\Location\CurrentLocation
 */
$entries['application.currentLocation.service'] = function () {
    return new AmeliaBooking\Application\Services\Location\CurrentLocation();
};

/**
 * Appointment service
 *
 * @param Container $c
 *
 * @return AppointmentApplicationService
 */
$entries['application.booking.appointment.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Booking\AppointmentApplicationService($c);
};

/**
 * Event service
 *
 * @param Container $c
 *
 * @return EventApplicationService
 */
$entries['application.booking.event.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Booking\EventApplicationService($c);
};

/**
 * Reservation service
 *
 * @param Container $c
 *
 * @return ReservationService
 */
$entries['application.reservation.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Reservation\ReservationService($c);
};

/**
 * Reservation
 *
 * @param bool $validate
 *
 * @return Reservation
 */
$entries['application.reservation'] = function ($validate) {
    return new AmeliaBooking\Domain\Entity\Booking\Reservation($validate);
};

/**
 * Appointment Reservation service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Application\Services\Reservation\AppointmentReservationService
 */
$entries['application.reservation.appointment.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Reservation\AppointmentReservationService($c);
};

/**
 * Package Reservation service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Application\Services\Reservation\PackageReservationService
 */
$entries['application.reservation.package.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Reservation\PackageReservationService($c);
};

/**
 * Event Reservation service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Application\Services\Reservation\EventReservationService
 */
$entries['application.reservation.event.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Reservation\EventReservationService($c);
};

/**
 * Booking service
 *
 * @param Container $c
 *
 * @return BookingApplicationService
 */
$entries['application.booking.booking.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Booking\BookingApplicationService($c);
};

/**
 * Bookable service
 *
 * @param Container $c
 *
 * @return BookableApplicationService
 */
$entries['application.bookable.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Bookable\BookableApplicationService($c);
};

/**
 * Bookable package
 *
 * @param Container $c
 *
 * @return AbstractPackageApplicationService
 */
$entries['application.bookable.package'] = function ($c) {
    return new AmeliaBooking\Application\Services\Bookable\PackageApplicationService($c);
};

/**
 * Gallery service
 *
 * @param Container $c
 *
 * @return GalleryApplicationService
 */
$entries['application.gallery.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Gallery\GalleryApplicationService($c);
};

/**
 * Calendar service
 *
 * @param Container $c
 *
 * @return TimeSlotService
 */
$entries['application.timeSlot.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\TimeSlot\TimeSlotService($c);
};

/**
 * Cache service
 *
 * @param Container $c
 *
 * @return CacheApplicationService
 */
$entries['application.cache.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Cache\CacheApplicationService($c);
};

/**
 * Coupon service
 *
 * @param Container $c
 *
 * @return CouponApplicationService
 */
$entries['application.coupon.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Coupon\CouponApplicationService($c);
};

/**
 * Location service
 *
 * @param Container $c
 *
 * @return LocationApplicationService
 */
$entries['application.location.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Location\LocationApplicationService($c);
};

/**
 * Email Notification Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Notification\EmailNotificationService
 */
$entries['application.emailNotification.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Notification\EmailNotificationService($c, 'email');
};

/**
 * Notification Helper Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Notification\NotificationHelperService
 */
$entries['application.notificationHelper.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Notification\NotificationHelperService($c);
};

/**
 * Email Notification Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Notification\SMSNotificationService
 */
$entries['application.smsNotification.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Notification\SMSNotificationService($c, 'sms');
};

/**
 * Appointment Notification Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Placeholder\AppointmentPlaceholderService
 */
$entries['application.placeholder.appointment.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Placeholder\AppointmentPlaceholderService($c);
};

/**
 * Package Notification Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Placeholder\PackagePlaceholderService
 */
$entries['application.placeholder.package.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Placeholder\PackagePlaceholderService($c);
};

/**
 * Event Notification Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Placeholder\EventPlaceholderService
 */
$entries['application.placeholder.event.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Placeholder\EventPlaceholderService($c);
};

/**
 * Stats Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Stats\StatsService
 */
$entries['application.stats.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Stats\StatsService($c);
};

/**
 * Helper Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Helper\HelperService
 */
$entries['application.helper.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Helper\HelperService($c);
};

/**
 * Settings Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Settings\SettingsService
 */
$entries['application.settings.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Settings\SettingsService($c);
};

/**
 * SMS API Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Notification\SMSAPIService
 */
$entries['application.smsApi.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Notification\SMSAPIService($c);
};

/**
 * Payment service
 *
 * @param Container $c
 *
 * @return PaymentApplicationService
 */
$entries['application.payment.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Payment\PaymentApplicationService($c);
};

/**
 * Upload File Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\CustomField\CustomFieldApplicationService
 */
$entries['application.customField.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\CustomField\CustomFieldApplicationService($c);
};

/**
 * Web Hook Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Application\Services\WebHook\WebHookApplicationService
 */
$entries['application.webHook.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\WebHook\WebHookApplicationService($c);
};

/**
 * Zoom Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Zoom\ZoomApplicationService
 */
$entries['application.zoom.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Zoom\ZoomApplicationService($c);
};

/**
 * ICS File Service
 *
 * @param Container $c
 *
 * @return \AmeliaBooking\Application\Services\Booking\IcsApplicationService
 */
$entries['application.ics.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Booking\IcsApplicationService($c);
};

/**
 * Stash Service
 *
 * @param Container $c
 *
 * @return AmeliaBooking\Application\Services\Stash\StashApplicationService
 */
$entries['application.stash.service'] = function ($c) {
    return new AmeliaBooking\Application\Services\Stash\StashApplicationService($c);
};
