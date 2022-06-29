<?php

namespace AmeliaBooking\Application\Services\Booking;

use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Factory\Booking\Appointment\CustomerBookingFactory;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class BookingApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class BookingApplicationService
{
    private $container;

    /**
     * AppointmentApplicationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param CustomerBooking $booking
     *
     * @return boolean
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function delete($booking)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var CustomerBookingExtraRepository $customerBookingExtraRepository */
        $customerBookingExtraRepository = $this->container->get('domain.booking.customerBookingExtra.repository');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');

        /** @var Collection $payments */
        $payments = $paymentRepository->getByEntityId($booking->getId()->getValue(), 'customerBookingId');

        /** @var Payment $payment */
        foreach ($payments->getItems() as $payment) {
            if (!$paymentAS->delete($payment)) {
                return false;
            }
        }

        if (!$customerBookingExtraRepository->deleteByEntityId($booking->getId()->getValue(), 'customerBookingId') ||
            !$bookingRepository->delete($booking->getId()->getValue())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param array $appointment
     * @param array $oldAppointment
     *
     * @return array
     */
    public function getBookingsWithChangedStatus(&$appointment, $oldAppointment)
    {
        $bookings = [];

        foreach ((array)$appointment['bookings'] as $key => $booking) {
            $oldBookingKey = array_search($booking['id'], array_column($oldAppointment['bookings'], 'id'), true);

            $changedStatus = $booking['status'] !== $oldAppointment['bookings'][$oldBookingKey]['status'];

            $oldCanceledOrRejected = $this->isBookingCanceledOrRejected(
                $oldAppointment['bookings'][$oldBookingKey]['status']
            );

            $newCanceledOrRejected = $this->isBookingCanceledOrRejected(
                $appointment['bookings'][$key]['status']
            );

            if (empty($appointment['bookings'][$key]['isChangedStatus'])) {
                $appointment['bookings'][$key]['isChangedStatus'] = false;
            }

            if ($oldBookingKey === false || ($changedStatus && !($oldCanceledOrRejected && $newCanceledOrRejected))) {
                $appointment['bookings'][$key]['isChangedStatus'] = true;
                $booking['isChangedStatus'] = true;
                $bookings[] = $booking;
            }
        }

        foreach ((array)$oldAppointment['bookings'] as $oldBooking) {
            $newBookingKey = array_search($oldBooking['id'], array_column($appointment['bookings'], 'id'), true);

            if (($newBookingKey === false) && $this->isBookingApprovedOrPending($oldBooking['status'])) {
                $oldBooking['status'] = BookingStatus::REJECTED;

                $oldBooking['isChangedStatus'] = true;

                $bookings[] = $oldBooking;
            }
        }

        return $bookings;
    }

    /**
     * @param string $bookingStatus
     *
     * @return boolean
     */
    public function isBookingApprovedOrPending($bookingStatus)
    {
        return $bookingStatus === BookingStatus::APPROVED || $bookingStatus === BookingStatus::PENDING;
    }

    /**
     * @param string $bookingStatus
     *
     * @return boolean
     */
    public function isBookingCanceledOrRejected($bookingStatus)
    {
        return $bookingStatus === BookingStatus::CANCELED || $bookingStatus === BookingStatus::REJECTED;
    }

    /**
     * @param $bookingsArray
     *
     * @return array
     */
    public function filterApprovedBookings($bookingsArray)
    {
        return array_intersect_key(
            $bookingsArray,
            array_flip(array_keys(array_column($bookingsArray, 'status'), 'approved'))
        );
    }

    /**
     * @param array $bookingsArray
     * @param array $statuses
     *
     * @return mixed
     */
    public function removeBookingsByStatuses($bookingsArray, $statuses)
    {
        foreach ($statuses as $status) {
            foreach ($bookingsArray as $bookingKey => $bookingArray) {
                if ($bookingArray['status'] === $status) {
                    unset($bookingsArray[$bookingKey]);
                }
            }
        }

        return $bookingsArray;
    }

    /**
     * @param array $data
     *
     * @return array|null
     * @throws \Exception
     */
    public function getAppointmentData($data)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        if (isset($data['utcOffset']) && $data['utcOffset'] === '') {
            $data['utcOffset'] = null;
        }

        if (isset($data['bookings'][0]['customFields']) && !$data['bookings'][0]['customFields']) {
            $data['bookings'][0]['customFields'] = null;
        }

        if (isset($data['bookings'][0]['utcOffset']) && $data['bookings'][0]['utcOffset'] === '') {
            $data['bookings'][0]['utcOffset'] = null;
        }

        if (isset($data['timeZone']) && $data['timeZone'] === '') {
            $data['timeZone'] = null;
        }

        if (isset($data['utc']) && $data['utc'] === '') {
            $data['utc'] = null;
        }

        if (!empty($data['bookings'][0]['customer']['firstName'])) {
            $data['bookings'][0]['customer']['firstName'] =
                sanitize_text_field($data['bookings'][0]['customer']['firstName']);
        }

        if (!empty($data['bookings'][0]['customer']['lastName'])) {
            $data['bookings'][0]['customer']['lastName'] =
                sanitize_text_field($data['bookings'][0]['customer']['lastName']);
        }

        if (!empty($data['bookings'][0]['customer']['email'])) {
            $data['bookings'][0]['customer']['email'] =
                sanitize_email($data['bookings'][0]['customer']['email']);
        }

        if (!empty($data['bookings'][0]['customer']['phone'])) {
            $data['bookings'][0]['customer']['phone'] =
                sanitize_text_field($data['bookings'][0]['customer']['phone']);
        }

        if (!empty($data['bookings'][0]['customer']['countryPhoneIso'])) {
            $data['bookings'][0]['customer']['countryPhoneIso'] =
                sanitize_text_field($data['bookings'][0]['customer']['countryPhoneIso']);
        }

        if (!empty($data['bookings'][0]['customer']['pictureThumbPath'])) {
            $data['bookings'][0]['customer']['pictureThumbPath'] =
                sanitize_url($data['bookings'][0]['customer']['pictureThumbPath']);
        }

        if (!empty($data['bookings'][0]['customer']['pictureFullPath'])) {
            $data['bookings'][0]['customer']['pictureFullPath'] =
                sanitize_url($data['bookings'][0]['customer']['pictureFullPath']);
        }

        if (!empty($data['bookings'][0]['customer']['note'])) {
            $data['bookings'][0]['customer']['note'] =
                sanitize_text_field($data['bookings'][0]['customer']['note']);
        }

        if (!empty($data['bookings'][0]['customFields'])) {
            $customFields = $data['bookings'][0]['customFields'];

            foreach ($customFields as $customFieldId => $customField) {
                foreach ($customField as $key => $value) {
                    if (!in_array($key, ['type', 'value', 'label'])) {
                        unset($customFields[$customFieldId][$key]);

                        continue 2;
                    }
                }

                if (in_array(
                    $customField['type'],
                    ['text-area', 'file', 'text', 'select', 'checkbox', 'radio', 'datepicker']
                )) {
                    if ($customField['type'] === 'file') {
                        if (isset($customField['value'])) {
                            foreach ($customFields[$customFieldId]['value'] as $index => $value) {
                                $customFields[$customFieldId]['value'][$index] = [
                                    'name' => sanitize_text_field($value['name'])
                                ];
                            }
                        }
                    } elseif ($customField['type'] === 'checkbox') {
                        if (isset($customField['value'])) {
                            foreach ($customFields[$customFieldId]['value'] as $index => $value) {
                                $customFields[$customFieldId]['value'][$index] = sanitize_text_field($value);
                            }
                        }
                    } else {
                        $customFields[$customFieldId]['value'] =
                            sanitize_text_field($customFields[$customFieldId]['value']);
                    }
                } else {
                    unset($customFields[$customFieldId]);
                }
            }

            $data['bookings'][0]['customFields'] = $customFields;
        }

        if (isset($data['bookings'][0]['customer']['id']) && $data['bookings'][0]['customer']['id'] === '') {
            $data['bookings'][0]['customer']['id'] = null;
        }

        if (isset($data['bookings'][0]['customer']['phone']) && $data['bookings'][0]['customer']['phone'] === '') {
            $data['bookings'][0]['customer']['phone'] = null;
        }

        if (isset($data['bookings'][0]['customerId']) && $data['bookings'][0]['customerId'] === '') {
            $data['bookings'][0]['customerId'] = null;
        }

        if (isset($data['bookings'][0]['couponCode']) && $data['bookings'][0]['couponCode'] === '') {
            $data['bookings'][0]['couponCode'] = null;
        }

        if (isset($data['locationId']) && $data['locationId'] === '') {
            $data['locationId'] = null;
        }

        if (isset($data['recaptcha']) && $data['recaptcha'] === '') {
            $data['recaptcha'] = null;
        }

        if (isset($data['recurring'])) {
            foreach ($data['recurring'] as $key => $recurringData) {
                if (isset($data['recurring'][$key]['locationId']) &&
                    $data['recurring'][$key]['locationId'] === ''
                ) {
                    $data['recurring'][$key]['locationId'] = null;
                }
            }
        }

        if (isset($data['package'])) {
            foreach ($data['package'] as $key => $recurringData) {
                if (isset($data['package'][$key]['locationId']) &&
                    $data['package'][$key]['locationId'] === ''
                ) {
                    $data['package'][$key]['locationId'] = null;
                }
            }
        }

        // Convert UTC slot to slot in TimeZone based on Settings
        if ((isset($data['bookingStart']) &&
            $data['bookings'][0]['utcOffset'] !== null &&
            $settingsService->getSetting('general', 'showClientTimeZone')) ||
            (isset($data['utc']) ? (isset($data['bookingStart']) && $data['utc'] === true) : false)
        ) {
            $data['bookingStart'] = DateTimeService::getCustomDateTimeFromUtc(
                $data['bookingStart']
            );

            if (isset($data['recurring'])) {
                foreach ($data['recurring'] as $key => $recurringData) {
                    $data['recurring'][$key]['bookingStart'] = DateTimeService::getCustomDateTimeFromUtc(
                        $recurringData['bookingStart']
                    );
                }
            }
        } elseif (isset($data['utc']) && $data['utc'] === false && !empty($data['timeZone'])) {
            $data['bookingStart'] = DateTimeService::getDateTimeObjectInTimeZone(
                $data['bookingStart'],
                $data['timeZone']
            )->setTimezone(DateTimeService::getTimeZone())->format('Y-m-d H:i:s');
        }

        if ($settingsService->getSetting('general', 'showClientTimeZone') &&
            !empty($data['package'])
        ) {
            foreach ($data['package'] as $key => $recurringData) {
                $data['package'][$key]['bookingStart'] = DateTimeService::getCustomDateTimeFromUtc(
                    $recurringData['bookingStart']
                );
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return Appointment|Event
     *
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     */
    public function getReservationEntity($data)
    {
        /** @var Appointment|Event $reservation */
        $reservation = null;

        switch ($data['type']) {
            case Entities::APPOINTMENT:
                $reservation = AppointmentFactory::create($data);

                break;

            case Entities::EVENT:
                $reservation = EventFactory::create($data);

                break;
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var Collection $indexedBookings */
        $indexedBookings = new Collection();

        /** @var CustomerBooking $booking */
        foreach ($reservation->getBookings()->getItems() as $booking) {
            if ($booking->getCustomer() === null && $booking->getCustomerId() !== null) {
                /** @var Customer $customer */
                $customer = $userRepository->getById($booking->getCustomerId()->getValue());

                $booking->setCustomer(UserFactory::create(array_merge($customer->toArray(), ['type' => 'customer'])));
            }

            $token = $bookingRepository->getToken($booking->getId()->getValue());

            if (!empty($token['token'])) {
                $booking->setToken(new Token($token['token']));
            }

            $indexedBookings->addItem($booking, $booking->getId()->getValue());
        }

        $reservation->setBookings($indexedBookings);

        $locationId = $reservation->getLocation() === null && $reservation->getLocationId() !== null ?
            $reservation->getLocationId()->getValue() : null;

        switch ($reservation->getType()->getValue()) {
            case Entities::APPOINTMENT:
                if ($reservation->getService() === null && $reservation->getServiceId() !== null) {
                    /** @var BookableApplicationService $bookableAS */
                    $bookableAS = $this->container->get('application.bookable.service');

                    /** @var Service $service */
                    $service = $bookableAS->getAppointmentService(
                        $reservation->getServiceId()->getValue(),
                        $reservation->getProviderId()->getValue()
                    );

                    $reservation->setService($service);
                }

                if ($reservation->getProvider() === null && $reservation->getProviderId() !== null) {
                    /** @var Provider $provider */
                    $provider = $providerRepository->getByCriteriaWithSchedule(
                        [
                            Entities::PROVIDERS => [$reservation->getProviderId()->getValue()
                            ]
                        ]
                    )->getItem($reservation->getProviderId()->getValue());

                    $reservation->setProvider($provider);
                }

                if ($reservation->getLocation() === null &&
                    $reservation->getLocationId() === null &&
                    $reservation->getProvider() !== null &&
                    $reservation->getProvider()->getLocationId() !== null
                ) {
                    $locationId = $reservation->getProvider()->getLocationId()->getValue();
                }

                break;
        }

        if ($locationId !== null) {
            /** @var Location $location */
            $location = $locationRepository->getById($locationId);

            $reservation->setLocation($location);
        }

        return $reservation;
    }

    /**
     * @param array $data
     *
     * @return CustomerBooking
     *
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     */
    public function getBookingEntity($data)
    {
        /** @var CustomerBooking $booking */
        $booking = CustomerBookingFactory::create($data);

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        if ($booking->getCustomer() === null && $booking->getCustomerId() !== null) {
            /** @var Customer $customer */
            $customer = $userRepository->getById($booking->getCustomerId()->getValue());

            $booking->setCustomer(UserFactory::create(array_merge($customer->toArray(), ['type' => 'customer'])));
        }

        $token = $bookingRepository->getToken($booking->getId()->getValue());

        if (!empty($token['token'])) {
            $booking->setToken(new Token($token['token']));
        }

        return $booking;
    }

    /**
     * @param Appointment|Event $reservation
     *
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function setReservationEntities($reservation)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');

        /** @var CustomerBooking $booking */
        foreach ($reservation->getBookings()->getItems() as $booking) {
            if ($booking->getCustomer() === null && $booking->getCustomerId() !== null) {
                /** @var Customer $customer */
                $customer = $userRepository->getById($booking->getCustomerId()->getValue());

                $booking->setCustomer(UserFactory::create(array_merge($customer->toArray(), ['type' => 'customer'])));
            }
        }

        $locationId = $reservation->getLocation() === null && $reservation->getLocationId() !== null ?
            $reservation->getLocationId()->getValue() : null;

        switch ($reservation->getType()->getValue()) {
            case Entities::APPOINTMENT:
                if ($reservation->getService() === null && $reservation->getServiceId() !== null) {
                    /** @var BookableApplicationService $bookableAS */
                    $bookableAS = $this->container->get('application.bookable.service');

                    /** @var Service $service */
                    $service = $bookableAS->getAppointmentService(
                        $reservation->getServiceId()->getValue(),
                        $reservation->getProviderId()->getValue()
                    );

                    if ($service->getCategory() === null && $service->getCategoryId() !== null) {
                        /** @var Category $category */
                        $category = $categoryRepository->getById($service->getCategoryId()->getValue());

                        $service->setCategory($category);
                    }

                    $reservation->setService($service);
                }

                if ($reservation->getProvider() === null && $reservation->getProviderId() !== null) {
                    /** @var Collection $providers */
                    $providers = $providerRepository->getByCriteriaWithSchedule(
                        [
                            Entities::PROVIDERS => [$reservation->getProviderId()->getValue()]
                        ]
                    );
                    /** @var Provider $provider */
                    $provider = count($providers->getItems()) ? $providers->getItem($reservation->getProviderId()->getValue()) : null;
                    if ($provider) {
                        $reservation->setProvider($provider);
                    }
                }

                if ($reservation->getLocation() === null &&
                    $reservation->getLocationId() === null &&
                    $reservation->getProvider() !== null &&
                    $reservation->getProvider()->getLocationId() !== null
                ) {
                    $locationId = $reservation->getProvider()->getLocationId()->getValue();
                }

                break;
        }

        if ($locationId !== null) {
            /** @var Location $location */
            $location = $locationRepository->getById($locationId);

            $reservation->setLocation($location);
        }
    }
}
