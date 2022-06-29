<?php

namespace AmeliaBooking\Application\Services\Bookable;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use Exception;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class BasicPackageApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class BasicPackageApplicationService extends AbstractPackageApplicationService
{

    /**
     * @param Collection $packageCustomerServices
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     */
    public function deletePackageCustomer($packageCustomerServices)
    {
        return true;
    }

    /**
     * @param Collection $appointments
     *
     * @return void
     */
    public function setPackageBookingsForAppointments($appointments)
    {
    }

    /**
     * @param int  $packageCustomerServiceId
     * @param int  $customerId
     * @param bool $isCabinetBooking
     *
     * @return boolean
     */
    public function isBookingAvailableForPurchasedPackage($packageCustomerServiceId, $customerId, $isCabinetBooking)
    {
        return false;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getPackageStatsData($params)
    {
        return [];
    }

    /**
     * @param array      $packageDatesData
     * @param Collection $appointmentsPackageCustomerServices
     * @param int        $packageCustomerServiceId
     * @param string     $date
     * @param int        $occupiedDuration
     *
     * @return void
     */
    public function updatePackageStatsData(
        &$packageDatesData,
        $appointmentsPackageCustomerServices,
        $packageCustomerServiceId,
        $date,
        $occupiedDuration
    ) {
    }

    /**
     * @param Collection $appointments
     *
     * @return Collection
     *
     * @throws Exception
     */
    public function getPackageCustomerServicesForAppointments($appointments)
    {
        return new Collection();
    }

    /**
     * @param Collection $appointments
     * @param array      $params
     *
     * @return array
     */
    public function getPackageAvailability($appointments, $params)
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPackagesArray()
    {
        return [];
    }
}
