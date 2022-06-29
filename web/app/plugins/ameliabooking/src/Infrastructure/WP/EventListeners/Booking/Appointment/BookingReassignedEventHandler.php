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
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Application\Services\Zoom\ZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class BookingReassignedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class BookingReassignedEventHandler
{
    /** @var string */
    const TIME_UPDATED = 'bookingTimeUpdated';

    /** @var string */
    const APPOINTMENT_DELETED = 'appointmentDeleted';

    /** @var string */
    const APPOINTMENT_ADDED = 'appointmentAdded';

    /** @var string */
    const BOOKING_ADDED = 'bookingAdded';

    /** @var string */
    const BOOKING_CANCELED = 'bookingCanceled';

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
        /** @var IcsApplicationService $icsService */
        $icsService = $container->get('application.ics.service');


        $booking = $commandResult->getData()['booking'];

        $booking['icsFiles'] = $icsService->getIcsData(
            Entities::APPOINTMENT,
            $booking['id'],
            [],
            true
        );


        $oldAppointment = $commandResult->getData()['oldAppointment'];

        $oldAppointmentStatusChanged = $commandResult->getData()['oldAppointmentStatusChanged'];

        /** @var Appointment $oldAppointmentObject */
        $oldAppointmentObject = AppointmentFactory::create($oldAppointment);

        $bookingApplicationService->setReservationEntities($oldAppointmentObject);


        $newAppointment = $commandResult->getData()['newAppointment'];

        /** @var Appointment $newAppointmentObject */
        $newAppointmentObject = null;

        if ($newAppointment !== null) {
            $newAppointmentObject = AppointmentFactory::create($newAppointment);

            $bookingApplicationService->setReservationEntities($newAppointmentObject);
        }


        $existingAppointment = $commandResult->getData()['existingAppointment'];

        $existingAppointmentStatusChanged = $commandResult->getData()['existingAppointmentStatusChanged'];

        /** @var Appointment $existingAppointmentObject */
        $existingAppointmentObject = null;

        if ($existingAppointment !== null) {
            $existingAppointmentObject = AppointmentFactory::create($existingAppointment);

            $bookingApplicationService->setReservationEntities($existingAppointmentObject);
        }


        // appointment is rescheduled
        if ($existingAppointment === null && $newAppointment === null) {
            foreach ($oldAppointment['bookings'] as $bookingKey => $bookingArray) {
                if ($booking['id'] === $bookingArray['id'] && ($bookingArray['status'] === BookingStatus::APPROVED || $bookingArray['status'] === BookingStatus::PENDING)) {
                    $oldAppointment['bookings'][$bookingKey]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $bookingArray['id'],
                        [],
                        true
                    );
                }
            }

            if ($zoomService) {
                $zoomService->handleAppointmentMeeting($oldAppointmentObject, self::TIME_UPDATED);

                if ($oldAppointmentObject->getZoomMeeting()) {
                    $oldAppointment['zoomMeeting'] = $oldAppointmentObject->getZoomMeeting()->toArray();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($oldAppointmentObject, self::TIME_UPDATED);
                } catch (Exception $e) {
                }

                if ($oldAppointmentObject->getGoogleCalendarEventId() !== null) {
                    $oldAppointment['googleCalendarEventId'] = $oldAppointmentObject->getGoogleCalendarEventId()->getValue();
                }
                if ($oldAppointmentObject->getGoogleMeetUrl() !== null) {
                    $oldAppointment['googleMeetUrl'] = $oldAppointmentObject->getGoogleMeetUrl();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($oldAppointmentObject, self::TIME_UPDATED);
                } catch (Exception $e) {
                }

                if ($oldAppointmentObject->getOutlookCalendarEventId() !== null) {
                    $oldAppointment['outlookCalendarEventId'] = $oldAppointmentObject->getOutlookCalendarEventId()->getValue();
                }
            }

            $emailNotificationService->sendAppointmentRescheduleNotifications($oldAppointment);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentRescheduleNotifications($oldAppointment);
            }

            $webHookService->process(self::TIME_UPDATED, $oldAppointment, []);
        }



        // old appointment got status changed to Cancelled because booking is rescheduled to new OR existing appointment
        if ($oldAppointmentObject->getStatus()->getValue() === BookingStatus::CANCELED) {
            if ($zoomService) {
                $zoomService->handleAppointmentMeeting($oldAppointmentObject, self::APPOINTMENT_DELETED);

                if ($oldAppointmentObject->getZoomMeeting()) {
                    $oldAppointment['zoomMeeting'] = $oldAppointmentObject->getZoomMeeting()->toArray();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($oldAppointmentObject, self::APPOINTMENT_DELETED);
                } catch (\Exception $e) {
                }

                if ($oldAppointmentObject->getGoogleCalendarEventId() !== null) {
                    $oldAppointment['googleCalendarEventId'] = $oldAppointmentObject->getGoogleCalendarEventId()->getValue();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($oldAppointmentObject, self::APPOINTMENT_DELETED);
                } catch (\Exception $e) {
                }

                if ($oldAppointmentObject->getOutlookCalendarEventId() !== null) {
                    $oldAppointment['outlookCalendarEventId'] = $oldAppointmentObject->getOutlookCalendarEventId()->getValue();
                }
            }
        }

        // booking is rescheduled to new OR existing appointment
        if (($newAppointment !== null || $existingAppointment !== null) &&
            $oldAppointmentObject->getStatus()->getValue() !== BookingStatus::CANCELED
        ) {
            if ($zoomService) {
                if ($oldAppointmentObject->getZoomMeeting()) {
                    $oldAppointment['zoomMeeting'] = $oldAppointmentObject->getZoomMeeting()->toArray();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($oldAppointmentObject, self::BOOKING_CANCELED);
                } catch (\Exception $e) {
                }

                if ($oldAppointmentObject->getGoogleCalendarEventId() !== null) {
                    $oldAppointment['googleCalendarEventId'] = $oldAppointmentObject->getGoogleCalendarEventId()->getValue();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($oldAppointmentObject, self::BOOKING_CANCELED);
                } catch (\Exception $e) {
                }

                if ($oldAppointmentObject->getOutlookCalendarEventId() !== null) {
                    $oldAppointment['outlookCalendarEventId'] = $oldAppointmentObject->getOutlookCalendarEventId()->getValue();
                }
            }

            if ($oldAppointmentStatusChanged) {
                foreach ($oldAppointment['bookings'] as $bookingKey => $bookingArray) {
                    if ($bookingArray['status'] === BookingStatus::APPROVED || $bookingArray['status'] === BookingStatus::PENDING) {
                        $oldAppointment['bookings'][$bookingKey]['isChangedStatus'] = true;

                        if ($booking['id'] === $bookingArray['id']) {
                            $oldAppointment['bookings'][$bookingKey]['icsFiles'] = $icsService->getIcsData(
                                Entities::APPOINTMENT,
                                $bookingArray['id'],
                                [],
                                true
                            );
                        }
                    }
                }

                $emailNotificationService->sendAppointmentStatusNotifications($oldAppointment, true, true);

                if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                    $smsNotificationService->sendAppointmentStatusNotifications($oldAppointment, true, true);
                }
            }
        }

        if ($newAppointment !== null) {
            if ($zoomService) {
                $zoomService->handleAppointmentMeeting($newAppointmentObject, self::APPOINTMENT_ADDED);

                if ($newAppointmentObject->getZoomMeeting()) {
                    $newAppointment['zoomMeeting'] = $newAppointmentObject->getZoomMeeting()->toArray();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($newAppointmentObject, self::APPOINTMENT_ADDED);
                } catch (\Exception $e) {
                }

                if ($newAppointmentObject->getGoogleCalendarEventId() !== null) {
                    $newAppointment['googleCalendarEventId'] = $newAppointmentObject->getGoogleCalendarEventId()->getValue();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($newAppointmentObject, self::APPOINTMENT_ADDED);
                } catch (\Exception $e) {
                }

                if ($newAppointmentObject->getOutlookCalendarEventId() !== null) {
                    $newAppointment['outlookCalendarEventId'] = $newAppointmentObject->getOutlookCalendarEventId()->getValue();
                }
            }

            foreach ($newAppointment['bookings'] as $bookingKey => $bookingArray) {
                if ($booking['id'] === $bookingArray['id'] && ($bookingArray['status'] === BookingStatus::APPROVED || $bookingArray['status'] === BookingStatus::PENDING)) {
                    $newAppointment['bookings'][$bookingKey]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $bookingArray['id'],
                        [],
                        true
                    );
                }
            }

            $emailNotificationService->sendAppointmentRescheduleNotifications($newAppointment);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentRescheduleNotifications($newAppointment);
            }

            $webHookService->process(self::TIME_UPDATED, $newAppointment, []);
        } else if ($existingAppointment !== null) {
            if ($zoomService) {
                $zoomService->handleAppointmentMeeting($existingAppointmentObject, self::BOOKING_ADDED);

                if ($existingAppointmentObject->getZoomMeeting()) {
                    $existingAppointment['zoomMeeting'] = $existingAppointmentObject->getZoomMeeting()->toArray();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($existingAppointmentObject, self::BOOKING_ADDED);
                } catch (Exception $e) {
                }

                if ($existingAppointmentObject->getGoogleCalendarEventId() !== null) {
                    $existingAppointment['googleCalendarEventId'] = $existingAppointmentObject->getGoogleCalendarEventId()->getValue();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($existingAppointmentObject, self::BOOKING_ADDED);
                } catch (Exception $e) {
                }

                if ($existingAppointmentObject->getOutlookCalendarEventId() !== null) {
                    $existingAppointment['outlookCalendarEventId'] = $existingAppointmentObject->getOutlookCalendarEventId()->getValue();
                }
            }

            $booking['icsFiles'] = $icsService->getIcsData(
                Entities::APPOINTMENT,
                $booking['id'],
                [],
                true
            );

            $emailNotificationService->sendAppointmentRescheduleNotifications(
                array_merge(
                    $existingAppointment,
                    ['bookings' => [$booking]]
                )
            );

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentRescheduleNotifications(
                    array_merge(
                        $existingAppointment,
                        ['bookings' => [$booking]]
                    )
                );
            }

            if ($existingAppointmentStatusChanged) {
                foreach ($existingAppointment['bookings'] as $bookingKey => $bookingArray) {
                    if ($bookingArray['status'] === BookingStatus::APPROVED &&
                        $existingAppointment['status'] === BookingStatus::APPROVED &&
                        $bookingArray['id'] !== $booking['id']
                    ) {
                        $existingAppointment['bookings'][$bookingKey]['isChangedStatus'] = true;
                    }
                }

                $emailNotificationService->sendAppointmentStatusNotifications($existingAppointment, true, true);

                if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                    $smsNotificationService->sendAppointmentStatusNotifications($existingAppointment, true, true);
                }
            }
        }
    }
}
