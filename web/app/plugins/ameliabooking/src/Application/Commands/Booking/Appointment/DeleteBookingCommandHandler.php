<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use Slim\Exception\ContainerValueNotFoundException;
use Interop\Container\Exception\ContainerException;

/**
 * Class DeleteBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class DeleteBookingCommandHandler extends CommandHandler
{
    /**
     * @param DeleteBookingCommand $command
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function handle(DeleteBookingCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanDelete(Entities::APPOINTMENTS)) {
            throw new AccessDeniedException('You are not allowed to delete appointment');
        }

        $result = new CommandResult();

        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        /** @var AppointmentApplicationService $appointmentApplicationService */
        $appointmentApplicationService = $this->container->get('application.booking.appointment.service');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');


        /** @var Appointment $appointment */
        $appointment = $appointmentRepository->getByBookingId($command->getArg('id'));

        /** @var CustomerBooking $removedBooking */
        $removedBooking = $appointment->getBookings()->getItem($command->getArg('id'));

        $appointmentRepository->beginTransaction();

        if ($appointment->getBookings()->length() === 1) {
            $resultData = $appointmentApplicationService->removeBookingFromNonGroupAppointment(
                $appointment,
                $removedBooking
            );
        } else {
            $resultData = $appointmentApplicationService->removeBookingFromGroupAppointment(
                $appointment,
                $removedBooking
            );
        }

        $isSuccess = true;

        if ($appointment->getBookings()->length() > 1 && !$bookingApplicationService->delete($removedBooking)) {
            $isSuccess = false;
        } else if (!$appointmentApplicationService->delete($appointment)) {
            $isSuccess = false;
        }

        if (!$isSuccess) {
            $appointmentRepository->rollback();

            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete booking');

            return $result;
        }

        $appointmentRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully deleted booking');
        $result->setData($resultData);

        return $result;
    }
}
