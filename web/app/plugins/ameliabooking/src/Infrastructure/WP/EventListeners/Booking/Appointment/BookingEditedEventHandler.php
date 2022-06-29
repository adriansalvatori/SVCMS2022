<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\WebHook\WebHookApplicationService;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use AmeliaPHPMailer\PHPMailer\Exception;

/**
 * Class BookingEditedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class BookingEditedEventHandler
{
    /** @var string */
    const BOOKING_STATUS_UPDATED = 'bookingStatusUpdated';

    /** @var string */
    const BOOKING_CANCELED = 'bookingCanceled';

    /** @var string */
    const BOOKING_ADDED = 'bookingAdded';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public static function handle($commandResult, $container)
    {
        /** @var GoogleCalendarService $googleCalendarService */
        $googleCalendarService = $container->get('infrastructure.google.calendar.service');
        /** @var OutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $container->get('infrastructure.outlook.calendar.service');
        /** @var EmailNotificationService $emailNotificationService */
        $emailNotificationService = $container->get('application.emailNotification.service');
        /** @var SMSNotificationService $smsNotificationService */
        $smsNotificationService = $container->get('application.smsNotification.service');
        /** @var SettingsService $settingsService */
        $settingsService = $container->get('domain.settings.service');
        /** @var WebHookApplicationService $webHookService */
        $webHookService = $container->get('application.webHook.service');

        $appointment = $commandResult->getData()[$commandResult->getData()['type']];
        $booking     = $commandResult->getData()[Entities::BOOKING];
        $bookingStatusChanged = $commandResult->getData()['bookingStatusChanged'];

        if ($bookingStatusChanged) {
            /** @var EventRepository $eventRepository */
            $eventRepository   = $container->get('domain.booking.event.repository');
            $reservationObject = $eventRepository->getById($appointment['id']);


            if ($googleCalendarService) {
                if ($booking['status'] === BookingStatus::APPROVED) {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::BOOKING_ADDED, $reservationObject->getPeriods());
                } else if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED) {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::BOOKING_CANCELED, $reservationObject->getPeriods());
                }
            }

            if ($outlookCalendarService) {
                if ($booking['status'] === BookingStatus::APPROVED) {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::BOOKING_ADDED, $reservationObject->getPeriods());
                } else if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED) {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::BOOKING_CANCELED, $reservationObject->getPeriods());
                }
            }
            $emailNotificationService->sendCustomerBookingNotification($appointment, $booking);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendCustomerBookingNotification($appointment, $booking);
            }

            $webHookService->process(self::BOOKING_STATUS_UPDATED, $appointment, [$booking]);
        }
    }
}
