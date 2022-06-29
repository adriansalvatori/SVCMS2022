<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\BookingCancellationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Interop\Container\Exception\ContainerException;

/**
 * Class UpdateAppointmentTimeCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class UpdateAppointmentTimeCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'bookingStart'
    ];

    /**
     * @param UpdateAppointmentTimeCommand $command
     *
     * @return CommandResult
     *
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws ContainerException
     */
    public function handle(UpdateAppointmentTimeCommand $command)
    {
        $this->checkMandatoryFields($command);

        $result = new CommandResult();

        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');
        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');
        /** @var AppointmentApplicationService $appointmentAS */
        $appointmentAS = $this->container->get('application.booking.appointment.service');
        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');
        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(Entities::APPOINTMENT);
        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');

        try {
            /** @var AbstractUser $user */
            $user = $userAS->authorization(
                $command->getPage() === 'cabinet' ? $command->getToken() : null,
                $command->getCabinetType()
            );
        } catch (AuthorizationException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setData([
                'reauthorize' => true
            ]);

            return $result;
        }

        if ($userAS->isCustomer($user) && !$settingsDS->getSetting('roles', 'allowCustomerReschedule')) {
            throw new AccessDeniedException('You are not allowed to update appointment');
        }

        /** @var Appointment $appointment */
        $appointment = $appointmentRepo->getById((int)$command->getArg('id'));

        /** @var Service $service */
        $service = $bookableAS->getAppointmentService(
            $appointment->getServiceId()->getValue(),
            $appointment->getProviderId()->getValue()
        );

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            if ($userAS->isAmeliaUser($user) &&
                $userAS->isCustomer($user) &&
                $bookingAS->isBookingApprovedOrPending($booking->getStatus()->getValue()) &&
                ($service->getMinCapacity()->getValue() !== 1 || $service->getMaxCapacity()->getValue() !== 1) &&
                ($user->getId() && $booking->getCustomerId()->getValue() !== $user->getId()->getValue())
            ) {
                throw new AccessDeniedException('You are not allowed to update appointment');
            }
        }

        $minimumRescheduleTimeInSeconds = $settingsDS
            ->getEntitySettings($service->getSettings())
            ->getGeneralSettings()
            ->getMinimumTimeRequirementPriorToRescheduling();

        try {
            $reservationService->inspectMinimumCancellationTime(
                $appointment->getBookingStart()->getValue(),
                $minimumRescheduleTimeInSeconds
            );
        } catch (BookingCancellationException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('You are not allowed to update booking');
            $result->setData([
                'rescheduleBookingUnavailable' => true
            ]);

            return $result;
        }

        $bookingStart = $command->getField('bookingStart');

        $bookingStartInUtc = DateTimeService::getCustomDateTimeObject(
            $bookingStart
        )->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i');

        // Convert UTC slot to slot in TimeZone based on Settings
        if ($command->getField('utcOffset') !== null && $settingsDS->getSetting('general', 'showClientTimeZone')) {
            $bookingStart = DateTimeService::getCustomDateTimeFromUtc(
                $bookingStart
            );
        }

        $appointment->setBookingStart(
            new DateTimeValue(
                DateTimeService::getCustomDateTimeObject(
                    $bookingStart
                )
            )
        );

        $appointment->setBookingEnd(
            new DateTimeValue(
                DateTimeService::getCustomDateTimeObject($bookingStart)
                    ->modify('+' . $appointmentAS->getAppointmentLengthTime($appointment, $service) . ' second')
            )
        );

        if (!$appointmentAS->canBeBooked($appointment, $userAS->isCustomer($user))) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['time_slot_unavailable']);
            $result->setData([
                'timeSlotUnavailable' => true
            ]);

            return $result;
        }

        $appointmentRepo->update((int)$command->getArg('id'), $appointment);

        foreach ($appointment->getBookings()->getItems() as $booking) {
            $paymentAS->updateBookingPaymentDate($booking, $bookingStartInUtc);
        }

        $appointment->setRescheduled(new BooleanValueObject(true));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated appointment time');
        $result->setData([
            Entities::APPOINTMENT => $appointment->toArray()
        ]);

        return $result;
    }
}
