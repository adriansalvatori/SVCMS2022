<?php
/**
 * Assembling domain services:
 * Instantiating domain services and injecting the Infrastructure layer implementations
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Permissions service
 *
 * @param $c
 *
 * @return \AmeliaBooking\Domain\Services\Permissions\PermissionsService
 */
$entries['domain.permissions.service'] = function ($c) {
    return new AmeliaBooking\Domain\Services\Permissions\PermissionsService(
        $c,
        new AmeliaBooking\Infrastructure\WP\PermissionsService\PermissionsChecker()
    );
};

/**
 * Appointment service
 *
 * @return \AmeliaBooking\Domain\Services\Booking\AppointmentDomainService
 */
$entries['domain.booking.appointment.service'] = function () {
    return new AmeliaBooking\Domain\Services\Booking\AppointmentDomainService();
};


/**
 * Event service
 *
 * @return \AmeliaBooking\Domain\Services\Booking\EventDomainService
 */
$entries['domain.booking.event.service'] = function () {
    return new AmeliaBooking\Domain\Services\Booking\EventDomainService();
};

/**
 * Settings service
 *
 * @return \AmeliaBooking\Domain\Services\Settings\SettingsService
 */
$entries['domain.settings.service'] = function () {
    return new AmeliaBooking\Domain\Services\Settings\SettingsService(
        new AmeliaBooking\Infrastructure\WP\SettingsService\SettingsStorage()
    );
};

/**
 * @return \AmeliaBooking\Domain\Services\TimeSlot\TimeSlotService
 */
$entries['domain.timeSlot.service'] = function () {
    return new AmeliaBooking\Domain\Services\TimeSlot\TimeSlotService();
};
