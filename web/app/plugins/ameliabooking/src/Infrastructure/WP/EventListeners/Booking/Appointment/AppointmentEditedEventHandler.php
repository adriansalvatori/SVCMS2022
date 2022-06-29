<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\IcsApplicationService;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\WebHook\WebHookApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Application\Services\Zoom\ZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentEditedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentEditedEventHandler
{
    /** @var string */
    const APPOINTMENT_EDITED = 'appointmentEdited';
    /** @var string */
    const APPOINTMENT_ADDED = 'appointmentAdded';
    /** @var string */
    const APPOINTMENT_DELETED = 'appointmentDeleted';
    /** @var string */
    const APPOINTMENT_STATUS_AND_TIME_UPDATED = 'appointmentStatusAndTimeUpdated';
    /** @var string */
    const TIME_UPDATED = 'bookingTimeUpdated';
    /** @var string */
    const BOOKING_STATUS_UPDATED = 'bookingStatusUpdated';
    /** @var string */
    const ZOOM_USER_CHANGED = 'zoomUserChanged';
    /** @var string */
    const ZOOM_LICENCED_USER_CHANGED = 'zoomLicencedUserChanged';
    /** @var string */
    const APPOINTMENT_STATUS_AND_ZOOM_LICENCED_USER_CHANGED = 'appointmentStatusAndZoomLicencedUserChanged';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws Exception
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
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $container->get('application.booking.booking.service');
        /** @var ZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var LessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');

        $appointment = $commandResult->getData()[Entities::APPOINTMENT];

        $bookings = $commandResult->getData()['bookingsWithChangedStatus'];

        $appointmentStatusChanged = $commandResult->getData()['appointmentStatusChanged'];

        $appointmentRescheduled = $commandResult->getData()['appointmentRescheduled'];

        $appointmentEmployeeChanged = $commandResult->getData()['appointmentEmployeeChanged'];

        $appointmentZoomUserChanged = $commandResult->getData()['appointmentZoomUserChanged'];

        $appointmentZoomUsersLicenced = $commandResult->getData()['appointmentZoomUsersLicenced'];

        /** @var Appointment $reservationObject */
        $reservationObject = AppointmentFactory::create($appointment);

        $bookingApplicationService->setReservationEntities($reservationObject);

        /** @var CustomerBooking $bookingObject */
        foreach ($reservationObject->getBookings()->getItems() as $bookingObject) {
            foreach ($appointment['bookings'] as $index => $bookingArray) {
                if ($bookingArray['id'] === $bookingObject->getId()->getValue()) {
                    $appointment['bookings'][$index]['customer'] = $bookingObject->getCustomer()->toArray();
                }
            }
        }

        if ($zoomService) {
            $commandSlug = self::APPOINTMENT_EDITED;

            if ($appointmentEmployeeChanged && $appointmentZoomUserChanged && $appointmentZoomUsersLicenced && $appointmentStatusChanged) {
                $commandSlug = self::APPOINTMENT_STATUS_AND_ZOOM_LICENCED_USER_CHANGED;
            } elseif ($appointmentEmployeeChanged && $appointmentZoomUserChanged && $appointmentZoomUsersLicenced) {
                $commandSlug = self::ZOOM_LICENCED_USER_CHANGED;
            } elseif ($appointmentEmployeeChanged && $appointmentZoomUserChanged) {
                $commandSlug = self::ZOOM_USER_CHANGED;
            } elseif ($appointmentStatusChanged && $appointmentRescheduled) {
                $commandSlug = self::APPOINTMENT_STATUS_AND_TIME_UPDATED;
            } elseif ($appointmentStatusChanged) {
                $commandSlug = self::BOOKING_STATUS_UPDATED;
            } elseif ($appointmentRescheduled) {
                $commandSlug = self::TIME_UPDATED;
            }

            if ($commandSlug || !$reservationObject->getZoomMeeting()) {
                $zoomService->handleAppointmentMeeting($reservationObject, $commandSlug);
            }

            if ($reservationObject->getZoomMeeting()) {
                $appointment['zoomMeeting'] = $reservationObject->getZoomMeeting()->toArray();
            }
        }

        if ($lessonSpaceService && $reservationObject->getProvider()) {
            $lessonSpaceService->handle($reservationObject, Entities::APPOINTMENT);
            if ($reservationObject->getLessonSpace()) {
                $appointment['lessonSpace'] = $reservationObject->getLessonSpace();
            }
        }

        if (!$appointmentEmployeeChanged && $reservationObject->getProvider()) {
            try {
                $googleCalendarService->handleEvent($reservationObject, self::APPOINTMENT_EDITED);
            } catch (Exception $e) {
            }
        } else {
            $newProviderId = $reservationObject->getProviderId()->getValue();

            $reservationObject->setProviderId(new Id($appointmentEmployeeChanged));

            try {
                $googleCalendarService->handleEvent($reservationObject, self::APPOINTMENT_DELETED);
            } catch (Exception $e) {
            }

            $reservationObject->setGoogleCalendarEventId(null);

            $reservationObject->setProviderId(new Id($newProviderId));

            try {
                $googleCalendarService->handleEvent($reservationObject, self::APPOINTMENT_ADDED);
            } catch (Exception $e) {
            }
        }

        if ($reservationObject->getGoogleCalendarEventId() !== null) {
            $appointment['googleCalendarEventId'] = $reservationObject->getGoogleCalendarEventId()->getValue();
        }
        if ($reservationObject->getGoogleMeetUrl() !== null) {
            $appointment['googleMeetUrl'] = $reservationObject->getGoogleMeetUrl();
        }

        if (!$appointmentEmployeeChanged && $reservationObject->getProvider()) {
            try {
                $outlookCalendarService->handleEvent($reservationObject, self::APPOINTMENT_EDITED);
            } catch (Exception $e) {
            }
        } else {
            $newProviderId = $reservationObject->getProviderId()->getValue();

            $reservationObject->setProviderId(new Id($appointmentEmployeeChanged));

            try {
                $outlookCalendarService->handleEvent($reservationObject, self::APPOINTMENT_DELETED);
            } catch (Exception $e) {
            }

            $reservationObject->setOutlookCalendarEventId(null);

            $reservationObject->setProviderId(new Id($newProviderId));

            try {
                $outlookCalendarService->handleEvent($reservationObject, self::APPOINTMENT_ADDED);
            } catch (Exception $e) {
            }
        }

        if ($reservationObject->getOutlookCalendarEventId() !== null) {
            $appointment['outlookCalendarEventId'] = $reservationObject->getOutlookCalendarEventId()->getValue();
        }

        if ($appointmentStatusChanged === true) {
            $emailNotificationService->sendAppointmentStatusNotifications($appointment, true, true);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentStatusNotifications($appointment, true, true);
            }
        }

        /** @var IcsApplicationService $icsService */
        $icsService = $container->get('application.ics.service');

        if ($appointmentRescheduled === true) {
            foreach ($appointment['bookings'] as $index => $booking) {
                if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
                    $appointment['bookings'][$index]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $booking['id'],
                        [],
                        true
                    );
                }
            }
            $emailNotificationService->sendAppointmentRescheduleNotifications($appointment);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentRescheduleNotifications($appointment);
            }
        }

        // check bookings with changed status for ICS files
        if ($bookings) {
            foreach ($bookings as $index => $booking) {
                if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
                    $bookings[$index]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $booking['id'],
                        [],
                        true
                    );
                }
            }
        }

        $appointment['employee_changed'] = $appointmentEmployeeChanged !== null;

        $emailNotificationService->sendAppointmentEditedNotifications(
            $appointment,
            $bookings,
            $appointmentStatusChanged
        );

        if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
            $smsNotificationService
                ->sendAppointmentEditedNotifications($appointment, $bookings, $appointmentStatusChanged);
        }

        if ($appointmentRescheduled === true) {
            $webHookService->process(self::TIME_UPDATED, $appointment, []);
        }

        if ($bookings) {
            $canceledBookings = [];
            $otherBookings    = [];
            foreach ($bookings as $booking) {
                if ($booking['status'] === BookingStatus::CANCELED) {
                    $canceledBookings[] = $booking;
                } else {
                    $otherBookings[] = $booking;
                }
            }

            if (count($canceledBookings) > 0) {
                $webHookService->process(BookingCanceledEventHandler::BOOKING_CANCELED, $appointment, $canceledBookings);
            }
            if (count($otherBookings) > 0) {
                $webHookService->process(self::BOOKING_STATUS_UPDATED, $appointment, $otherBookings);
            }
        }
    }
}
