<?php

namespace AmeliaBooking\Application\Commands\PaymentGateway;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Bookable\PackageApplicationService;
use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\EventApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Cache\Cache;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentStatus;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Cache\CacheRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\CustomerRepository;
use AmeliaBooking\Infrastructure\Services\Payment\MollieService;
use Exception;
use Interop\Container\Exception\ContainerException;

/**
 * Class MolliePaymentNotifyCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\PaymentGateway
 */
class MolliePaymentNotifyCommandHandler extends CommandHandler
{
    public $mandatoryFields = [
        'name',
    ];

    /**
     * @param MolliePaymentNotifyCommand $command
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws ContainerException
     */
    public function handle(MolliePaymentNotifyCommand $command)
    {
        $result = new CommandResult();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('');
        $result->setData([]);

        $this->checkMandatoryFields($command);

        /** @var CacheRepository $cacheRepository */
        $cacheRepository = $this->container->get('domain.cache.repository');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->container->get('domain.users.customers.repository');

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var AppointmentApplicationService $appointmentAS */
        $appointmentAS = $this->container->get('application.booking.appointment.service');

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        /** @var BookableApplicationService $bookableAS */
        $bookableAS = $this->container->get('application.bookable.service');

        /** @var EventApplicationService $eventApplicationService */
        $eventApplicationService = $this->container->get('application.booking.event.service');


        /** @var Cache $cache */
        $cache = ($data = explode('_', $command->getField('name'))) && isset($data[0], $data[1]) ?
            $cacheRepository->getByIdAndName($data[0], $data[1]) : null;

        if (!$cache || !$cache->getPaymentId()) {
            return $result;
        }

        $cacheData = json_decode($cache->getData()->getValue(), true);


        /** @var Payment $payment */
        $payment = $paymentRepository->getById($cache->getPaymentId()->getValue());

        /** @var MollieService $paymentService */
        $paymentService = $this->container->get('infrastructure.payment.mollie.service');

        $response = $paymentService->fetchPayment(
            ['id' => $command->getField('id')]
        );

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully get booking');
        $result->setDataInResponse(false);

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($data[2]);

        $cacheRepository->beginTransaction();

        $status = $response->getStatus();

        if ($cacheData['status'] === null && $status === 'paid') {
            switch ($data[2]) {
                case (Entities::APPOINTMENT):
                    $recurringData = [];

                    /** @var Appointment $appointment */
                    $appointment = $appointmentRepository->getByPaymentId($payment->getId()->getValue());

                    if ($appointment->getLocationId()) {
                        /** @var Location $location */
                        $location = $locationRepository->getById($appointment->getLocationId()->getValue());

                        $appointment->setLocation($location);
                    }

                    /** @var CustomerBooking $booking */
                    $booking = $appointment->getBookings()->getItem($payment->getCustomerBookingId()->getValue());

                    $token = $bookingRepository->getToken($booking->getId()->getValue());

                    if (!empty($token['token'])) {
                        $booking->setToken(new Token($token['token']));
                    }

                    /** @var AbstractUser $customer */
                    $customer = $customerRepository->getById($booking->getCustomerId()->getValue());

                    /** @var Collection $nextPayments */
                    $nextPayments = $paymentRepository->getByEntityId($payment->getId()->getValue(), 'parentId');

                    /** @var Payment $nextPayment */
                    foreach ($nextPayments->getItems() as $nextPayment) {
                        /** @var Appointment $nextAppointment */
                        $nextAppointment = $appointmentRepository->getByPaymentId($nextPayment->getId()->getValue());

                        if ($nextAppointment->getLocationId()) {
                            /** @var Location $location */
                            $location = $locationRepository->getById($nextAppointment->getLocationId()->getValue());

                            $nextAppointment->setLocation($location);
                        }

                        /** @var CustomerBooking $nextBooking */
                        $nextBooking = $nextAppointment->getBookings()->getItem(
                            $nextPayment->getCustomerBookingId()->getValue()
                        );

                        /** @var Service $nextService */
                        $nextService = $bookableAS->getAppointmentService(
                            $nextAppointment->getServiceId()->getValue(),
                            $nextAppointment->getProviderId()->getValue()
                        );

                        $nextAppointmentStatusChanged = $appointmentAS->isAppointmentStatusChangedWithBooking(
                            $nextService,
                            $nextAppointment,
                            $nextPayment,
                            $nextBooking
                        );

                        $recurringData[] = [
                            'type'                     => Entities::APPOINTMENT,
                            Entities::APPOINTMENT      => $nextAppointment->toArray(),
                            Entities::BOOKING          => $nextBooking->toArray(),
                            'appointmentStatusChanged' => $nextAppointmentStatusChanged,
                            'utcTime'                  => $reservationService->getBookingPeriods(
                                $nextAppointment,
                                $nextBooking,
                                $nextService
                            ),
                        ];
                    }

                    /** @var Service $service */
                    $service = $bookableAS->getAppointmentService(
                        $appointment->getServiceId()->getValue(),
                        $appointment->getProviderId()->getValue()
                    );

                    $appointmentStatusChanged = $appointmentAS->isAppointmentStatusChangedWithBooking(
                        $service,
                        $appointment,
                        $payment,
                        $booking
                    );

                    $customerCabinetUrl = '';

                    if ($customer &&
                        $customer->getEmail() &&
                        $customer->getEmail()->getValue() &&
                        $booking->getInfo() &&
                        $booking->getInfo()->getValue()
                    ) {
                        $infoJson = json_decode($booking->getInfo()->getValue(), true);

                        /** @var HelperService $helperService */
                        $helperService = $this->container->get('application.helper.service');

                        $customerCabinetUrl = $helperService->getCustomerCabinetUrl(
                            $customer->getEmail()->getValue(),
                            'email',
                            $appointment->getBookingStart()->getValue()->format('Y-m-d'),
                            $appointment->getBookingEnd()->getValue()->format('Y-m-d'),
                            $infoJson['locale']
                        );
                    }

                    $result->setData(
                        [
                            'type'                     => Entities::APPOINTMENT,
                            Entities::APPOINTMENT      => $appointment->toArray(),
                            Entities::BOOKING          => $booking->toArray(),
                            'customer'                 => $customer->toArray(),
                            'packageId'                => 0,
                            'recurring'                => $recurringData,
                            'appointmentStatusChanged' => $appointmentStatusChanged,
                            'bookable'                 => $service->toArray(),
                            'utcTime'                  => $reservationService->getBookingPeriods(
                                $appointment,
                                $booking,
                                $service
                            ),
                            'paymentId'                => $payment->getId()->getValue(),
                            'packageCustomerId'        => 0,
                            'payment'                  => $payment ? $payment->toArray() : null,
                            'customerCabinetUrl'       => $customerCabinetUrl,
                        ]
                    );

                    break;

                case (Entities::EVENT):
                    /** @var Event $event */
                    $event = $reservationService->getReservationByBookingId(
                        $payment->getCustomerBookingId()->getValue()
                    );

                    if ($event->getLocationId()) {
                        /** @var Location $location */
                        $location = $locationRepository->getById($event->getLocationId()->getValue());

                        $event->setLocation($location);
                    }

                    /** @var CustomerBooking $booking */
                    $booking = $event->getBookings()->getItem($payment->getCustomerBookingId()->getValue());

                    $token = $bookingRepository->getToken($booking->getId()->getValue());

                    if (!empty($token['token'])) {
                        $booking->setToken(new Token($token['token']));
                    }

                    if ($booking->getStatus()->getValue() === BookingStatus::PENDING) {
                        $booking->setChangedStatus(new BooleanValueObject(true));
                        $booking->setStatus(new BookingStatus(BookingStatus::APPROVED));

                        $bookingRepository->updateFieldById(
                            $booking->getId()->getValue(),
                            BookingStatus::APPROVED,
                            'status'
                        );
                    }

                    /** @var AbstractUser $customer */
                    $customer = $customerRepository->getById($booking->getCustomerId()->getValue());


                    $paymentRepository->updateFieldById(
                        $payment->getId()->getValue(),
                        $reservationService->getPaymentAmount($booking, $event) > $payment->getAmount()->getValue() ?
                            PaymentStatus::PARTIALLY_PAID : PaymentStatus::PAID,
                        'status'
                    );


                    $result->setData(
                        [
                            'type'                     => Entities::EVENT,
                            Entities::EVENT            => $event->toArray(),
                            Entities::BOOKING          => $booking->toArray(),
                            'appointmentStatusChanged' => false,
                            'customer'                 => $customer->toArray(),
                            'packageId'                => 0,
                            'recurring'                => [],
                            'utcTime'                  => $reservationService->getBookingPeriods(
                                $event,
                                $booking,
                                $event
                            ),
                            'paymentId'                => $payment->getId()->getValue(),
                            'packageCustomerId'        => 0,
                            'payment'                  => $payment ? $payment->toArray() : null,
                        ]
                    );

                    break;

                case (Entities::PACKAGE):
                    /** @var Collection $packageCustomerServices */
                    $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
                        ['packagesCustomers' => [$payment->getPackageCustomerId()->getValue()]]
                    );

                    $packageId = null;

                    $customerId = null;

                    /** @var PackageCustomerService $packageCustomerService */
                    foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
                        $paymentRepository->updateFieldById(
                            $payment->getId()->getValue(),
                            $packageCustomerService->getPackageCustomer()->getPrice()->getValue() >
                            $payment->getAmount()->getValue() ? PaymentStatus::PARTIALLY_PAID : PaymentStatus::PAID,
                            'status'
                        );

                        $packageId = $packageCustomerService->getPackageCustomer()->getPackageId()->getValue();

                        $customerId = $packageCustomerService->getPackageCustomer()->getCustomerId()->getValue();

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

                                $appointmentStatusChanged = $appointmentAS->isAppointmentStatusChangedWithBooking(
                                    $packageService,
                                    $packageAppointment,
                                    null,
                                    $packageBooking
                                );

                                if ($firstBooking === null) {
                                    $firstBooking = $packageBooking;
                                }

                                $packageData[] = [
                                    'type'                     => Entities::APPOINTMENT,
                                    Entities::APPOINTMENT      => $packageAppointment->toArray(),
                                    Entities::BOOKING          => $packageBooking->toArray(),
                                    'appointmentStatusChanged' => $appointmentStatusChanged,
                                    'utcTime'                  => $reservationService->getBookingPeriods(
                                        $packageAppointment,
                                        $packageBooking,
                                        $packageService
                                    ),
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
                            'type'                     => Entities::PACKAGE,
                            'customer'                 => $customer->toArray(),
                            'packageId'                => $packageId,
                            'recurring'                => [],
                            'package'                  => $packageData,
                            'appointmentStatusChanged' => false,
                            'utcTime'                  => [],
                            'bookable'                 => $package ? $package->toArray() : null,
                            'paymentId'                => $payment->getId()->getValue(),
                            'packageCustomerId'        => $payment->getPackageCustomerId() ?
                                $payment->getPackageCustomerId()->getValue() : null,
                            'payment'                  => $payment ? $payment->toArray() : null,
                            'customerCabinetUrl'       => $customerCabinetUrl,
                        ]
                    );

                    break;
            }

