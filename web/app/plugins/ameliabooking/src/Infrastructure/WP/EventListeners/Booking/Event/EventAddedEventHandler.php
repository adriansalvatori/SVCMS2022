<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Application\Services\Zoom\ZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\OutlookCalendarService;

/**
 * Class EventAddedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event
 */
class EventAddedEventHandler
{
    /** @var string */
    const EVENT_ADDED = 'eventAdded';

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
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $container->get('application.booking.booking.service');
        /** @var ZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var LessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');
        /** @var GoogleCalendarService $googleCalendarService */
        $googleCalendarService = $container->get('infrastructure.google.calendar.service');
        /** @var OutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $container->get('infrastructure.outlook.calendar.service');


        $events = $commandResult->getData()[Entities::EVENTS];

        foreach ($events as $event) {
            /** @var Event $reservationObject */
            $reservationObject = EventFactory::create($event);

            $bookingApplicationService->setReservationEntities($reservationObject);

            if ($zoomService) {
                $zoomService->handleEventMeeting($reservationObject, $reservationObject->getPeriods(), self::EVENT_ADDED);
            }
            if ($lessonSpaceService) {
                $lessonSpaceService->handle($reservationObject, Entities::EVENT, $reservationObject->getPeriods());
            }
            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::EVENT_ADDED, $reservationObject->getPeriods());
                } catch (\Exception $e) {
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::EVENT_ADDED, $reservationObject->getPeriods());
                } catch (\Exception $e) {
                }
            }
        }
    }
}
