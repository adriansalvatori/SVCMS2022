<?php

namespace AmeliaBooking\Domain\Services\Booking;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;

/**
 * Class AppointmentDomainService
 *
 * @package AmeliaBooking\Domain\Services\Booking
 */
class AppointmentDomainService
{
    /**
     * Returns an array with bookings statuses count for passed appointment
     *
     * @param Appointment $appointment
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getBookingsStatusesCount($appointment)
    {
        $approvedBookings = 0;
        $pendingBookings = 0;
        $canceledBookings = 0;
        $rejectedBookings = 0;

        foreach ((array)$appointment->getBookings()->keys() as $customerBookingKey) {
            /** @var CustomerBooking $booking */
            $booking = $appointment->getBookings()->getItem($customerBookingKey);

            switch ($booking->getStatus()->getValue()) {
                case BookingStatus::PENDING:
                    $pendingBookings += $booking->getPersons()->getValue();
                    break;
                case BookingStatus::CANCELED:
                    $canceledBookings += $booking->getPersons()->getValue();
                    break;
                case BookingStatus::REJECTED:
                    $rejectedBookings += $booking->getPersons()->getValue();
                    break;
                default:
                    $approvedBookings += $booking->getPersons()->getValue();
                    break;
            }
        }

        return [
            'approvedBookings' => $approvedBookings,
            'pendingBookings'  => $pendingBookings,
            'canceledBookings' => $canceledBookings,
            'rejectedBookings' => $rejectedBookings
        ];
    }

    /**
     * @param Service $service
     * @param array   $bookingsCount
     *
     * @return string
     */
    public function getAppointmentStatusWhenEditAppointment($service, $bookingsCount)
    {
        $totalBookings = array_sum($bookingsCount);

        if ($bookingsCount['canceledBookings'] === $totalBookings) {
            return BookingStatus::CANCELED;
        }

        if ($bookingsCount['rejectedBookings'] === $totalBookings) {
            return BookingStatus::REJECTED;
        }

        if ($bookingsCount['rejectedBookings'] + $bookingsCount['canceledBookings'] === $totalBookings) {
            return BookingStatus::CANCELED;
        }

        return $bookingsCount['approvedBookings'] >= $service->getMinCapacity()->getValue() ?
            BookingStatus::APPROVED : BookingStatus::PENDING;
    }

    /**
     * When booking status is changed (customer cancel the booking), calculate appointment booking status.
     *
     * If there is no any more 'approved' and 'pending' bookings, set appointment status to 'cancel'.
     *
     * If appointment status is 'approved' or 'pending' and minimum capacity condition is not satisfied,
     * set appointment status to 'pending'.
     *
     * @param Service $service
     * @param array   $bookingsCount
     * @param string  $appointmentStatus
     *
     * @return string
     */
    public function getAppointmentStatusWhenChangingBookingStatus($service, $bookingsCount, $appointmentStatus)
    {
        if ($bookingsCount['approvedBookings'] === 0 && $bookingsCount['pendingBookings'] === 0) {
            return BookingStatus::CANCELED;
        }

        return $bookingsCount['approvedBookings'] >= $service->getMinCapacity()->getValue() ?
            BookingStatus::APPROVED : BookingStatus::PENDING;
    }
}
