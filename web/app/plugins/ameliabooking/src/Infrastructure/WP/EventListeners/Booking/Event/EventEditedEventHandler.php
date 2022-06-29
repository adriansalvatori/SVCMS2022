<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\IcsApplicationService;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\WebHook\WebHookApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Booking\Event\EventPeriod;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Application\Services\Zoom\ZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;

/**
 * Class EventEditedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event
 */
class EventEditedEventHandler
{
    /** @var string */
    const TIME_UPDATED = 'bookingTimeUpdated';

    /** @var string */
    const EVENT_DELETED = 'eventDeleted';

    /** @var string */
    const EVENT_ADDED = 'eventAdded';

    /** @var string */
    const EVENT_PERIOD_DELETED = 'eventPeriodDeleted';

    /** @var string */
    const EVENT_PERIOD_ADDED = 'eventPeriodAdded';

    /** @var string */
    const ZOOM_USER_CHANGED = 'zoomUserChanged';
    /** @var string */
    const ZOOM_LICENCED_USER_CHANGED = 'zoomLicencedUserChanged';

    /** @var string */
    const PROVIDER_CHANGED = 'providerChanged';

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
        /** @var EmailNotificationService $emailNotificationService */
        $emailNotificationService = $container->get('application.emailNotification.service');
        /** @var SMSNotificationService $smsNotificationService */
        $smsNotificationService = $container->get('application.smsNotification.service');
        /** @var SettingsService $settingsService */
        $settingsService = $container->get('domain.settings.service');
        /** @var WebHookApplicationService $webHookService */
        $webHookService = $container->get('application.webHook.service');
        /** @var ZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var LessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');
        /** @var GoogleCalendarService $googleCalendarService */
        $googleCalendarService = $container->get('infrastructure.google.calendar.service');
        /** @var OutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $container->get('infrastructure.outlook.calendar.service');

        $eventsData = $commandResult->getData()[Entities::EVENTS];

        /** @var Collection $deletedEvents */
        $deletedEvents = self::getCollection($eventsData['deleted']);

        /** @var Collection $rescheduledEvents */
        $rescheduledEvents = self::getCollection($eventsData['rescheduled']);

        /** @var Collection $addedEvents */
        $addedEvents = self::getCollection($eventsData['added']);

        /** @var Collection $clonedEvents */
        $clonedEvents = self::getCollection($eventsData['cloned']);

