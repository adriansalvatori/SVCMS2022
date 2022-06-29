<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\Reservation\AppointmentReservationService;
use AmeliaBooking\Application\Services\TimeSlot\TimeSlotService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\BookingCancellationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\Booking\AppointmentDomainService;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Exception;
use Interop\Container\Exception\ContainerException;

/**
 * Class ReassignBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class ReassignBookingCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'bookingStart'
    ];

    /**
     * @param ReassignBookingCommand $command
     *
     * @return CommandResult
     *
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(ReassignBookingCommand $command)
    {
        $this->checkMandatoryFields($command);

        $result = new CommandResult();

        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');
        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');
        /** @var AppointmentApplicationService $appointmentAS */
        $appointmentAS = $this->container->get('application.booking.appointment.service');
        /** @var AppointmentDomainService $appointmentDS */
        $appointmentDS = $this->container->get('domain.booking.appointment.service');
        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');
        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');
        /** @var AppointmentReservationService $reservationService */
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
            $result->setData(
                [
                    'reauthorize' => true
                ]
            );

            return $result;
        }

        if ($userAS->isCustomer($user) &&
            !$settingsDS->getSetting('roles', 'allowCustomerReschedule')
        ) {
            throw new AccessDeniedException('You are not allowed to update appointment');
        }

        /** @var Appointment $oldAppointment */
        $oldAppointment = $reservationService->getReservationByBookingId((int)$command->getArg('id'));

        /** @var CustomerBooking $booking */
        $booking = $oldAppointment->getBookings()->getItem((int)$command->getArg('id'));

        $oldAppointmentStatusChanged = false;

        /** @var CustomerBooking $oldAppointmentBooking */
        foreach ($oldAppointment->getBookings()->getItems() as $oldAppointmentBooking) {
            if ($userAS->isAmeliaUser($user) &&
                $userAS->isCustomer($user) &&
                ($booking->getId()->getValue() === $oldAppointmentBooking->getId()->getValue()) &&
                ($user->getId() && $oldAppointmentBooking->getCustomerId()->getValue() !== $user->getId()->getValue())
            ) {
                throw new AccessDeniedException('You are not allowed to update appointment');
            }
        }

        /** @var Service $service */
        $service = $bookableAS->getAppointmentService(
            $oldAppointment->getServiceId()->getValue(),
            $oldAppointment->getProviderId()->getValue()
        );

        $minimumRescheduleTimeInSeconds = $settingsDS
            ->getEntitySettings($service->getSettings())
            ->getGeneralSettings()
            ->getMinimumTimeRequirementPriorToRescheduling();

        try {
            $reservationService->inspectMinimumCancellationTime(
                $oldAppointment->getBookingStart()->getValue(),
                $minimumRescheduleTimeInSeconds
            );
        } catch (BookingCancellationException $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('You are not allowed to update booking');
            $result->setData(
                [
                    'rescheduleBookingUnavailable' => true
                ]
            );

            return $result;
        }

        $bookingStart = $command->getField('bookingStart');

        $bookingStartInUtc = DateTimeService::getCustomDateTimeObject(
            $bookingStart
        )->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i');

        if ($command->getField('timeZone') === 'UTC') {
            $bookingStart = DateTimeService::getCustomDateTimeFromUtc(
                $bookingStart
            );
        } elseif ($command->getField('timeZone')) {
            $bookingStart = DateTimeService::getDateTimeObjectInTimeZone(
                $bookingStart,
                $command->getField('timeZone')
            )->setTimezone(DateTimeService::getTimeZone())->format('Y-m-d H:i:s');
        } elseif ($command->getField('utcOffset') !== null &&
            $settingsDS->getSetting('general', 'showClientTimeZone')
        ) {
            $bookingStart = DateTimeService::getCustomDateTimeFromUtc(
                $bookingStart
            );
        }

        /** @var TimeSlotService $timeSlotService */
        $timeSlotService = $this->container->get('application.timeSlot.service');

        if (!$timeSlotService->isSlotFree(
            $service->getId()->getValue(),
            DateTimeService::getCustomDateTimeObject(
                $bookingStart
            ),
            $oldAppointment->getProviderId()->getValue(),
            $booking->getExtras()->getItems(),
            $oldAppointment->getId()->getValue(),
            $booking->getPersons()->getValue(),
            true
        )) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['time_slot_unavailable']);
            $result->setData(
                [
                    'timeSlotUnavailable' => true
                ]
            );

            return $result;
        }

        /** @var Collection $existingAppointments */
        $existingAppointments = $appointmentRepository->getFiltered(
            [
                'dates'     => [$bookingStart, $bookingStart],
                'services'  => [$oldAppointment->getServiceId()->getValue()],
                'providers' => [$oldAppointment->getProviderId()->getValue()]
            ]
        );

        /** @var Appointment $newAppointment */
        $newAppointment = null;

        /** @var Appointment $existingAppointment */
        $existingAppointment = $existingAppointments->length() ?
            $existingAppointments->getItem($existingAppointments->keys()[0]) : null;

        if ($existingAppointment &&
            $existingAppointment->getId()->getValue() === $oldAppointment->getId()->getValue()
        ) {
            $existingAppointment = null;
        }

        /** @var BookingStatus $bookingStatus  */
        $bookingStatus = $settingsDS
            ->getEntitySettings($service->getSettings())
            ->getGeneralSettings()
            ->getDefaultAppointmentStatus();

        $existingAppointmentStatusChanged = false;

        $appointmentRepository->beginTransaction();

        if ($existingAppointment === null &&
            (
                $oldAppointment->getBookings()->length() === 1 ||
                $bookingStart === $oldAppointment->getBookingStart()->getValue()->format('Y-m-d H:i')
            )
        ) {
            $oldAppointment->setBookingStart(
                new DateTimeValue(
                    DateTimeService::getCustomDateTimeObject(
                        $bookingStart
                    )
                )
            );

            $oldAppointment->setBookingEnd(
                new DateTimeValue(
                    DateTimeService::getCustomDateTimeObject($bookingStart)
                        ->modify(
                            '+' . $appointmentAS->getAppointmentLengthTime($oldAppointment, $service) . ' second'
                        )
                )
            );

            if ($oldAppointment->getStatus()->getValue() === BookingStatus::APPROVED && $bookingStatus === BookingStatus::PENDING) {
                $oldAppointment->setStatus(new BookingStatus($bookingStatus));
                $booking->setStatus(new BookingStatus(BookingStatus::PENDING));
                $booking->setChangedStatus(new BooleanValueObject(true));

                $bookingRepository->updateFieldById(
                    $booking->getId()->getValue(),
                    $bookingStatus,
                    'status'
                );
            }

            $paymentAS->updateBookingPaymentDate($booking, $bookingStartInUtc);

            $appointmentRepository->update($oldAppointment->getId()->getValue(), $oldAppointment);

            $oldAppointment->setRescheduled(new BooleanValueObject(true));

            $reservationService->updateWooCommerceOrder($booking, $oldAppointment);
        } else {
            $oldAppointment->getBookings()->deleteItem($booking->getId()->getValue());

            if ($existingAppointment !== null) {
                $booking->setAppointmentId($existingAppointment->getId());

                if ($booking->getStatus()->getValue() === BookingStatus::APPROVED && $bookingStatus === BookingStatus::PENDING) {
                    $booking->setStatus(new BookingStatus(BookingStatus::PENDING));
                    $booking->setChangedStatus(new BooleanValueObject(true));

                    $bookingRepository->updateFieldById(
                        $booking->getId()->getValue(),
                        $bookingStatus,
                        'status'
                    );
                }

                $existingAppointment->getBookings()->addItem($booking, $booking->getId()->getValue());

                $existingAppointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                    $service,
                    $appointmentDS->getBookingsStatusesCount($existingAppointment)
                );

                $existingAppointmentStatusChanged = $existingAppointment->getStatus()->getValue() !== $existingAppointmentStatus;

                $existingAppointment->setStatus(new BookingStatus($existingAppointmentStatus));

                $existingAppointment->setBookingEnd(
                    new DateTimeValue(
                        DateTimeService::getCustomDateTimeObject($bookingStart)
                            ->modify(
                                '+' . $appointmentAS->getAppointmentLengthTime($existingAppointment, $service) . ' second'
                            )
                    )
                );

                $bookingRepository->updateFieldById(
                    $booking->getId()->getValue(),
                    $existingAppointment->getId()->getValue(),
                    'appointmentId'
                );

                $paymentAS->updateBookingPaymentDate($booking, $bookingStartInUtc);

                $appointmentRepository->update($existingAppointment->getId()->getValue(), $existingAppointment);

                $reservationService->updateWooCommerceOrder($booking, $existingAppointment);
            } else {
                $newAppointment = AppointmentFactory::create(
                    array_merge(
                        $oldAppointment->toArray(),
                        [
                            'id'                     => null,
                            'googleCalendarEventId'  => null,
                            'outlookCalendarEventId' => null,
                            'zoomMeeting'            => null,
                            'bookings'               => [],
                        ]
                    )
                );

                $newAppointment->getBookings()->addItem($booking, $booking->getId()->getValue());

                $newAppointment->setBookingStart(
                    new DateTimeValue(
                        DateTimeService::getCustomDateTimeObject(
                            $bookingStart
                        )
                    )
                );

                $newAppointment->setBookingEnd(
                    new DateTimeValue(
                        DateTimeService::getCustomDateTimeObject($bookingStart)
                            ->modify(
                                '+' . $appointmentAS->getAppointmentLengthTime($newAppointment, $service) . ' second'
                            )
                    )
                );

                $newAppointment->setRescheduled(new BooleanValueObject(true));

                $newAppointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                    $service,
                    $appointmentDS->getBookingsStatusesCount($newAppointment)
                );

                $newAppointment->setStatus(new BookingStatus($newAppointmentStatus));

                $newAppointmentId = $appointmentRepository->add($newAppointment);

                $newAppointment->setId(new Id($newAppointmentId));

                $booking->setAppointmentId(new Id($newAppointmentId));

                $bookingRepository->updateFieldById(
                    $booking->getId()->getValue(),
                    $newAppointmentId,
                    'appointmentId'
                );

                $paymentAS->updateBookingPaymentDate($booking, $bookingStartInUtc);

                $reservationService->updateWooCommerceOrder($booking, $newAppointment);
            }

            if ($oldAppointment->getBookings()->length() === 0) {
                $appointmentRepository->delete($oldAppointment->getId()->getValue());

                $oldAppointment->setStatus(new BookingStatus(BookingStatus::CANCELED));

                $oldAppointmentStatusChanged = true;
            } else {
                $oldAppointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                    $service,
                    $appointmentDS->getBookingsStatusesCount($oldAppointment)
                );

                $oldAppointmentStatusChanged = $oldAppointment->getStatus()->getValue() !== $oldAppointmentStatus;

                $oldAppointment->setStatus(new BookingStatus($oldAppointmentStatus));

                $oldAppointment->setBookingEnd(
                    new DateTimeValue(
                        DateTimeService::getCustomDateTimeObject(
                            $oldAppointment->getBookingStart()->getValue()->format('Y-m-d H:i:s')
                        )->modify(
                            '+' . $appointmentAS->getAppointmentLengthTime($oldAppointment, $service) . ' second'
                        )
                    )
                );

                $appointmentRepository->update($oldAppointment->getId()->getValue(), $oldAppointment);
            }
        }

        $appointmentRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated appointment');
        $result->setData(
            [
                Entities::BOOKING                  => $booking ? $booking->toArray() : null,
                'newAppointment'                   => $newAppointment ? $newAppointment->toArray() : null,
                'oldAppointment'                   => $oldAppointment ? $oldAppointment->toArray() : null,
                'oldAppointmentStatusChanged'      => $oldAppointmentStatusChanged,
                'existingAppointment'              => $existingAppointment ? $existingAppointment->toArray() : null,
                'existingAppointmentStatusChanged' => $existingAppointmentStatusChanged,
            ]
        );

        return $result;
    }
}
