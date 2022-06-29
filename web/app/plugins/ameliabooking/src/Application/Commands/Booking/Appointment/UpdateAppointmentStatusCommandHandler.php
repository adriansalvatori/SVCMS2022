<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Interop\Container\Exception\ContainerException;

/**
 * Class UpdateAppointmentStatusCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class UpdateAppointmentStatusCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'status'
    ];

    /**
     * @param UpdateAppointmentStatusCommand $command
     *
     * @return CommandResult
     *
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function handle(UpdateAppointmentStatusCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWriteStatus(Entities::APPOINTMENTS)) {
            throw new AccessDeniedException('You are not allowed to update appointment status');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');
        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');
        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');
        /** @var AppointmentApplicationService $appointmentAS */
        $appointmentAS = $this->container->get('application.booking.appointment.service');
        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');

        $appointmentId   = (int)$command->getArg('id');
        $requestedStatus = $command->getField('status');

        /** @var Appointment $appointment */
        $appointment = $appointmentRepo->getById($appointmentId);

        $packageCustomerId = $command->getField('packageCustomerId');

        if ($packageCustomerId) {
            $appArray = array_filter(
                $appointment->getBookings()->getItems(),
                function ($booking) use ($packageCustomerId) {
                    /** @var Id $pcId */
                    $pcId = $booking->getPackageCustomerService() ?
                        $booking->getPackageCustomerService()->getPackageCustomer()->getId() : null;

                    return isset($pcId) && $pcId->getValue() === $packageCustomerId;
                }
            );

            $appointment->setBookings(new Collection($appArray));
        }

        $oldStatus = $appointment->getStatus()->getValue();

        if ($bookingAS->isBookingApprovedOrPending($requestedStatus) &&
            $bookingAS->isBookingCanceledOrRejected($appointment->getStatus()->getValue())
        ) {
            /** @var AbstractUser $user */
            $user = $this->container->get('logged.in.user');

            if (!$appointmentAS->canBeBooked($appointment, $userAS->isCustomer($user))) {
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setMessage(FrontendStrings::getCommonStrings()['time_slot_unavailable']);
                $result->setData(
                    [
                        'timeSlotUnavailable' => true,
                        'status'              => $appointment->getStatus()->getValue()
                    ]
                );

                return $result;
            }
        }

        $oldAppointmentArray = $appointment->toArray();

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            $booking->setStatus(new BookingStatus($requestedStatus));
        }

        /** @var Service $service */
        $service = $bookableAS->getAppointmentService(
            $appointment->getServiceId()->getValue(),
            $appointment->getProviderId()->getValue()
        );

        if ($requestedStatus === BookingStatus::APPROVED &&
            (
                (
                    $service->getMaxCapacity()->getValue() === 1 &&
                    $appointment->getBookings()->length() > 1
                ) || (
                    $service->getMaxCapacity()->getValue() > 1 &&
                    $appointment->getBookings()->length() > $service->getMaxCapacity()->getValue()
                )
            )
        ) {
            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('Appointment status not updated');
            $result->setData(
                [
                    Entities::APPOINTMENT       => $appointment->toArray(),
                    'bookingsWithChangedStatus' => [],
                    'status'                    => $appointment->getStatus()->getValue(),
                    'oldStatus'                 => $appointment->getStatus()->getValue(),
                    'message'                   => BackendStrings::getEventStrings()['maximum_capacity_reached'],
                ]
            );

            return $result;
        }

        $appointment->setStatus(new BookingStatus($requestedStatus));

        $appointmentRepo->beginTransaction();

        if ($packageCustomerId) {
            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                $bookingRepository->updateStatusById($booking->getId()->getValue(), $requestedStatus);
            }

            if ($appointment->getBookings()->length() === 1) {
                $appointmentRepo->updateStatusById($appointmentId, $requestedStatus);
            }
        } else {
            $bookingRepository->updateStatusByAppointmentId($appointmentId, $requestedStatus);
            $appointmentRepo->updateStatusById($appointmentId, $requestedStatus);
        }

        $appointmentRepo->commit();

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            if ($booking->getStatus()->getValue() === BookingStatus::APPROVED &&
                ($appointment->getStatus()->getValue() === BookingStatus::PENDING || $appointment->getStatus()->getValue() === BookingStatus::APPROVED)
            ) {
                $booking->setChangedStatus(new BooleanValueObject(true));
            }
        }

        $appointmentArray          = $appointment->toArray();
        $bookingsWithChangedStatus = $bookingAS->getBookingsWithChangedStatus($appointmentArray, $oldAppointmentArray);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated appointment status');
        $result->setData(
            [
            Entities::APPOINTMENT       => $appointmentArray,
            'bookingsWithChangedStatus' => $bookingsWithChangedStatus,
            'status'                    => $requestedStatus,
            'oldStatus'                 => $oldStatus,
            'message'                   =>
                BackendStrings::getAppointmentStrings()['appointment_status_changed'] . strtolower(BackendStrings::getCommonStrings()[$requestedStatus])
            ]
        );

        return $result;
    }
}
