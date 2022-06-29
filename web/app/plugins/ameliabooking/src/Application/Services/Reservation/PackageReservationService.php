<?php

namespace AmeliaBooking\Application\Services\Reservation;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Bookable\PackageApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\AbstractBookable;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Reservation;
use AmeliaBooking\Domain\Entity\CustomField\CustomField;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\String\PaymentType;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\CustomerRepository;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class PackageReservationService
 *
 * @package AmeliaBooking\Application\Services\Reservation
 */
class PackageReservationService extends AppointmentReservationService
{
    /**
     * @return string
     */
    public function getType()
    {
        return Entities::PACKAGE;
    }

    /**
     * @param array      $bookingCustomFieldsArray
     * @param Collection $customFieldsCollection
     * @param int        $serviceId
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function getCustomFieldsJsonForService(
        $bookingCustomFieldsArray,
        $customFieldsCollection,
        $serviceId
    ) {
        foreach ($bookingCustomFieldsArray as $customFieldId => $value) {
            /** @var CustomField $customField */
            $customField = $customFieldsCollection->getItem($customFieldId);

            $isCustomFieldForService = false;

            /** @var Service $customFieldService */
            foreach ($customField->getServices()->getItems() as $customFieldService) {
                if ($customFieldService->getId()->getValue() === (int)$serviceId) {
                    $isCustomFieldForService = true;
                    break;
                }
            }