        /** @var Event $event */
        foreach ($deletedEvents->getItems() as $event) {
            $eventId = $event->getId()->getValue();

            if ($zoomService &&
                $clonedEvents->keyExists($eventId) &&
                $clonedEvents->getItem($eventId)->getStatus()->getValue() === BookingStatus::APPROVED
            ) {
                $zoomService->handleEventMeeting($event, $event->getPeriods(), self::EVENT_DELETED);
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_DELETED, $event->getPeriods());
                } catch (\Exception $e) {
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEventPeriod($event, self::EVENT_DELETED, $event->getPeriods());
                } catch (\Exception $e) {
                }
            }
        }

        /** @var Event $event */
        foreach ($addedEvents->getItems() as $event) {
            if ($zoomService) {
                $zoomService->handleEventMeeting($event, $event->getPeriods(), self::EVENT_ADDED);
            }
            if ($lessonSpaceService) {
                $lessonSpaceService->handle($event, Entities::EVENT, $event->getPeriods());
            }
            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_ADDED, $event->getPeriods());
                } catch (\Exception $e) {
                }
            }
            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEventPeriod($event, self::EVENT_ADDED, $event->getPeriods());
                } catch (\Exception $e) {
                }
            }
        }

        /** @var Event $event */
        foreach ($clonedEvents->getItems() as $event) {
            if ($lessonSpaceService) {
                $lessonSpaceService->handle($event, Entities::EVENT, $event->getPeriods());
            }
        }

        /** @var Event $event */
        foreach ($rescheduledEvents->getItems() as $event) {
            $eventId = $event->getId()->getValue();

            /** @var Event $clonedEvent */
            $clonedEvent = $clonedEvents->keyExists($eventId) ? $clonedEvents->getItem($eventId) : null;

            if ($lessonSpaceService) {
                $lessonSpaceService->handle($event, Entities::EVENT, $event->getPeriods());
            }

            if ($zoomService && $clonedEvent && $clonedEvent->getStatus()->getValue() === BookingStatus::APPROVED) {
                /** @var Collection $rescheduledPeriods */
                $rescheduledPeriods = new Collection();

                /** @var Collection $addedPeriods */
                $addedPeriods = new Collection();

                /** @var Collection $deletedPeriods */
                $deletedPeriods = new Collection();

                /** @var EventPeriod $eventPeriod */
                foreach ($event->getPeriods()->getItems() as $eventPeriod) {
                    $eventPeriodId = $eventPeriod->getId()->getValue();

                    /** @var EventPeriod $clonedEventPeriod */
                    $clonedEventPeriod = $clonedEvent->getPeriods()->keyExists($eventPeriodId) ?
                        $clonedEvent->getPeriods()->getItem($eventPeriodId) : null;

                    if ($clonedEventPeriod && $clonedEventPeriod->toArray() !== $eventPeriod->toArray()) {
                        $rescheduledPeriods->addItem($eventPeriod, $eventPeriodId);
                    } elseif (!$clonedEventPeriod) {
                        $addedPeriods->addItem($eventPeriod, $eventPeriodId);
                    }
                }

                /** @var EventPeriod $clonedEventPeriod */
                foreach ($clonedEvent->getPeriods()->getItems() as $clonedEventPeriod) {
                    $eventPeriodId = $clonedEventPeriod->getId()->getValue();
                    if (!$event->getPeriods()->keyExists($eventPeriodId)) {
                        $deletedPeriods->addItem($clonedEventPeriod, $clonedEventPeriod->getId()->getValue());
                    }
                }

                if ($rescheduledPeriods->length()) {
                    $zoomService->handleEventMeeting($event, $rescheduledPeriods, self::TIME_UPDATED);
                    if ($googleCalendarService) {
                        try {
                            $googleCalendarService->handleEventPeriodsChange($event, self::TIME_UPDATED, $rescheduledPeriods);
                        } catch (\Exception $e) {
                        }
                    }

                    if ($outlookCalendarService) {
                        try {
                            $outlookCalendarService->handleEventPeriod($event, self::TIME_UPDATED, $rescheduledPeriods);
                        } catch (\Exception $e) {
                        }
                    }
                }

                if ($addedPeriods->length()) {
                    $zoomService->handleEventMeeting($event, $addedPeriods, self::EVENT_PERIOD_ADDED);
                    if ($googleCalendarService) {
                        try {
                            $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_PERIOD_ADDED, $addedPeriods);
                        } catch (\Exception $e) {
                        }
                    }
                    if ($outlookCalendarService) {
                        try {
                            $outlookCalendarService->handleEventPeriod($event, self::EVENT_PERIOD_ADDED, $addedPeriods);
                        } catch (\Exception $e) {
                        }
                    }
                }

                if ($deletedPeriods->length()) {
                    $zoomService->handleEventMeeting($event, $deletedPeriods, self::EVENT_PERIOD_DELETED);
                    if ($googleCalendarService) {
                        try {
                            $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_PERIOD_DELETED, $deletedPeriods);
                        } catch (\Exception $e) {
                        }
                    }
                    if ($outlookCalendarService) {
                        try {
                            $outlookCalendarService->handleEventPeriod($event, self::EVENT_PERIOD_DELETED, $deletedPeriods);
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
        }


        $zoomUserChange = $commandResult->getData()['zoomUserChanged'];
        if ($zoomUserChange) {
            if (!$rescheduledEvents->length()) {
                $command = $commandResult->getData()['zoomUsersLicenced'] ? self::ZOOM_LICENCED_USER_CHANGED : self::ZOOM_USER_CHANGED;
                /** @var Event $event */
                foreach ($clonedEvents->getItems() as $event) {
                    $zoomService->handleEventMeeting($event, $event->getPeriods(), $command, $zoomUserChange);
                }
            }
        }

        if ($commandResult->getData()['newInfo']) {
            if (!$rescheduledEvents->length()) {
                /** @var Event $event */
                foreach ($clonedEvents->getItems() as $event) {
                    $zoomService->handleEventMeeting($event, $event->getPeriods(), EventStatusUpdatedEventHandler::EVENT_STATUS_UPDATED);
                }
            }
        }

        $newProviders    = $commandResult->getData()['newProviders'];
        $removeProviders = $commandResult->getData()['removeProviders'];
        $newInfo         = $commandResult->getData()['newInfo'];
        $organizerChange = $commandResult->getData()['organizerChanged'];
        $newOrganizer    = $commandResult->getData()['newOrganizer'];
        /** @var Event $event */
        foreach ($clonedEvents->getItems() as $event) {
            if ($organizerChange) {
                $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_PERIOD_DELETED, $event->getPeriods());
                $outlookCalendarService->handleEventPeriod($event, self::EVENT_PERIOD_DELETED, $event->getPeriods());
                if ($newOrganizer) {
                    $event->setOrganizerId(new Id($newOrganizer));
                    $googleCalendarService->handleEventPeriodsChange($event, self::EVENT_PERIOD_ADDED, $event->getPeriods());
                    $outlookCalendarService->handleEventPeriod($event, self::EVENT_PERIOD_ADDED, $event->getPeriods());
                }
            }
            if ($newInfo) {
                $event->setName($newInfo['name']);
                $event->setDescription($newInfo['description']);
            }
            if (($newProviders || $removeProviders || $newInfo) && (!$organizerChange || $newOrganizer)) {
                $googleCalendarService->handleEventPeriodsChange($event, self::PROVIDER_CHANGED, $event->getPeriods(), $newProviders, $removeProviders);
                $outlookCalendarService->handleEventPeriod($event, self::PROVIDER_CHANGED, $event->getPeriods(), $newProviders, $removeProviders);
            }
        }


        foreach ($eventsData['rescheduled'] as $eventArray) {

            /** @var IcsApplicationService $icsService */
            $icsService = $container->get('application.ics.service');

            foreach ($eventArray['bookings'] as $index => $booking) {
                if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
                    $eventArray['bookings'][$index]['icsFiles'] = $icsService->getIcsData(
                        Entities::EVENT,
                        $booking['id'],
                        [],
                        true
                    );
                }
            }

            $emailNotificationService->sendAppointmentRescheduleNotifications($eventArray);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentRescheduleNotifications($eventArray);
            }

            $webHookService->process(self::TIME_UPDATED, $eventArray, []);
        }
    }

    /**
     * @param array $eventsArray
     *
     * @return Collection
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    private static function getCollection($eventsArray)
    {
        /** @var Collection $events */
        $events = new Collection();

        foreach ($eventsArray as $eventArray) {
            /** @var Event $eventObject */
            $eventObject = EventFactory::create($eventArray);

            /** @var Collection $eventPeriods */
            $eventPeriods = new Collection();

            /** @var EventPeriod $period */
            foreach ($eventObject->getPeriods()->getItems() as $period) {
                $eventPeriods->addItem($period, $period->getId()->getValue());
            }

            $eventObject->setPeriods($eventPeriods);

            $events->addItem($eventObject, $eventObject->getId()->getValue());
        }
        return $events;
    }
}
