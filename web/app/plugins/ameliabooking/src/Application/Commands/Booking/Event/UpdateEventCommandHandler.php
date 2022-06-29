<?php

namespace AmeliaBooking\Application\Commands\Booking\Event;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Booking\Event\EventPeriod;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Services\Zoom\ZoomService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class UpdateEventCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Event
 */
class UpdateEventCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'id',
        'name',
        'periods',
        'applyGlobally'
    ];

    /**
     * @param UpdateEventCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(UpdateEventCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');
        /** @var EventApplicationService $eventApplicationService */
        $eventApplicationService = $this->container->get('application.booking.event.service');
        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');
        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');

        try {
            /** @var AbstractUser $user */
            $user = $userAS->authorization(
                $command->getPage() === 'cabinet' ? $command->getToken() : null,
                $command->getCabinetType()
            );
        } catch (AuthorizationException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setData(
                [
                    'reauthorize' => true
                ]
            );

            return $result;
        }

        if ($userAS->isCustomer($user) ||
            (
                $userAS->isProvider($user) && !$settingsDS->getSetting('roles', 'allowWriteEvents')
            )
        ) {
            throw new AccessDeniedException('You are not allowed to update an event');
        }

        /** @var Event $event */
        $event = $eventApplicationService->build($command->getFields());

        /** @var Event $event */
        $oldEvent = $eventApplicationService->getEventById(
            $event->getId()->getValue(),
            [
                'fetchEventsPeriods'    => true,
                'fetchEventsTickets'    => true,
                'fetchEventsTags'       => true,
                'fetchEventsProviders'  => true,
                'fetchEventsImages'     => true,
                'fetchApprovedBookings' => false,
                'fetchBookingsTickets'  => true,
                'fetchBookingsUsers'    => true,
                'fetchBookingsPayments' => true,
            ]
        );

        if ($oldEvent->getRecurring() &&
            $event->getRecurring() &&
            (
                $event->getRecurring()->getUntil()->getValue() < $oldEvent->getRecurring()->getUntil()->getValue() ||
                $event->getRecurring()->getCycle()->getValue() !== $oldEvent->getRecurring()->getCycle()->getValue()
            )
        ) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update event');

            return $result;
        }

        $event->setBookings($oldEvent->getBookings());

        /** @var EventPeriod $oldEventPeriod */
        foreach ($oldEvent->getPeriods()->getItems() as $oldEventPeriod) {
            /** @var EventPeriod $eventPeriod */
            foreach ($event->getPeriods()->getItems() as $eventPeriod) {
                if ($eventPeriod->getId() &&
                    $oldEventPeriod->getId()->getValue() === $eventPeriod->getId()->getValue()
                ) {
                    if ($oldEventPeriod->getZoomMeeting()) {
                        $eventPeriod->setZoomMeeting($oldEventPeriod->getZoomMeeting());
                    }
                    if ($oldEventPeriod->getLessonSpace()) {
                        $eventPeriod->setLessonSpace($oldEventPeriod->getLessonSpace());
                    }
                }
            }
        }

        $eventRepository->beginTransaction();

        try {
            $parsedEvents = $eventApplicationService->update(
                $oldEvent,
                $event,
                $command->getField('applyGlobally')
            );
        } catch (QueryExecutionException $e) {
            $eventRepository->rollback();
            throw $e;
        }

        $eventRepository->commit();


        $providersRemoved = array_udiff(
            $oldEvent->getProviders()->getItems(),
            $event->getProviders()->getItems(),
            function ($a, $b) {
                if ($a->getId()->getValue() == $b->getId()->getValue()) {
                    return 0;
                } else {
                    return ($a->getId()->getValue() < $b->getId()->getValue() ? -1 : 1);
                }
            }
        );

        $providersAdded = array_udiff(
            $event->getProviders()->getItems(),
            $oldEvent->getProviders()->getItems(),
            function ($a, $b) {
                if ($a->getId()->getValue() == $b->getId()->getValue()) {
                    return 0;
                } else {
                    return ($a->getId()->getValue() < $b->getId()->getValue() ? -1 : 1);
                }
            }
        );

        $newInfo = ($event->getDescription() ? $event->getDescription()->getValue() : null) !==
            ($oldEvent->getDescription() ? $oldEvent->getDescription()->getValue() : null) ||
            $event->getName()->getValue() !== $oldEvent->getName()->getValue();

        $zoomUserChanged = ($event->getZoomUserId() ? $event->getZoomUserId()->getValue() : null) !==
            ($oldEvent->getZoomUserId() ? $oldEvent->getZoomUserId()->getValue() : null);

        $zoomUsersLicenced = false;

        if ($oldEvent->getZoomUserId() && $event->getZoomUserId() && $zoomUserChanged) {
            /** @var ZoomService $zoomService */
            $zoomService = $this->container->get('infrastructure.zoom.service');

            $zoomUserType    = 0;
            $zoomOldUserType = 0;
            $zoomResult      = $zoomService->getUsers();
            if (!(isset($zoomResult['code']) && $zoomResult['code'] === 124) &&
                !($zoomResult['users'] === null && isset($zoomResult['message']))) {
                $zoomUsers = $zoomResult['users'];
                foreach ($zoomUsers as $key => $val) {
                    if ($val['id'] === $event->getZoomUserId()->getValue()) {
                        $zoomUserType = $val['type'];
                    }
                    if ($val['id'] === $oldEvent->getZoomUserId()->getValue()) {
                        $zoomOldUserType = $val['type'];
                    }
                }
            }
            if ($zoomOldUserType > 1 && $zoomUserType > 1) {
                $zoomUsersLicenced = true;
            }
        }

        $organizerChanged = ($event->getOrganizerId() ? $event->getOrganizerId()->getValue() : null)
            !== ($oldEvent->getOrganizerId() ? $oldEvent->getOrganizerId()->getValue() : null);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated event.');
        $result->setData(
            [
                Entities::EVENTS => $parsedEvents,
                'zoomUserChanged'  => $zoomUserChanged && $event->getZoomUserId() ? $event->getZoomUserId()->getValue() : null,
                'zoomUsersLicenced'  => $zoomUsersLicenced,
                'newInfo'        => $newInfo ? [
                    'name'        => $event->getName(),
                    'description' => $event->getDescription()
                ] : null,
                'newProviders'  => $providersAdded,
                'removeProviders' => $providersRemoved,
                'organizerChanged' => $organizerChanged,
                'newOrganizer'    =>  $event->getOrganizerId() ? $event->getOrganizerId()->getValue() : null,
            ]
        );

        return $result;
    }
}
