<?php

namespace AmeliaBooking\Application\Commands\Booking\Event;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class DeleteEventBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Event
 */
class DeleteEventBookingCommandHandler extends CommandHandler
{
    /**
     * @param DeleteEventBookingCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function handle(DeleteEventBookingCommand $command)
    {
        $result = new CommandResult();

        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');

        if (!$this->getContainer()->getPermissionsService()->currentUserCanDelete(Entities::EVENTS)) {
            try {
                /** @var AbstractUser $user */
                $user = $userAS->authorization(
                    $command->getToken(),
                    Entities::PROVIDER
                );
            } catch (AuthorizationException $e) {
                $result = new CommandResult();

                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setData(
                    [
                    'reauthorize' => true
                    ]
                );

                return $result;
            }

            if ($userAS->isCustomer($user)) {
                throw new AccessDeniedException('You are not allowed to delete event bookings');
            }
        }

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        /** @var EventApplicationService $eventApplicationService */
        $eventApplicationService = $this->container->get('application.booking.event.service');

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        /** @var CustomerBooking $customerBooking */
        $customerBooking = $customerBookingRepository->getById((int)$command->getField('id'));

        /** @var Collection $events */
        $events = $eventRepository->getByBookingIds([$customerBooking->getId()->getValue()]);

        /** @var Event $event */
        $event = $events->getItem($events->keys()[0]);

        $customerBookingRepository->beginTransaction();

        if (!$eventApplicationService->deleteEventBooking($customerBooking)) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete booking');

            return $result;
        }

        $customerBookingRepository->commit();

        $event->setNotifyParticipants(
            $bookingAS->isBookingApprovedOrPending($customerBooking->getStatus()->getValue())
        );

        $customerBooking->setChangedStatus(
            new BooleanValueObject(
                $bookingAS->isBookingApprovedOrPending($customerBooking->getStatus()->getValue())
            )
        );

        $customerBooking->setStatus(new BookingStatus(BookingStatus::REJECTED));

        /** @var Collection $payments */
        $payments = $event->getBookings()->getItem($event->getBookings()->keys()[0])->getPayments();

        if ($payments && count($payments->getItems())) {
            $customerBooking->setPayments($payments);
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully deleted event booking');
        $result->setData(
            [
            'type'                     => Entities::EVENT,
            Entities::EVENT            => $event->toArray(),
            Entities::BOOKING          => $customerBooking->toArray(),
            'appointmentStatusChanged' => false
            ]
        );

        return $result;
    }
}