            if (!$isCustomFieldForService) {
                unset($bookingCustomFieldsArray[$customFieldId]);
            }
        }

        return json_encode($bookingCustomFieldsArray);
    }

    /**
     * @param array       $appointmentData
     * @param Reservation $reservation
     * @param bool        $save
     *
     * @return void
     *
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     * @throws ContainerException
     */
    public function book($appointmentData, $reservation, $save)
    {
        /** @var PackageApplicationService $packageApplicationService */
        $packageApplicationService = $this->container->get('application.bookable.package');

        $clonedCustomFieldsData = $appointmentData['bookings'][0]['customFields'] ?
            json_decode($appointmentData['bookings'][0]['customFields'], true) : null;

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var Package $package */
        $package = $packageRepository->getById($appointmentData['packageId']);

        /** @var PackageCustomer $packageCustomer */
        $packageCustomer = $packageApplicationService->addPackageCustomer(
            $package,
            $appointmentData['bookings'][0]['customer']['id'],
            $appointmentData['utcOffset'],
            $this->getPaymentAmount(null, $package),
            $save
        );

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageApplicationService->addPackageCustomerServices(
            $package,
            $packageCustomer,
            $appointmentData['packageRules'],
            $save
        );

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
            if (!empty($appointmentData['serviceId']) &&
                (int)$appointmentData['serviceId'] === $packageCustomerService->getServiceId()->getValue()
            ) {
                $appointmentData['bookings'][0]['packageCustomerService'] = $packageCustomerService->toArray();

                break;
            }
        }

        /** @var CustomFieldRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('domain.customField.repository');

        /** @var Collection $customFieldsCollection */
        $customFieldsCollection = $customFieldRepository->getAll();

        $reservation->setCustomer(UserFactory::create($appointmentData['bookings'][0]['customer']));

        $reservation->setReservation($package);

        $reservation->setBookable($package);

        $reservation->setPackageCustomerServices($packageCustomerServices);

        /** @var Collection $packageReservations */
        $packageReservations = new Collection();

        foreach ($appointmentData['package'] as $index => $packageData) {
            $packageAppointmentData = array_merge(
                $appointmentData,
                [
                    'serviceId'          => $packageData['serviceId'],
                    'providerId'         => $packageData['providerId'],
                    'locationId'         => $packageData['locationId'],
                    'bookingStart'       => $packageData['bookingStart'],
                    'notifyParticipants' => $packageData['notifyParticipants'],
                    'parentId'           => null,
                    'recurring'          => [],
                    'package'            => [],
                    'payment'            => null,
                ]
            );

            if (!empty($packageData['utcOffset'])) {
                $packageAppointmentData['bookings'][0]['utcOffset'] = $packageData['utcOffset'];
            }

            $packageAppointmentData['bookings'][0]['customFields'] = $clonedCustomFieldsData ?
                $this->getCustomFieldsJsonForService(
                    $clonedCustomFieldsData,
                    $customFieldsCollection,
                    $packageAppointmentData['serviceId']
                ) : null;

            /** @var PackageCustomerService $packageCustomerService */
            foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
                if ((int)$packageData['serviceId'] === $packageCustomerService->getServiceId()->getValue()) {
                    $packageAppointmentData['bookings'][0]['packageCustomerService'] =
                        $packageCustomerService->toArray();

                    break;
                }
            }

            try {
                /** @var Reservation $packageReservation */
                $packageReservation = new Reservation();

                $this->bookSingle(
                    $packageReservation,
                    $packageAppointmentData,
                    $reservation->hasAvailabilityValidation()->getValue(),
                    $save
                );
            } catch (Exception $e) {
                if ($save) {
                    /** @var Reservation $packageReservation */
                    foreach ($packageReservations->getItems() as $packageReservation) {
                        $this->deleteReservation($packageReservation);
                    }

                    $this->deleteReservation($reservation);

                    $packageApplicationService->deletePackageCustomer($packageCustomerServices);
                }

                throw $e;
            }

            $packageReservations->addItem($packageReservation);
        }

        $reservation->setPackageReservations($packageReservations);
        $reservation->setRecurring(new Collection());

        $paymentAmount = $this->getPaymentAmount($reservation->getBooking(), $package);

        $applyDeposit = $appointmentData['deposit'] && $appointmentData['payment']['gateway'] !== PaymentType::ON_SITE;

        if ($applyDeposit) {
            $paymentDeposit = $this->calculateDepositAmount(
                $paymentAmount,
                $package,
                1
            );

            $appointmentData['payment']['deposit'] = $paymentAmount !== $paymentDeposit;

            $paymentAmount = $paymentDeposit;
        }

        $reservation->setApplyDeposit(new BooleanValueObject($applyDeposit));

        if ($save) {
            /** @var Payment $payment */
            $payment = $this->addPayment(
                null,
                $packageCustomer->getId()->getValue(),
                $appointmentData['payment'],
                $paymentAmount,
                DateTimeService::getNowDateTimeObject(),
                Entities::PACKAGE
            );

            /** @var PackageCustomerService $packageCustomerService */
            foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
                $packageCustomerService->getPackageCustomer()->setPayment($payment);
            }

            $packageCustomer->setPayment($payment);
        }
    }

    /**
     * @param array $data
     *
     * @return AbstractBookable
     *
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws NotFoundException
     */
    public function getBookableEntity($data)
    {
        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');

        return $bookableAS->getAppointmentService($data['serviceId'], $data['providerId']);
    }

    /**
     * @param Service $bookable
     *
     * @return boolean
     */
    public function isAggregatedPrice($bookable)
    {
        return true;
    }

    /**
     * @param Reservation $reservation
     * @param string      $paymentGateway
     * @param array       $requestData
     *
     * @return array
     *
     */
    public function getWooCommerceData($reservation, $paymentGateway, $requestData)
    {
        /** @var Package $package */
        $package = $reservation->getBookable();

        /** @var AbstractUser $customer */
        $customer = $reservation->getCustomer();

        $packageAppointmentsData = [];

        $customFields = null;

        /** @var Reservation $packageReservation */
        foreach ($reservation->getPackageReservations()->getItems() as $key => $packageReservation) {
            $packageAppointmentData = [
                'serviceId'          => $packageReservation->getReservation()->getServiceId()->getValue(),
                'providerId'         => $packageReservation->getReservation()->getProviderId()->getValue(),
                'locationId'         => $packageReservation->getReservation()->getLocationId() ?
                    $packageReservation->getReservation()->getLocationId()->getValue() : null,
                'bookingStart'       =>
                    $packageReservation->getReservation()->getBookingStart()->getValue()->format('Y-m-d H:i:s'),
                'bookingEnd'         =>
                    $packageReservation->getReservation()->getBookingEnd()->getValue()->format('Y-m-d H:i:s'),
                'notifyParticipants' => $packageReservation->getReservation()->isNotifyParticipants(),
                'status'             => $packageReservation->getReservation()->getStatus()->getValue(),
                'utcOffset'          => $packageReservation->getBooking()->getUtcOffset() ?
                    $packageReservation->getBooking()->getUtcOffset()->getValue() : null,
            ];

            $packageAppointmentsData[] = $packageAppointmentData;

            $customFields = $packageReservation->getBooking()->getCustomFields();
        }

        return [
            'type'               => Entities::PACKAGE,
            'utcOffset'          => $requestData['utcOffset'],
            'packageRules'       => $requestData['packageRules'],
            'packageId'          => $package->getId()->getValue(),
            'name'               => $package->getName()->getValue(),
            'couponId'           => '',
            'couponCode'         => '',
            'dateTimeValues'     => [],
            'bookings'           => [
                [
                    'customerId'   => $customer->getId() ? $customer->getId()->getValue() : null,
                    'customer'     => [
                        'email'           => $customer->getEmail()->getValue(),
                        'externalId'      => $customer->getExternalId() ? $customer->getExternalId()->getValue() : null,
                        'firstName'       => $customer->getFirstName()->getValue(),
                        'id'              => $customer->getId() ? $customer->getId()->getValue() : null,
                        'lastName'        => $customer->getLastName()->getValue(),
                        'phone'           => $customer->getPhone()->getValue(),
                        'countryPhoneIso' => $customer->getCountryPhoneIso() ?
                            $customer->getCountryPhoneIso()->getValue() : null,
                    ],
                    'persons'      => 1,
                    'extras'       => [],
                    'status'       => null,
                    'utcOffset'    => null,
                    'customFields' => $customFields ? json_decode($customFields->getValue(), true) : null,
                ]
            ],
            'payment'            => [
                'gateway' => $paymentGateway
            ],
            'locale'             => $reservation->getLocale()->getValue(),
            'timeZone'           => $reservation->getTimeZone()->getValue(),
            'recurring'          => [],
            'package'            => $packageAppointmentsData,
            'deposit'            => $reservation->getApplyDeposit()->getValue(),
            'customer'           => array_merge(
                [
                    'locale'     => $reservation->getLocale()->getValue(),
                ],
                $reservation->getCustomer()->toArray()
            )
        ];
    }

    /**
     * @param Reservation  $reservation
     *
     * @return float
     */
    public function getReservationPaymentAmount($reservation)
    {
        /** @var Package $bookable */
        $bookable = $reservation->getBookable();

        $paymentAmount = $this->getPaymentAmount($reservation->getBooking(), $bookable);

        if ($reservation->getApplyDeposit()->getValue()) {
            $paymentAmount = $this->calculateDepositAmount(
                $paymentAmount,
                $bookable,
                1
            );
        }

        return $paymentAmount;
    }

    /**
     * @param CustomerBooking $booking
     * @param Package         $bookable
     *
     * @return float
     */
    public function getPaymentAmount($booking, $bookable)
    {
        $price = $bookable->getPrice()->getValue();

        if (!$bookable->getCalculatedPrice()->getValue() && $bookable->getDiscount()->getValue()) {
            $subtraction = $price / 100 * ($bookable->getDiscount()->getValue() ?: 0);

            return (float)round($bookable->getPrice()->getValue() - $subtraction, 2);
        }

        return (float)$bookable->getPrice()->getValue();
    }

    /**
     * @param Payment $payment
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getReservationByPayment($payment)
    {
        $result = new CommandResult();

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->container->get('domain.users.customers.repository');

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            ['packagesCustomers' => [$payment->getPackageCustomerId()->getValue()]]
        );

        $packageId = null;

        $customerId = null;

        $packageCustomerId = null;

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
            $packageId = $packageCustomerService->getPackageCustomer()->getPackageId()->getValue();

            $customerId = $packageCustomerService->getPackageCustomer()->getCustomerId()->getValue();

            $packageCustomerId = $packageCustomerService->getPackageCustomer()->getId()->getValue();

            break;
        }

        /** @var Package $package */
        $package = $packageId ? $packageRepository->getById($packageId) : null;

        $packageData = [];

        /** @var Collection $appointments */
        $appointments = $appointmentRepository->getFiltered(
            ['packageCustomerServices' => $packageCustomerServices->keys()]
        );

        $firstBooking = null;

        $firstAppointment = null;

        $firstService = null;

        /** @var Appointment $packageAppointment */
        foreach ($appointments->getItems() as $packageAppointment) {
            if ($packageAppointment->getLocationId()) {
                /** @var Location $location */
                $location = $locationRepository->getById($packageAppointment->getLocationId()->getValue());

                $packageAppointment->setLocation($location);
            }

            /** @var CustomerBooking $packageBooking */
            foreach ($packageAppointment->getBookings()->getItems() as $packageBooking) {
                if ($packageBooking->getPackageCustomerService() &&
                    in_array(
                        $packageBooking->getPackageCustomerService()->getId()->getValue(),
                        $packageCustomerServices->keys()
                    )
                ) {
                    /** @var Service $packageService */
                    $packageService = $bookableAS->getAppointmentService(
                        $packageAppointment->getServiceId()->getValue(),
                        $packageAppointment->getProviderId()->getValue()
                    );

                    if ($firstBooking === null) {
                        $firstBooking = $packageBooking;

                        $this->setToken($firstBooking);

                        $firstAppointment = $packageAppointment;

                        $firstService = $packageService;

                        continue;
                    }

                    $packageData[] = [
                        'type'                     => Entities::APPOINTMENT,
                        Entities::APPOINTMENT      => $packageAppointment->toArray(),
                        Entities::BOOKING          => $packageBooking->toArray(),
                        'appointmentStatusChanged' => true,
                        'utcTime'                  => $this->getBookingPeriods(
                            $packageAppointment,
                            $packageBooking,
                            $packageService
                        ),
                        'isRetry'                  => true,
                    ];
                }
            }
        }

        /** @var AbstractUser $customer */
        $customer = $customerRepository->getById($customerId);

        $customerCabinetUrl = '';

        if ($customer->getEmail() && $customer->getEmail()->getValue()) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            $locale = '';

            if ($firstBooking && $firstBooking->getInfo() && $firstBooking->getInfo()->getValue()) {
                $info = json_decode($firstBooking->getInfo()->getValue(), true);

                $locale = !empty($info['locale']) ? $info['locale'] : '';
            }

            $customerCabinetUrl = $helperService->getCustomerCabinetUrl(
                $customer->getEmail()->getValue(),
                'email',
                null,
                null,
                $locale
            );
        }

        $result->setData(
            [
                'type'                     => Entities::APPOINTMENT,
                Entities::APPOINTMENT      => $firstAppointment ? $firstAppointment->toArray() : null,
                Entities::BOOKING          => $firstBooking ? $firstBooking->toArray() : null,
                'customer'                 => $customer->toArray(),
                'packageId'                => $packageId,
                'recurring'                => $packageData,
                'appointmentStatusChanged' => false,
                'utcTime'                  => $firstAppointment && $firstBooking ? $this->getBookingPeriods(
                    $firstAppointment,
                    $firstBooking,
                    $firstService
                ) : [],
                'bookable'                 => $package ? $package->toArray() : null,
                'isRetry'                  => true,
                'paymentId'                => $payment->getId()->getValue(),
                'packageCustomerId'        => $packageCustomerId,
                'payment'                  => [
                    'id'           => $payment->getId()->getValue(),
                    'amount'       => $payment->getAmount()->getValue(),
                    'gateway'      => $payment->getGateway()->getName()->getValue(),
                    'gatewayTitle' => $payment->getGatewayTitle() ? $payment->getGatewayTitle()->getValue() : '',
                ],
                'customerCabinetUrl'       => $customerCabinetUrl,
            ]
        );

        return $result;
    }
}
