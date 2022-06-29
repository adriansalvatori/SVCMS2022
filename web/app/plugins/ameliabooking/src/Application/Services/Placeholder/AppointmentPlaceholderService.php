<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\NotificationSendTo;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\Services\LessonSpace\LessonSpaceService;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use DateTime;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentPlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class AppointmentPlaceholderService extends PlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws ContainerException
     */
    public function getEntityPlaceholdersDummyData($type)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $companySettings = $settingsService->getCategorySettings('company');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $timestamp = date_create()->getTimestamp();


        return [
            'appointment_id'          => '1',
            'appointment_date'        => date_i18n($dateFormat, $timestamp),
            'appointment_date_time'   => date_i18n($dateFormat . ' ' . $timeFormat, $timestamp),
            'appointment_start_time'  => date_i18n($timeFormat, $timestamp),
            'appointment_end_time'    => date_i18n($timeFormat, date_create('1 hour')->getTimestamp()),
            'appointment_notes'       => 'Appointment note',
            'appointment_price'       => $helperService->getFormattedPrice(100),
            'appointment_cancel_url'  => 'http://cancel_url.com',
            'zoom_join_url'           => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a>' : 'https://join_zoom_link.com',
            'zoom_host_url'           => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a>' : 'https://start_zoom_link.com',
            'google_meet_url'          => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a>' : 'https://join_google_meet_link.com',
            'lesson_space_url'       => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a>' : 'https://lessonspace.com/room-id',
            'appointment_duration'    => $helperService->secondsToNiceDuration(1800),
            'appointment_deposit_payment'     => $helperService->getFormattedPrice(20),
            'appointment_status'      => BackendStrings::getCommonStrings()['approved'],
            'category_name'           => 'Category Name',
            'service_description'     => 'Service Description',
            'reservation_description' => 'Service Description',
            'service_duration'        => $helperService->secondsToNiceDuration(5400),
            'service_name'            => 'Service Name',
            'reservation_name'        => 'Service Name',
            'service_price'           => $helperService->getFormattedPrice(100),
            'service_extras'          => 'Extra1, Extra2, Extra3'
        ];
    }

    /**
     * @param array        $appointment
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getPlaceholdersData($appointment, $bookingKey = null, $type = null, $customer = null)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        $token = isset($appointment['bookings'][$bookingKey]) ?
            $bookingRepository->getToken($appointment['bookings'][$bookingKey]['id']) : null;

        $token = isset($token['token']) ? $token['token'] : null;

        $data = [];

        $data = array_merge($data, $this->getAppointmentData($appointment, $bookingKey, $type));
        $data = array_merge($data, $this->getServiceData($appointment, $bookingKey));
        $data = array_merge($data, $this->getEmployeeData($appointment, $bookingKey));
        $data = array_merge($data, $this->getRecurringAppointmentsData($appointment, $bookingKey, $type, 'recurring'));
        $data = array_merge($data, $this->getBookingData($appointment, $type, $bookingKey, $token, $data['deposit']));
        $data = array_merge($data, $this->getCompanyData($bookingKey !== null ? $appointment['bookings'][$bookingKey]['info'] : null));
        $data = array_merge($data, $this->getCustomersData($appointment, $type, $bookingKey, $customer));
        $data = array_merge($data, $this->getCustomFieldsData($appointment, $bookingKey));
        $data = array_merge($data, $this->getCouponsData($appointment, $type, $bookingKey));

        return $data;
    }

    /**
     * @param        $appointment
     * @param null   $bookingKey
     * @param string $type
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getAppointmentData($appointment, $bookingKey = null, $type = null)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        if ($appointment['providerId'] && empty($appointment['provider'])) {
            /** @var Provider $user */
            $user = $userRepository->getById($appointment['providerId']);

            $appointment['provider'] = $user->toArray();
        }

        if ($bookingKey !== null && $appointment['bookings'][$bookingKey]['utcOffset'] !== null
            && $settingsService->getSetting('general', 'showClientTimeZone')
        ) {
            $bookingStart = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingStart']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );

            $bookingEnd = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingEnd']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );
        } else if ($bookingKey === null && !empty($appointment['provider']['timeZone'])) {
            $bookingStart = DateTimeService::getDateTimeObjectInTimeZone(
                DateTimeService::getCustomDateTimeObject(
                    $appointment['bookingStart']
                )->setTimezone(new \DateTimeZone($appointment['provider']['timeZone']))->format('Y-m-d H:i:s'),
                'UTC'
            );

            $bookingEnd = DateTimeService::getDateTimeObjectInTimeZone(
                DateTimeService::getCustomDateTimeObject(
                    $appointment['bookingEnd']
                )->setTimezone(new \DateTimeZone($appointment['provider']['timeZone']))->format('Y-m-d H:i:s'),
                'UTC'
            );
        } else {
            $bookingStart = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingStart']);
            $bookingEnd   = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingEnd']);
        }

        $zoomStartUrl = '';
        $zoomJoinUrl  = '';

        $lessonSpaceLink = '';
        if (array_key_exists('lessonSpace', $appointment) && $appointment['lessonSpace']) {
            $lessonSpaceLink = $type === 'email' ?
                '<a href="' . $appointment['lessonSpace'] . '">' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a>'
                : $appointment['lessonSpace'];
        }

        if (isset($appointment['zoomMeeting']['joinUrl'], $appointment['zoomMeeting']['startUrl'])) {
            $zoomStartUrl = $appointment['zoomMeeting']['startUrl'];
            $zoomJoinUrl  = $appointment['zoomMeeting']['joinUrl'];
        }

        $googleMeetUrl = '';
        if (array_key_exists('googleMeetUrl', $appointment) && $appointment['googleMeetUrl']) {
            $googleMeetUrl = $type === 'email' ?
                '<a href="' . $appointment['googleMeetUrl'] . '">' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a>'
                : $appointment['googleMeetUrl'];
        }

        return [
            'appointment_id'         => !empty($appointment['id']) ? $appointment['id'] : '',
            'appointment_status'     => BackendStrings::getCommonStrings()[$appointment['status']],
            'appointment_notes'      => !empty($appointment['internalNotes']) ? $appointment['internalNotes'] : '',
            'appointment_date'       => date_i18n($dateFormat, $bookingStart->getTimestamp()),
            'appointment_date_time'  => date_i18n($dateFormat . ' ' . $timeFormat, $bookingStart->getTimestamp()),
            'appointment_start_time' => date_i18n($timeFormat, $bookingStart->getTimestamp()),
            'appointment_end_time'   => date_i18n($timeFormat, $bookingEnd->getTimestamp()),
            'lesson_space_url'       => $lessonSpaceLink,
            'zoom_host_url'          => $zoomStartUrl && $type === 'email' ?
                '<a href="' . $zoomStartUrl . '">' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a>'
                : $zoomStartUrl,
            'zoom_join_url'          => $zoomJoinUrl && $type === 'email' ?
                '<a href="' . $zoomJoinUrl . '">' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a>'
                : $zoomJoinUrl,
            'google_meet_url'        => $googleMeetUrl,
        ];
    }

    /**
     * @param $appointmentArray
     * @param $bookingKey
     *
     * @return array
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    private function getServiceData($appointmentArray, $bookingKey = null)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var Service $service */
        $service = $serviceRepository->getByIdWithExtras($appointmentArray['serviceId']);
        /** @var Category $category */
        $category = $categoryRepository->getById($service->getCategoryId()->getValue());

        $categoryName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null,
            $category->getTranslations() ? $category->getTranslations()->getValue() : null,
            'name'
        ) ?: $category->getName()->getValue();

        $serviceName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null,
            $service->getTranslations() ? $service->getTranslations()->getValue() : null,
            'name'
        ) ?: $service->getName()->getValue();

        $serviceDescription = $helperService->getBookingTranslation(
            $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null,
            $service->getTranslations() ? $service->getTranslations()->getValue() : null,
            'description'
        ) ?: ($service->getDescription() ? $service->getDescription()->getValue() : '');

        $data = [
            'category_name'           => $categoryName,
            'service_description'     => $serviceDescription,
            'reservation_description' => $serviceDescription,
            'service_duration'        => $helperService->secondsToNiceDuration($service->getDuration()->getValue()),
            'service_name'            => $serviceName,
            'reservation_name'        => $serviceName,
            'service_price'           => $helperService->getFormattedPrice($service->getPrice()->getValue())
        ];

        $bookingExtras = [];

        foreach ((array)$appointmentArray['bookings'] as $booking) {
            foreach ((array)$booking['extras'] as $bookingExtra) {
                $bookingExtras[$bookingExtra['extraId']] = [
                    'quantity' => $bookingExtra['quantity']
                ];
            }
        }

        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        /** @var Collection $extras */
        $extras = $extraRepository->getAllIndexedById();

        $duration = $service->getDuration()->getValue();

        if ($bookingKey !== null) {
            foreach ($appointmentArray['bookings'][$bookingKey]['extras'] as $bookingExtra) {
                /** @var Extra $extra */
                $extra = $extras->getItem($bookingExtra['extraId']);

                $duration += $extra->getDuration() ? $extra->getDuration()->getValue() * $bookingExtra['quantity'] : 0;
            }
        } else {
            $maxBookingDuration = 0;

            foreach ($appointmentArray['bookings'] as $booking) {
                $bookingDuration = $duration;

                foreach ($booking['extras'] as $bookingExtra) {
                    /** @var Extra $extra */
                    $extra = $extras->getItem($bookingExtra['extraId']);

                    $bookingDuration += $extra->getDuration() ?
                        $extra->getDuration()->getValue() * $bookingExtra['quantity'] : 0;
                }

                if ($bookingDuration > $maxBookingDuration &&
                    ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING)
                ) {
                    $maxBookingDuration = $bookingDuration;
                }
            }

            $duration = $maxBookingDuration;
        }

        $data['appointment_duration'] = $helperService->secondsToNiceDuration($duration);

        $allExtraNames = "";
        /** @var Extra $extra */
        foreach ($extras->getItems() as $extra) {
            $extraId = $extra->getId()->getValue();

            $data["service_extra_{$extraId}_name"] =
                array_key_exists($extraId, $bookingExtras) ? $extra->getName()->getValue() : '';

            $data["service_extra_{$extraId}_name"] = $helperService->getBookingTranslation(
                $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null,
                $data["service_extra_{$extraId}_name"] && $extra->getTranslations() ?
                    $extra->getTranslations()->getValue() : null,
                'name'
            ) ?: $data["service_extra_{$extraId}_name"];

            $data["service_extra_{$extraId}_quantity"] =
                array_key_exists($extraId, $bookingExtras) ? $bookingExtras[$extraId]['quantity'] : '';

            $data["service_extra_{$extraId}_price"] = array_key_exists($extraId, $bookingExtras) ?
                $helperService->getFormattedPrice($extra->getPrice()->getValue()) : '';

            if(!empty($data["service_extra_{$extraId}_name"])) $allExtraNames .= $data["service_extra_{$extraId}_name"].', ';
        }

        $data["service_extras"] = substr($allExtraNames, 0, -2);
        $data['deposit'] = $service->getDeposit() && $service->getDeposit()->getValue();

        return $data;
    }

    /**
     * @param $appointment
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function getEmployeeData($appointment, $bookingKey = null)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');
        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var Provider $user */
        $user = $userRepository->getById($appointment['providerId']);

        if (!($locationId = $appointment['locationId'])) {
            $locationId = $user->getLocationId() ? $user->getLocationId()->getValue() : null;
        }

        /** @var Location $location */
        $location = $locationId ? $locationRepository->getById($locationId) : null;

        $locationName        = $settingsService->getSetting('company', 'address');
        $locationDescription = '';
        if ($location) {
            $locationName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $appointment['bookings'][$bookingKey]['info'] : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'name'
            ) ?: $location->getName()->getValue();

            $locationDescription = $helperService->getBookingTranslation(
                $bookingKey !== null ? $appointment['bookings'][$bookingKey]['info'] : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'description'
            ) ?: ($location->getDescription() ? $location->getDescription()->getValue() : '');
        }

        $firstName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $appointment['bookings'][$bookingKey]['info'] : null,
            $user->getTranslations() ? $user->getTranslations()->getValue() : null,
            'firstName'
        ) ?: $user->getFirstName()->getValue();

        $lastName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $appointment['bookings'][$bookingKey]['info'] : null,
            $user->getTranslations() ? $user->getTranslations()->getValue() : null,
            'lastName'
        ) ?: $user->getLastName()->getValue();

        return [
            'employee_email'       => $user->getEmail()->getValue(),
            'employee_first_name'  => $firstName,
            'employee_last_name'   => $lastName,
            'employee_full_name'   => $firstName . ' ' . $lastName,
            'employee_phone'       => $user->getPhone()->getValue(),
            'employee_note'        => $user->getNote() ? $user->getNote()->getValue() : '',
            'employee_panel_url'  => trim($this->container->get('domain.settings.service')
                ->getSetting('roles', 'providerCabinet')['pageUrl']),
            'location_address'     => !$location ?
                $settingsService->getSetting('company', 'address') : $location->getAddress()->getValue(),
            'location_phone'       => !$location ?
                $settingsService->getSetting('company', 'phone') : $location->getPhone()->getValue(),
            'location_name'        => $locationName,
            'location_description' => $locationDescription
        ];
    }

    /**
     * @param array  $appointment
     * @param int    $bookingKey
     * @param string $type
     * @param string $placeholderType
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getRecurringAppointmentsData($appointment, $bookingKey, $type, $placeholderType)
    {
        if (!array_key_exists('recurring', $appointment)) {
            return [
                "{$placeholderType}_appointments_details" => ''
            ];
        }

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.appointment.service");

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $appointmentsSettings = $settingsService->getCategorySettings('appointments');

        $recurringAppointmentDetails = [];

        foreach ($appointment['recurring'] as $recurringData) {
            $recurringBookingKey = null;

            $isForCustomer =
                $bookingKey !== null ||
                (isset($appointment['isForCustomer']) && $appointment['isForCustomer']);

            if ($isForCustomer) {
                foreach ($recurringData['appointment']['bookings'] as $key => $recurringBooking) {
                    if (isset($recurringData['booking']['id'])) {
                        if ($recurringBooking['id'] === $recurringData['booking']['id']) {
                            $recurringBookingKey = $key;
                        }
                    } else {
                        $recurringBookingKey = $bookingKey;
                    }
                }
            }

            $token =
                $recurringBookingKey !== null &&
                isset(
                    $recurringData['appointment']['bookings'][$recurringBookingKey],
                    $recurringData['appointment']['bookings'][$recurringBookingKey]['id']
                ) ? $bookingRepository->getToken($recurringData['appointment']['bookings'][$recurringBookingKey]['id']) : null;

            $recurringPlaceholders = array_merge(
                $this->getEmployeeData($recurringData['appointment'], $recurringBookingKey),
                $this->getAppointmentData($recurringData['appointment'], $recurringBookingKey, $type),
                $this->getServiceData($recurringData['appointment'], $recurringBookingKey),
                $this->getCustomFieldsData($recurringData['appointment'], $bookingKey),
                $this->getBookingData(
                    $recurringData['appointment'],
                    $type,
                    $recurringBookingKey,
                    isset($token['token']) ? $token['token'] : null
                )
            );

            unset($recurringPlaceholders['icsFiles']);

            if (!$isForCustomer) {
                if (isset($recurringPlaceholders['appointment_cancel_url'])) {
                    $recurringPlaceholders['appointment_cancel_url'] = '';
                }

                $recurringPlaceholders['zoom_join_url'] = '';
            } else {
                $recurringPlaceholders['employee_panel_url'] = '';

                $recurringPlaceholders['zoom_host_url'] = '';
            }

            $placeholderString =
                $placeholderType .
                'Placeholders' .
                ($isForCustomer && $placeholderType === 'package' ? 'Customer' : '') .
                ($isForCustomer && $placeholderType === 'recurring' ? 'Customer' : '') .
                ($type === 'email' ? '' : 'Sms');

            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            $content = $helperService->getBookingTranslation(
                $recurringBookingKey !== null ? $recurringData['appointment']['bookings'][$recurringBookingKey]['info'] : null,
                json_encode($appointmentsSettings['translations']),
                $placeholderString
            ) ?: $appointmentsSettings[$placeholderString];

            $recurringAppointmentDetails[] = $placeholderService->applyPlaceholders(
                $content,
                $recurringPlaceholders
            );
        }

        return [
            "{$placeholderType}_appointments_details" => $recurringAppointmentDetails ? implode(
                $type === 'email' ? '<br>' : PHP_EOL,
                $recurringAppointmentDetails
            ) : ''
        ];
    }
}
