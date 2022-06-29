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
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Application\Services\Zoom\ZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use Interop\Container\Exception\ContainerException;

/**
 * Class AppointmentAddedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentAddedEventHandler
{
    /** @var string */
    const APPOINTMENT_ADDED = 'appointmentAdded';

    /** @var string */
    const BOOKING_ADDED = 'bookingAdded';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
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

        $recurringData = $commandResult->getData()['recurring'];

        $appointment = $commandResult->getData()[Entities::APPOINTMENT];

        $appointmentObject = AppointmentFactory::create($appointment);

        $bookingApplicationService->setReservationEntities($appointmentObject);

        $pastAppointment =  $appointmentObject->getBookingStart()->getValue() < DateTimeService::getNowDateTimeObject();

        if ($zoomService && !$pastAppointment) {
            $zoomService->handleAppointmentMeeting($appointmentObject, self::APPOINTMENT_ADDED);

            if ($appointmentObject->getZoomMeeting()) {
                $appointment['zoomMeeting'] = $appointmentObject->getZoomMeeting()->toArray();
            }
        }

        if ($lessonSpaceService && !$pastAppointment) {
            $lessonSpaceService->handle($appointmentObject, Entities::APPOINTMENT);
            if ($appointmentObject->getLessonSpace()) {
                $appointment['lessonSpace'] = $appointmentObject->getLessonSpace();
            }
        }

        if ($googleCalendarService) {
            try {
                $googleCalendarService->handleEvent($appointmentObject, self::APPOINTMENT_ADDED);
            } catch (\Exception $e) {
            }

            if ($appointmentObject->getGoogleCalendarEventId() !== null) {
                $appointment['googleCalendarEventId'] = $appointmentObject->getGoogleCalendarEventId()->getValue();
            }
            if ($appointmentObject->getGoogleMeetUrl() !== null) {
                $appointment['googleMeetUrl'] = $appointmentObject->getGoogleMeetUrl();
            }
        }

        if ($outlookCalendarService) {
            try {
                $outlookCalendarService->handleEvent($appointmentObject, self::APPOINTMENT_ADDED);
            } catch (\Exception $e) {
            }

            if ($appointmentObject->getOutlookCalendarEventId() !== null) {
                $appointment['outlookCalendarEventId'] = $appointmentObject->getOutlookCalendarEventId()->getValue();
            }
        }

        foreach ($recurringData as $key => $recurringReservationData) {
            $recurringReservationObject = AppointmentFactory::create($recurringReservationData[Entities::APPOINTMENT]);

            $bookingApplicationService->setReservationEntities($recurringReservationObject);

            if ($zoomService && !$pastAppointment) {
                $zoomService->handleAppointmentMeeting($recurringReservationObject, self::BOOKING_ADDED);

                if ($recurringReservationObject->getZoomMeeting()) {
                    $recurringData[$key][Entities::APPOINTMENT]['zoomMeeting'] =
                        $recurringReservationObject->getZoomMeeting()->toArray();
                }
            }

            if ($lessonSpaceService && !$pastAppointment) {
                $lessonSpaceService->handle($recurringReservationObject, Entities::APPOINTMENT);
                if ($recurringReservationObject->getLessonSpace()) {
                    $recurringData[$key][Entities::APPOINTMENT]['lessonSpace'] = $recurringReservationObject->getLessonSpace();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($recurringReservationObject, self::BOOKING_ADDED);
                } catch (\Exception $e) {
                }

                if ($recurringReservationObject->getGoogleCalendarEventId() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['googleCalendarEventId'] =
                        $recurringReservationObject->getGoogleCalendarEventId()->getValue();
                }
                if ($recurringReservationObject->getGoogleMeetUrl() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['googleMeetUrl'] =
                        $recurringReservationObject->getGoogleMeetUrl();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($recurringReservationObject, self::BOOKING_ADDED);
                } catch (\Exception $e) {
                }

                if ($recurringReservationObject->getOutlookCalendarEventId() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['outlookCalendarEventId'] =
                        $recurringReservationObject->getOutlookCalendarEventId()->getValue();
                }
            }
        }

        $appointment['recurring'] = $recurringData;

        if (!$pastAppointment) {
            /** @var IcsApplicationService $icsService */
            $icsService = $container->get('application.ics.service');

            $recurringIds = [];

            foreach ($appointment['recurring'] as $recurringAppointment) {
                foreach ($recurringAppointment[Entities::APPOINTMENT]['bookings'] as $booking) {
                    $recurringIds[] = $booking['id'];
                }
            }

            foreach ($appointment['bookings'] as $index => $booking) {
                if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
                    $appointment['bookings'][$index]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $booking['id'],
                        $recurringIds,
                        true
                    );
                }
            }

            $emailNotificationService->sendAppointmentStatusNotifications($appointment, false, true, true);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentStatusNotifications($appointment, false, true);
            }
        }

        $webHookService->process(self::BOOKING_ADDED, $appointment, $appointment['bookings']);

        foreach ($recurringData as $key => $recurringReservationData) {
            $webHookService->process(
                self::BOOKING_ADDED,
                $recurringReservationData[Entities::APPOINTMENT],
                $recurringReservationData[Entities::APPOINTMENT]['bookings']
            );
        }
    }
}
