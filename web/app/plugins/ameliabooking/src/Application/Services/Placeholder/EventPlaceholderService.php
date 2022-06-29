<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Entity\Booking\Event\CustomerBookingEventTicket;
use AmeliaBooking\Domain\Entity\Booking\Event\EventTicket;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\CustomerBookingEventTicketRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventTicketRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use DateTime;

/**
 * Class EventPlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class EventPlaceholderService extends PlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getEntityPlaceholdersDummyData($type)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $companySettings = $settingsService->getCategorySettings('company');

        $liStartTag = $type === 'email' ? '<li>' : '';
        $liEndTag   = $type === 'email' ? '</li>' : PHP_EOL;
        $ulStartTag = $type === 'email' ? '<ul>' : '';
        $ulEndTag   = $type === 'email' ? '</ul>' : '';

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $timestamp = new DateTime();

        $periodStartTime = $timestamp->format($timeFormat);
        $periodStartDate = $timestamp->format($dateFormat);
        $periodEndDate = $timestamp->modify('+1 day');
        $periodEndTime = $periodEndDate->add(new \DateInterval('PT1H'))->format($timeFormat);
        $periodEndDate = $periodEndDate->format($dateFormat);

        $dateTimeString = $periodStartDate . ' - ' . $periodEndDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')';


        return [
            'attendee_code'             => '12345',
            'event_name'                => 'Event Name',
            'reservation_name'          => 'Reservation Name',
            'event_location'            => $companySettings['address'],
            'event_cancel_url'          => 'http://event_cancel_url.com',
            'event_periods'             =>
                $ulStartTag .
                    $liStartTag . date_i18n($dateFormat, $periodStartDate) . $liEndTag .
                    $liStartTag . date_i18n($dateFormat, $periodEndDate) . $liEndTag .
                $ulEndTag,
            'event_period_date'         =>
                $ulStartTag .
                    $liStartTag . date_i18n($dateFormat, $periodStartDate) . $liEndTag .
                    $liStartTag . date_i18n($dateFormat, $periodEndDate) . $liEndTag .
                $ulEndTag,
            'event_period_date_time'    =>
                $ulStartTag .
                    $liStartTag . $dateTimeString . $liEndTag .
                $ulEndTag,
            'event_start_date'          => date_i18n($dateFormat, $periodStartDate),
            'event_start_time'          => date_i18n($timeFormat, $periodStartTime),
            'event_start_date_time'     => date_i18n($dateFormat . ' ' . $timeFormat, $timestamp),
            'event_end_date'            => date_i18n($dateFormat, $periodEndDate),
            'event_end_time'            => date_i18n($timeFormat, $periodEndTime),
            'event_end_date_time'       => date_i18n($dateFormat . ' ' . $timeFormat, $periodEndDate),
            'event_deposit_payment'     => $helperService->getFormattedPrice(20),
            'event_price'               => $helperService->getFormattedPrice(100),
            'zoom_host_url_date'        => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] .'</a></li>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'http://start_zoom_meeting_link.com',
            'zoom_host_url_date_time'   => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'http://start_zoom_meeting_link.com',
            'zoom_join_url_date'        => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] .'</a></li>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'http://join_zoom_meeting_link.com',
            'zoom_join_url_date_time'   => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'http://join_zoom_meeting_link.com' ,
            'lesson_space_url_date'        => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] .'</a></li>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'https://lesson_space.com/room-id',
            'lesson_space_url_date_time'   => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'https://lesson_space.com/room-id',
            'event_description'         => 'Event Description',
            'reservation_description'   => 'Reservation Description',
            'employee_name_email_phone' =>
                $ulStartTag .
                    $liStartTag . 'John Smith, 555-0120' . $liEndTag .
                    $liStartTag . 'Edward Williams, 555-3524' . $liEndTag .
                $ulEndTag,
        ];
    }

    /**
     * @param array        $event
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     *
     * @return array
     *
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public function getPlaceholdersData($event, $bookingKey = null, $type = null, $customer = null)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        $token = isset($event['bookings'][$bookingKey]) ?
            $bookingRepository->getToken($event['bookings'][$bookingKey]['id']) : null;

        $token = isset($token['token']) ? $token['token'] : null;

        $data = [];

        $data = array_merge($data, $this->getEventData($event, $bookingKey, $token, $type));
        $data = array_merge($data, $this->getBookingData($event, $type, $bookingKey, $token));
        $data = array_merge($data, $this->getCompanyData($bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null));
        $data = array_merge($data, $this->getCustomersData($event, $type, $bookingKey, $customer));
        $data = array_merge($data, $this->getCustomFieldsData($event, $bookingKey));
        $data = array_merge($data, $this->getCouponsData($event, $type, $bookingKey));

        return $data;
    }

    /**
     * @param array  $event
     * @param int    $bookingKey
     * @param string $token
     * @param string $type
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    private function getEventData($event, $bookingKey = null, $token = null, $type = null)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var UserRepository $userRepo */
        $userRepo = $this->container->get('domain.users.repository');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $dateTimes = [];

        $locationName = '';
        $locationAddress = '';
        $locationDescription = '';
        $locationPhone = '';

        if ($event['locationId']) {
            /** @var LocationRepository $locationRepository */
            $locationRepository = $this->container->get('domain.locations.repository');

            $location = $locationRepository->getById($event['locationId']);

            $locationName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'name'
            ) ?: $location->getName()->getValue();

            $locationDescription = $helperService->getBookingTranslation(
                $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'description'
            ) ?: ($location->getDescription() ? $location->getDescription()->getValue() : '');

            $locationAddress = $location->getAddress() ? $location->getAddress()->getValue() : '';
            $locationPhone = $location->getPhone() ? $location->getPhone()->getValue() : '';
        } elseif ($event['customLocation']) {
            $locationName = $event['customLocation'];
        }

        $staff = [];

        /** @var string $liStartTag */
        $liStartTag = $type === 'email' ? '<li>' : '';

        /** @var string $liEndTag */
        $liEndTag = $type === 'email' ? '</li>' : PHP_EOL;

        /** @var string $ulStartTag */
        $ulStartTag = $type === 'email' ? '<ul>' : '';

        /** @var string $ulEndTag */
        $ulEndTag = $type === 'email' ? '</ul>' : '';

        $providers = (array)$event['providers'];

        if (isset($event['organizerId']) && !in_array($event['organizerId'], array_column($providers, 'id'))) {
            $providers[] = $userRepo->getById($event['organizerId'])->toArray();
        }

        $timeZones = [];

        foreach ($providers as $provider) {
            $firstName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
                $provider['translations'],
                'firstName'
            ) ?: $provider['firstName'];

            $lastName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
                $provider['translations'],
                'lastName'
            ) ?: $provider['lastName'];

            $staff[] = [
                'employee_first_name'       => $firstName,
                'employee_last_name'        => $lastName,
                'employee_full_name'        => $firstName . ' ' . $lastName,
                'employee_note'             => $provider['note'],
                'employee_phone'            => $provider['phone'],
                'employee_email'            => $provider['email'],
                'employee_name_email_phone' =>
                    (sizeof($event['providers']) > 1 ? $liStartTag : '') .
                    $firstName . ' ' . $lastName .
                    ($provider['phone'] ? ', ' . $provider['phone'] : '') .
                    (sizeof($event['providers']) > 1 ? $liEndTag : ''),
            ];

            $timeZones[] = $provider['timeZone'];
        }

        $timeZone = $providers && $timeZones && count(array_unique($timeZones)) === 1 ? array_unique($timeZones)[0] : null;

        $staff = [
            'employee_first_name'       =>
                implode(', ', array_column($staff, 'employee_first_name')),
            'employee_last_name'        =>
                implode(', ', array_column($staff, 'employee_last_name')),
            'employee_full_name'        =>
                implode(', ', array_column($staff, 'employee_full_name')),
            'employee_note'             =>
                implode(', ', array_column($staff, 'employee_note')),
            'employee_phone'            =>
                implode(', ', array_column($staff, 'employee_phone')),
            'employee_email'            =>
                implode(', ', array_column($staff, 'employee_email')),
            'employee_name_email_phone' =>
                $ulStartTag . implode('', array_column($staff, 'employee_name_email_phone')) . $ulEndTag,
        ];

        foreach ((array)$event['periods'] as $period) {
            if ($bookingKey !== null &&
                $event['bookings'][$bookingKey]['utcOffset'] !== null &&
                $settingsService->getSetting('general', 'showClientTimeZone')
            ) {
                $dateTimes[] = [
                    'start' => DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($period['periodStart']),
                        $event['bookings'][$bookingKey]['utcOffset']
                    ),
                    'end'   => DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($period['periodEnd']),
                        $event['bookings'][$bookingKey]['utcOffset']
                    )
                ];
            } else if ($bookingKey === null && $timeZone) {
                $dateTimes[] = [
                    'start' => DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $period['periodStart']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    ),
                    'end'   => DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $period['periodEnd']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    )
                ];
            } else {
                $dateTimes[] = [
                    'start' => DateTime::createFromFormat('Y-m-d H:i:s', $period['periodStart']),
                    'end'   => DateTime::createFromFormat('Y-m-d H:i:s', $period['periodEnd'])
                ];
            }
        }

        $eventDateList = [];
        $eventDateTimeList = [];
        $eventZoomStartDateList = [];
        $eventZoomStartDateTimeList = [];
        $eventZoomJoinDateList = [];
        $eventZoomJoinDateTimeList = [];
        $eventLessonSpaceDateList = [];
        $eventLessonSpaceDateTimeList = [];


        foreach ($dateTimes as $key => $dateTime) {
            /** @var \DateTime $startDateTime */
            $startDateTime = $dateTime['start'];

            /** @var \DateTime $endDateTime */
            $endDateTime = $dateTime['end'];

            $startDateString = $startDateTime->format('Y-m-d');
            $endDateString = $endDateTime->format('Y-m-d');

            $periodStartDate = date_i18n($dateFormat, $startDateTime->getTimestamp());
            $periodEndDate = date_i18n($dateFormat, $endDateTime->getTimestamp());

            $periodStartTime = date_i18n($timeFormat, $startDateTime->getTimestamp());
            $periodEndTime = date_i18n($timeFormat, $endDateTime->getTimestamp());

            $dateString = $startDateString === $endDateString ?
                $periodStartDate :
                $periodStartDate . ' - ' . $periodEndDate;

            $dateTimeString = $startDateString === $endDateString ?
                $periodStartDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')' :
                $periodStartDate . ' - ' . $periodEndDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')';

            $eventDateList[] = "$liStartTag{$dateString}$liEndTag";
            $eventDateTimeList[] = "$liStartTag{$dateTimeString}$liEndTag";

            if ($event['zoomUserId'] && $event['periods'][$key]['zoomMeeting']) {
                $startUrl = $event['periods'][$key]['zoomMeeting']['startUrl'];
                $joinUrl = $event['periods'][$key]['zoomMeeting']['joinUrl'];

                $eventZoomStartDateList[] =  $type === 'email' ? '<li><a href="' . $startUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' : $dateString . ': ' . $startUrl;
                $eventZoomStartDateTimeList[] = $type === 'email' ? '<li><a href="' . $startUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' : $dateTimeString . ': ' . $startUrl;
                $eventZoomJoinDateList[] = $type === 'email' ? '<li><a href="' . $joinUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' : $dateString . ': ' . $joinUrl;
                $eventZoomJoinDateTimeList[] = $type === 'email' ? '<li><a href="' . $joinUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' : $dateTimeString . ': ' . $joinUrl;
            }

            if ($event['periods'][$key]['lessonSpace']) {
                $lessoSpaceUrl = $event['periods'][$key]['lessonSpace'];

                $eventLessonSpaceDateList[]     = $type === 'email' ? '<li><a href="' . $lessoSpaceUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' : $dateString . ': ' . $lessoSpaceUrl;
                $eventLessonSpaceDateTimeList[] = $type === 'email' ? '<li><a href="' . $lessoSpaceUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' : $dateTimeString . ': ' . $lessoSpaceUrl;
            }
        }

        /** @var \DateTime $eventStartDateTime */
        $eventStartDateTime = $dateTimes[0]['start'];

        /** @var \DateTime $eventEndDateTime */
        $eventEndDateTime = $dateTimes[sizeof($dateTimes) - 1]['end'];

        $attendeeCode = $bookingKey !== null && $token ? $token : '';

        $eventName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
            $event['translations'],
            'name'
        ) ?: $event['name'];

        $eventDescription = $helperService->getBookingTranslation(
            $bookingKey !== null ? $event['bookings'][$bookingKey]['info'] : null,
            $event['translations'],
            'description'
        ) ?: $event['description'];

        $eventTickets = [];

        /** @var EventTicketRepository $eventTicketRepository */
        $eventTicketRepository = $this->container->get('domain.booking.event.ticket.repository');

        $ticketsPrice = null;

        if ($bookingKey !== null && !empty($event['bookings'][$bookingKey]['ticketsData'])) {
            $ticketsPrice = 0;

            /** @var CustomerBookingEventTicketRepository $bookingEventTicketRepository */
            $bookingEventTicketRepository =
                $this->container->get('domain.booking.customerBookingEventTicket.repository');

            $ticketsBookings = $bookingEventTicketRepository->getByEntityId(
                $event['bookings'][$bookingKey]['id'],
                'customerBookingId'
            );

            if ($ticketsBookings->length()) {
                /** @var CustomerBookingEventTicket $bookingToEventTicket */
                foreach ($ticketsBookings->getItems() as $key => $bookingToEventTicket) {
                    /** @var EventTicket $ticket */
                    $ticket = $eventTicketRepository->getById($bookingToEventTicket->getEventTicketId()->getValue());

                    $ticketName = $ticket->getTranslations() && $helperService->getBookingTranslation(
                        $event['bookings'][$bookingKey]['info'],
                        $ticket->getTranslations()->getValue(),
                        null
                    ) ?: $ticket->getName()->getValue();

                    $eventTickets[] = $bookingToEventTicket->getPersons()->getValue() . ' x ' . $ticketName;

                    $ticketsPrice +=
                        $bookingToEventTicket->getPersons()->getValue() * $bookingToEventTicket->getPrice()->getValue();
                }
            } elseif (!empty($event['bookings'][$bookingKey]['ticketsData'])) {
                foreach ($event['bookings'][$bookingKey]['ticketsData'] as $key => $bookingToEventTicket) {
                    if ($bookingToEventTicket['price']) {
                        /** @var EventTicket $ticket */
                        $ticket = $eventTicketRepository->getById($bookingToEventTicket['eventTicketId']);

                        $ticketName = $ticket->getTranslations() && $helperService->getBookingTranslation(
                            $event['bookings'][$bookingKey]['info'],
                            $ticket->getTranslations()->getValue(),
                            null
                        ) ?: $ticket->getName()->getValue();

                        $eventTickets[] = $bookingToEventTicket['persons'] . ' x ' . $ticketName;

                        $ticketsPrice +=
                            $bookingToEventTicket['persons'] * $bookingToEventTicket['price'];
                    }
                }
            }
        } else {
            foreach ($event['bookings'] as $booking) {
                if (!empty($booking['isLastBooking']) && !empty($booking['ticketsData'])) {
                    $ticketsPrice = 0;

                    /** @var CustomerBookingEventTicketRepository $bookingEventTicketRepository */
                    $bookingEventTicketRepository =
                        $this->container->get('domain.booking.customerBookingEventTicket.repository');

                    $ticketsBookings = $bookingEventTicketRepository->getByEntityId(
                        $booking['id'],
                        'customerBookingId'
                    );

                    if ($ticketsBookings->length()) {
                        /** @var CustomerBookingEventTicket $bookingToEventTicket */
                        foreach ($ticketsBookings->getItems() as $key => $bookingToEventTicket) {
                            /** @var EventTicket $ticket */
                            $ticket = $eventTicketRepository->getById($bookingToEventTicket->getEventTicketId()->getValue());

                            $eventTickets[] = $bookingToEventTicket->getPersons()->getValue() . ' x ' . $ticket->getName()->getValue();

                            $ticketsPrice +=
                                $bookingToEventTicket->getPersons()->getValue() * $bookingToEventTicket->getPrice()->getValue();
                        }
                    } elseif (!empty($booking['ticketsData'])) {
                        foreach ($booking['ticketsData'] as $key => $bookingToEventTicket) {
                            if ($bookingToEventTicket['price']) {
                                /** @var EventTicket $ticket */
                                $ticket = $eventTicketRepository->getById($bookingToEventTicket['eventTicketId']);

                                $ticketName = $ticket->getTranslations() && $helperService->getBookingTranslation(
                                    $event['bookings'][$bookingKey]['info'],
                                    $ticket->getTranslations()->getValue(),
                                    null
                                ) ?: $ticket->getName()->getValue();

                                $eventTickets[] = $bookingToEventTicket['persons'] . ' x ' . $ticketName;

                                $ticketsPrice +=
                                    $bookingToEventTicket['persons'] * $bookingToEventTicket['price'];
                            }
                        }
                    }
                }
            }
        }

        return array_merge([
            'attendee_code'            => substr($attendeeCode, 0, 5),
            'reservation_name'         => $eventName,
            'event_name'               => $eventName,
            'event_name_url'           => sanitize_title($event['name']),
            'event_price'              => $ticketsPrice !== null ?
                $helperService->getFormattedPrice($ticketsPrice) : $helperService->getFormattedPrice($event['price']),
            'event_description'        => $eventDescription,
            'event_tickets'            => $eventTickets ? implode(', ', $eventTickets) : '',
            'reservation_description'  => $eventDescription,
            'event_location'           => $locationName,
            'location_name'            => $locationName,
            'location_address'         => $locationAddress,
            'location_description'     => $locationDescription,
            'location_phone'           => $locationPhone,
            'event_period_date'        => $ulStartTag . implode('', $eventDateList) . $ulEndTag,
            'event_period_date_time'   => $ulStartTag . implode('', $eventDateTimeList) . $ulEndTag,
            'lesson_space_url_date'       => count($eventLessonSpaceDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventLessonSpaceDateList) . $ulEndTag,
            'lesson_space_url_date_time'  => count($eventLessonSpaceDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventLessonSpaceDateTimeList) . $ulEndTag,
            'zoom_host_url_date'       => count($eventZoomStartDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomStartDateList) . $ulEndTag,
            'zoom_host_url_date_time'  => count($eventZoomStartDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomStartDateTimeList) . $ulEndTag,
            'zoom_join_url_date'       => count($eventZoomJoinDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomJoinDateList) . $ulEndTag,
            'zoom_join_url_date_time'  => count($eventZoomJoinDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomJoinDateTimeList) . $ulEndTag,
            'event_start_date'         => date_i18n($dateFormat, $eventStartDateTime->getTimestamp()),
            'event_end_date'           => date_i18n($dateFormat, $eventEndDateTime->getTimestamp()),
            'event_start_date_time'    => date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $eventStartDateTime->getTimestamp()
            ),
            'event_end_date_time'      => date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $eventEndDateTime->getTimestamp()
            ),
            'event_start_time'         => date_i18n(
                $timeFormat,
                $eventStartDateTime->getTimestamp()
            ),
            'event_end_time'           => date_i18n(
                $timeFormat,
                $eventEndDateTime->getTimestamp()
            ),
        ], $staff);
    }
}