            $cache->setData(
                new Json(
                    json_encode(
                        array_merge(
                            json_decode($cache->getData()->getValue(), true),
                            [
                                'response' => $result->getData(),
                                'status'   => $status,
                            ]
                        )
                    )
                )
            );

            $cacheRepository->update($cache->getId()->getValue(), $cache);
        } elseif ($cacheData['status'] === null &&
            ($status === 'canceled' || $status === 'failed' || $status === 'expired')
        ) {
            switch ($data[2]) {
                case (Entities::APPOINTMENT):
                    /** @var Appointment $appointment */
                    $appointment = $appointmentRepository->getByPaymentId($payment->getId()->getValue());

                    /** @var Collection $nextPayments */
                    $nextPayments = $paymentRepository->getByEntityId($payment->getId()->getValue(), 'parentId');

                    /** @var Payment $nextPayment */
                    foreach ($nextPayments->getItems() as $nextPayment) {
                        /** @var Appointment $nextAppointment */
                        $nextAppointment = $appointmentRepository->getByPaymentId($nextPayment->getId()->getValue());

                        /** @var CustomerBooking $nextBooking */
                        $nextBooking = $nextAppointment->getBookings()->getItem(
                            $nextPayment->getCustomerBookingId()->getValue()
                        );

                        switch ($status) {
                            case ('expired'):
                                $nextBooking->setStatus(new BookingStatus(BookingStatus::CANCELED));

                                $bookingRepository->updateFieldById(
                                    $nextBooking->getId()->getValue(),
                                    BookingStatus::CANCELED,
                                    'status'
                                );

                                if ($nextAppointment->getBookings()->length() === 1) {
                                    $nextAppointment->setStatus(new BookingStatus(BookingStatus::CANCELED));

                                    $appointmentRepository->updateFieldById(
                                        $nextAppointment->getId()->getValue(),
                                        BookingStatus::CANCELED,
                                        'status'
                                    );
                                }

                                break;

                            case ('failed'):
                            case ('canceled'):
                                if ($nextAppointment->getBookings()->length() === 1) {
                                    $appointmentAS->delete($nextAppointment);
                                } else {
                                    $bookingAS->delete($nextBooking);
                                }

                                break;
                        }
                    }

                    /** @var CustomerBooking $booking */
                    $booking = $appointment->getBookings()->getItem($payment->getCustomerBookingId()->getValue());

                    switch ($status) {
                        case ('expired'):
                            $booking->setStatus(new BookingStatus(BookingStatus::CANCELED));

                            $bookingRepository->updateFieldById(
                                $booking->getId()->getValue(),
                                BookingStatus::CANCELED,
                                'status'
                            );

                            if ($appointment->getBookings()->length() === 1) {
                                $appointment->setStatus(new BookingStatus(BookingStatus::CANCELED));

                                $appointmentRepository->updateFieldById(
                                    $appointment->getId()->getValue(),
                                    BookingStatus::CANCELED,
                                    'status'
                                );
                            }

                            break;

                        case ('failed'):
                        case ('canceled'):
                            if ($appointment->getBookings()->length() === 1) {
                                $appointmentAS->delete($appointment);
                            } else {
                                $bookingAS->delete($booking);
                            }

                            break;
                    }

                    break;

                case (Entities::EVENT):
                    /** @var Event $event */
                    $event = $reservationService->getReservationByBookingId(
                        $payment->getCustomerBookingId()->getValue()
                    );

                    /** @var CustomerBooking $booking */
                    $booking = $event->getBookings()->getItem($payment->getCustomerBookingId()->getValue());

                    switch ($status) {
                        case ('expired'):
                            $booking->setStatus(new BookingStatus(BookingStatus::CANCELED));

                            $bookingRepository->updateFieldById(
                                $booking->getId()->getValue(),
                                BookingStatus::CANCELED,
                                'status'
                            );

                            break;

                        case ('failed'):
                        case ('canceled'):
                            $eventApplicationService->deleteEventBooking($booking);

                            break;
                    }



                    break;

                case (Entities::PACKAGE):
                    /** @var Collection $packageCustomerServices */
                    $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
                        ['packagesCustomers' => [$payment->getPackageCustomerId()->getValue()]]
                    );

                    /** @var Collection $appointments */
                    $appointments = $appointmentRepository->getFiltered(
                        ['packageCustomerServices' => $packageCustomerServices->keys()]
                    );

                    /** @var PackageApplicationService $packageApplicationService */
                    $packageApplicationService = $this->container->get('application.bookable.package');

                    /** @var Appointment $appointment */
                    foreach ($appointments->getItems() as $appointment) {
                        /** @var Appointment $packageAppointment */
                        $packageAppointment = $appointmentRepository->getById($appointment->getId()->getValue());

                        /** @var CustomerBooking $packageBooking */
                        $packageBooking = null;

                        /** @var CustomerBooking $appointmentBooking */
                        foreach ($packageAppointment->getBookings()->getItems() as $appointmentBooking) {
                            $packageBooking = $appointmentBooking->getPackageCustomerService() &&
                                in_array(
                                    $appointmentBooking->getPackageCustomerService()->getId()->getValue(),
                                    $packageCustomerServices->keys()
                                ) ? $appointmentBooking : null;
                        }

                        switch ($status) {
                            case ('expired'):
                                $packageBooking->setStatus(new BookingStatus(BookingStatus::CANCELED));

                                $bookingRepository->updateFieldById(
                                    $packageBooking->getId()->getValue(),
                                    BookingStatus::CANCELED,
                                    'status'
                                );

                                if ($packageAppointment->getBookings()->length() === 1) {
                                    $packageAppointment->setStatus(new BookingStatus(BookingStatus::CANCELED));

                                    $appointmentRepository->updateFieldById(
                                        $packageAppointment->getId()->getValue(),
                                        BookingStatus::CANCELED,
                                        'status'
                                    );
                                }

                                break;

                            case ('failed'):
                            case ('canceled'):
                                if ($packageAppointment->getBookings()->length() === 1) {
                                    $appointmentAS->delete($packageAppointment);
                                } elseif ($packageBooking) {
                                    $bookingAS->delete($packageBooking);
                                }

                                break;
                        }
                    }

                    switch ($status) {
                        case ('expired'):
                            break;

                        case ('failed'):
                        case ('canceled'):
                            $packageApplicationService->deletePackageCustomer($packageCustomerServices);

                            break;
                    }

                    break;
            }

            switch ($status) {
                case ('expired'):
                    $cacheRepository->delete($cache->getId()->getValue());

                    break;

                case ('failed'):
                case ('canceled'):
                    $cache->setData(
                        new Json(
                            json_encode(
                                array_merge(
                                    json_decode($cache->getData()->getValue(), true),
                                    [
                                        'status' => $status,
                                    ]
                                )
                            )
                        )
                    );

                    $cache->setPaymentId(null);

                    $cacheRepository->update($cache->getId()->getValue(), $cache);

                    break;
            }
        }

        $cacheRepository->commit();

        return $result;
    }
}
