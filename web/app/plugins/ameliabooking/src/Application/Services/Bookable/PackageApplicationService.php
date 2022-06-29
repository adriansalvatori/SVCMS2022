<?php

namespace AmeliaBooking\Application\Services\Bookable;

use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageService;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerFactory;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerServiceFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentType;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use Exception;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class PackageApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class PackageApplicationService extends AbstractPackageApplicationService
{

    /**
     * @param Package $package
     * @param int     $customerId
     * @param int     $utcOffset
     * @param float   $price
     * @param bool    $save
     *
     * @return PackageCustomer
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function addPackageCustomer($package, $customerId, $utcOffset, $price, $save)
    {
        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

        $endDateTime = null;

        if ($package->getEndDate()) {
            $endDateTime = $package->getEndDate()->getValue();
        } elseif ($package->getDurationCount()) {
            $endDateTime = DateTimeService::getNowDateTimeObject()
                ->modify("+{$package->getDurationCount()->getValue()} {$package->getDurationType()->getValue()}");
        }

        $startDateTimeString = DateTimeService::getNowDateTimeInUtc();

        /** @var PackageCustomer $packageCustomer */
        $packageCustomer = PackageCustomerFactory::create(
            [
                'customerId' => $customerId,
                'packageId'  => $package->getId()->getValue(),
                'price'      => $price,
                'end'        => $endDateTime ? $endDateTime->format('Y-m-d H:i:s') : null,
                'start'      => $startDateTimeString,
                'purchased'  => $startDateTimeString,
            ]
        );

        if ($save) {
            $packageCustomerId = $packageCustomerRepository->add($packageCustomer);

            $packageCustomer->setId(new Id($packageCustomerId));
        }

        return $packageCustomer;
    }

    /**
     * @param Package         $package
     * @param PackageCustomer $packageCustomer
     * @param array           $packageRules
     * @param bool            $save
     *
     * @return Collection
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function addPackageCustomerServices($package, $packageCustomer, $packageRules, $save)
    {
        $packageCustomerServices = new Collection();

        /** @var PackageService $packageService */
        foreach ($package->getBookable()->getItems() as $packageService) {
            $serviceIndex = array_search(
                $packageService->getService()->getId()->getValue(),
                array_column($packageRules, 'serviceId'),
                false
            );

            $packageData = [
                'serviceId'    => $packageService->getService()->getId()->getValue(),
                'providerId'   =>  $serviceIndex !== false && $packageRules[$serviceIndex]['providerId']
                        ? $packageRules[$serviceIndex]['providerId'] : null,
                'locationId'        => $serviceIndex !== false && $packageRules[$serviceIndex]['locationId']
                    ? $packageRules[$serviceIndex]['locationId'] : null,
                'bookingsCount'     => $packageService->getQuantity()->getValue(),
                'packageCustomer'   => $packageCustomer->toArray(),
            ];

            $packageCustomerService = $this->createPackageCustomerService($save, $packageData);

            $packageCustomerServices->addItem($packageCustomerService);
        }

        return $packageCustomerServices;
    }


    /**
     * @param bool         $save
     * @param array $packageData
     *
     * @return PackageCustomerService
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function createPackageCustomerService($save, $packageData)
    {
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        $packageCustomerService = PackageCustomerServiceFactory::create($packageData);

        if ($save) {
            $packageCustomerServiceId = $packageCustomerServiceRepository->add($packageCustomerService);

            $packageCustomerService->setId(new Id($packageCustomerServiceId));
        }

        return $packageCustomerService;
    }

    /**
     * @param Collection $packageCustomerServices
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function deletePackageCustomer($packageCustomerServices)
    {
        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');
        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
            $id = $packageCustomerService->getPackageCustomer()->getId()->getValue();

            /** @var Collection $payments */
            $payments = $paymentRepository->getByEntityId($id, 'packageCustomerId');

            /** @var Payment $payment */
            foreach ($payments->getItems() as $payment) {
                if (!$paymentAS->delete($payment)) {
                    return false;
                }
            }

            if ($packageCustomerServiceRepository->deleteByEntityId($id, 'packageCustomerId') &&
                $packageCustomerRepository->delete($id)
            ) {
                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * @param Collection $appointments
     *
     * @return void
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function setPackageBookingsForAppointments($appointments)
    {
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        $packageCustomerServiceIds = [];

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointment) {
            /** @var CustomerBooking $customerBooking */
            foreach ($appointment->getBookings()->getItems() as $customerBooking) {
                if ($customerBooking->getPackageCustomerService() &&
                    $customerBooking->getPackageCustomerService()->getId()
                ) {
                    $packageCustomerServiceIds[] =
                        $customerBooking->getPackageCustomerService()->getId()->getValue();
                }
            }
        }

        if ($packageCustomerServiceIds) {
            $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
                [
                    'ids'   => $packageCustomerServiceIds,
                ]
            );

            /** @var Appointment $appointment */
            foreach ($appointments->getItems() as $appointment) {
                /** @var CustomerBooking $customerBooking */
                foreach ($appointment->getBookings()->getItems() as $customerBooking) {
                    if ($customerBooking->getPackageCustomerService() &&
                        $customerBooking->getPackageCustomerService()->getId()
                    ) {
                        $customerBooking->setPackageCustomerService(
                            $packageCustomerServices->getItem(
                                $customerBooking->getPackageCustomerService()->getId()->getValue()
                            )
                        );

                        if ($customerBooking->getPackageCustomerService()->getPackageCustomer()->getPayment()) {
                            $customerBooking->setPayments(new Collection());

                            $customerBooking->getPayments()->addItem(
                                $customerBooking->getPackageCustomerService()->getPackageCustomer()->getPayment()
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Collection $appointments
     * @param Collection $packageCustomerServices
     * @param bool       $getAll
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function getAvailablePackageBookingsData($appointments, $packageCustomerServices, $getAll)
    {
        $availablePackageBookings = $this->getPackageUnusedBookingsCount(
            $packageCustomerServices,
            $appointments
        );

        $result = [];

        foreach ($availablePackageBookings as $customerData) {
            $packageAvailable = $getAll;

            foreach ($customerData['packages'] as $packageData) {
                foreach ($packageData['services'] as $serviceData) {
                    foreach ($serviceData['bookings'] as $bookingData) {
                        if ($bookingData['count'] > 0) {
                            $packageAvailable = true;

                            continue 3;
                        }
                    }
                }
            }

            if ($packageAvailable) {
                $result[] = $customerData;
            }
        }

        return $result;
    }

    /**
     * @param int  $packageCustomerServiceId
     * @param int  $customerId
     * @param bool $isCabinetBooking
     *
     * @return boolean
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function isBookingAvailableForPurchasedPackage($packageCustomerServiceId, $customerId, $isCabinetBooking)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            [
                'ids'        => [$packageCustomerServiceId],
                'customerId' => $customerId,
            ]
        );

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $key => $packageCustomerService) {
            /** @var Payment $payment */
            $payment = $packageCustomerService->getPackageCustomer()->getPayment();

            if ($payment &&
                $payment->getGateway()->getName()->getValue() === PaymentType::MOLLIE &&
                $payment->getStatus()->getValue() === PaymentStatus::PENDING
            ) {
                $packageCustomerServices->deleteItem($key);
            }

            if ($packageCustomerService->getPackageCustomer() &&
                $packageCustomerService->getPackageCustomer()->getId() &&
                (
                    $isCabinetBooking &&
                    $packageCustomerService->getPackageCustomer()->getStatus() &&
                    $packageCustomerService->getPackageCustomer()->getStatus()->getValue() === BookingStatus::CANCELED
                )
            ) {
                return false;
            }
        }

        /** @var Collection $appointments */
        $appointments = $appointmentRepository->getFiltered(
            [
                'customerId' => $customerId,
            ]
        );

        $availablePackageBookings = $packageCustomerServices->length() ?
            $this->getAvailablePackageBookingsData(
                $appointments,
                $packageCustomerServices,
                true
            ) : [];

        foreach ($availablePackageBookings as $customerData) {
            foreach ($customerData['packages'] as $packageData) {
                foreach ($packageData['services'] as $serviceData) {
                    foreach ($serviceData['bookings'] as $bookingData) {
                        if ($bookingData['count'] > 0) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param array $params
     *
     * @return array
     *
     * @throws QueryExecutionException
     * @throws ContainerValueNotFoundException
     * @throws Exception
     */
    public function getPackageStatsData($params)
    {
        $packageDatesData = [];

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var Collection $purchasedPackageCustomerServices */
        $purchasedPackageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            [
                'purchased' => $params['dates'],
            ]
        );

        $packageDatesDataCustomer = [];

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($purchasedPackageCustomerServices->getItems() as $packageCustomerService) {
            $dateString = $packageCustomerService->getPackageCustomer()->getPurchased()->getValue()->format('Y-m-d');

            $packageId = $packageCustomerService->getPackageCustomer()->getPackageId()->getValue();

            $packageCustomerId = $packageCustomerService->getPackageCustomer()->getId()->getValue();

            if (empty($packageDatesDataCustomer[$packageCustomerId])) {
                $packageCustomerRevenue = $packageCustomerService->getPackageCustomer()->getPayment() ?
                    $packageCustomerService->getPackageCustomer()->getPayment()->getAmount()->getValue() : 0;

                if (empty($packageDatesData[$dateString][$packageId])) {
                    $packageDatesData[$dateString][$packageId] = [
                        'count'     => 0,
                        'purchased' => 1,
                        'revenue'   => $packageCustomerRevenue,
                        'occupied'  => 0,
                    ];
                } else {
                    $packageDatesData[$dateString][$packageId]['purchased']++;

                    $packageDatesData[$dateString][$packageId]['revenue'] += $packageCustomerRevenue;
                }
            }

            $packageDatesDataCustomer[$packageCustomerId] = true;
        }

        return $packageDatesData;
    }

    /**
     * @param array      $packageDatesData
     * @param Collection $appointmentsPackageCustomerServices
     * @param int        $packageCustomerServiceId
     * @param string     $date
     * @param int        $occupiedDuration
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function updatePackageStatsData(
        &$packageDatesData,
        $appointmentsPackageCustomerServices,
        $packageCustomerServiceId,
        $date,
        $occupiedDuration
    ) {
        if ($appointmentsPackageCustomerServices->keyExists($packageCustomerServiceId)) {
            $packageCustomerService = $appointmentsPackageCustomerServices->getItem(
                $packageCustomerServiceId
            );

            $packageId = $packageCustomerService->getPackageCustomer()->getPackageId()->getValue();

            if (empty($packageDatesData[$date][$packageId])) {
                $packageDatesData[$date][$packageId] = [
                    'count'     => 1,
                    'purchased' => 0,
                    'revenue'   => 0,
                    'occupied'  => $occupiedDuration
                ];
            } else {
                $packageDatesData[$date][$packageId]['count']++;

                $packageDatesData[$date][$packageId]['occupied'] += $occupiedDuration;
            }
        }
    }

    /**
     * @param Collection $appointments
     *
     * @return Collection
     *
     * @throws QueryExecutionException
     * @throws ContainerValueNotFoundException
     * @throws Exception
     */
    public function getPackageCustomerServicesForAppointments($appointments)
    {
        $packageCustomerServiceIds = [];

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointment) {
            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                if ($booking->getPackageCustomerService()) {
                    $packageCustomerServiceIds[$booking->getPackageCustomerService()->getId()->getValue()] = true;
                }
            }
        }

        /** @var Collection $appointmentsPackageCustomerServices */
        $appointmentsPackageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            [
                'ids' => array_keys($packageCustomerServiceIds),
            ]
        );

        return $appointmentsPackageCustomerServices;
    }

    /**
     * @param Collection $appointments
     * @param array      $params
     *
     * @return array
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getPackageAvailability($appointments, $params)
    {
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            [
                'purchased'  => !empty($params['purchased']) ? $params['purchased'] : [],
                'customerId' => !empty($params['customerId']) ? $params['customerId'] : null,
                'packages'   => !empty($params['packageId']) ? [$params['packageId']] : [],
            ]
        );

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $key => $packageCustomerService) {
            /** @var Payment $payment */
            $payment = $packageCustomerService->getPackageCustomer()->getPayment();

            if ($payment &&
                $payment->getGateway()->getName()->getValue() === PaymentType::MOLLIE &&
                $payment->getStatus()->getValue() === PaymentStatus::PENDING
            ) {
                $packageCustomerServices->deleteItem($key);
            }
        }

        $params['packageCustomerServices'] = $packageCustomerServices->keys();

        /** @var Collection $packageAppointments */
        $packageAppointments = $packageCustomerServices->length() ?
            $appointmentRepository->getFiltered($params) : new Collection();

        /** @var Appointment $appointment */
        foreach ($packageAppointments->getItems() as $key => $appointment) {
            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                if ($booking->getPackageCustomerService()) {
                    /** @var PackageCustomerService $packageCustomerService */
                    $packageCustomerService = $packageCustomerServices->getItem(
                        $booking->getPackageCustomerService()->getId()->getValue()
                    );

                    $booking->getPackageCustomerService()->setPackageCustomer(
                        $packageCustomerService->getPackageCustomer()
                    );
                }
            }

            $appointments->addItem($appointment, $key);
        }

        return $packageCustomerServices->length() ?
            $this->getAvailablePackageBookingsData(
                $appointments,
                $packageCustomerServices,
                !empty($params['packageId'])
            ) : [];
    }

    /**
     * @return array
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getPackagesArray()
    {
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var Collection $packages */
        $packages = $packageRepository->getByCriteria([]);

        $currentDateTime = DateTimeService::getNowDateTimeObject();

        $packagesArray = [];

        /** @var Package $package */
        foreach ($packages->getItems() as $package) {
            if ($package->getSettings() && json_decode($package->getSettings()->getValue(), true) === null) {
                $package->setSettings(null);
            }

            $packagesArray[] = array_merge(
                $package->toArray(),
                [
                    'available' =>
                        !$package->getEndDate() ||
                        $package->getEndDate()->getValue() > $currentDateTime
                ]
            );
        }

        return $packagesArray;
    }


    /**
     * @param array $params
     *
     * @return Collection
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function getEmptyPackages($params)
    {
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        return $packageCustomerServiceRepository->getByCriteria($params, true);
    }
}
