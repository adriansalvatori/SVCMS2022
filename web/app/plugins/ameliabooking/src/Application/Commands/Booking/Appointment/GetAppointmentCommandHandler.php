<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\CustomerApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class GetAppointmentCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class GetAppointmentCommandHandler extends CommandHandler
{
    /**
     * @param GetAppointmentCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function handle(GetAppointmentCommand $command)
    {
        $result = new CommandResult();

        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');

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

        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerApplicationService $customerAS */
        $customerAS = $this->container->get('application.user.customer.service');

        /** @var Appointment $appointment */
        $appointment = $appointmentRepo->getById((int)$command->getField('id'));

        if ($userAS->isCustomer($user) && !$customerAS->hasCustomerBooking($appointment->getBookings(), $user)) {
            throw new AccessDeniedException('You are not allowed to read appointment');
        }

        /** @var Collection $recurringAppointments */
        $recurringAppointments = $appointmentRepo->getFiltered(
            [
                'parentId' => $appointment->getParentId() ?
                    $appointment->getParentId()->getValue() : $appointment->getId()->getValue()
            ]
        );

        $customerAS->removeBookingsForOtherCustomers($user, new Collection([$appointment]));

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            $customFields = [];

            if ($booking->getCustomFields() &&
                ($customFields = json_decode($booking->getCustomFields()->getValue(), true)) === null
            ) {
                $booking->setCustomFields(null);
            }

            if ($customFields) {
                $parsedCustomFields = [];

                foreach ((array)$customFields as $key => $customField) {
                    if ($customField) {
                        $parsedCustomFields[$key] = $customField;
                    }
                }

                $booking->setCustomFields(new Json(json_encode($parsedCustomFields)));
            }
        }

        if (!empty($command->getField('params')['timeZone'])) {
            $appointment->getBookingStart()->getValue()->setTimezone(
                new \DateTimeZone($command->getField('params')['timeZone'])
            );

            $appointment->getBookingEnd()->getValue()->setTimezone(
                new \DateTimeZone($command->getField('params')['timeZone'])
            );
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved appointment');
        $result->setData(
            [
                Entities::APPOINTMENT => $appointment->toArray(),
                'recurring'           => $recurringAppointments->toArray()
            ]
        );

        return $result;
    }
}
