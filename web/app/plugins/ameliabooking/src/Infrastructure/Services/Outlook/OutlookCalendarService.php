<?php

namespace AmeliaBooking\Infrastructure\Services\Outlook;

use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Application\Services\User\ProviderApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\EventPeriod;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Factory\Outlook\OutlookCalendarFactory;
use AmeliaBooking\Domain\Factory\User\ProviderFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\Label;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventPeriodsRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\CustomerRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentAddedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentDeletedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentEditedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentStatusUpdatedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\AppointmentTimeUpdatedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\BookingAddedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\BookingCanceledEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event\EventAddedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event\EventEditedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event\EventStatusUpdatedEventHandler;
use Exception;
use Interop\Container\Exception\ContainerException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Attendee;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\Calendar;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\Entity;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\FreeBusyStatus;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Location;
use Microsoft\Graph\Model\PatternedRecurrence;
use Microsoft\Graph\Model\PhysicalAddress;
use Microsoft\Graph\Model\Recipient;
use Microsoft\Graph\Model\RecurrencePattern;
use Microsoft\Graph\Model\RecurrencePatternType;
use Microsoft\Graph\Model\RecurrenceRange;
use Microsoft\Graph\Model\RecurrenceRangeType;
use Microsoft\Graph\Model\SingleValueLegacyExtendedProperty;
use WP_Error;

/**
 * Class OutlookCalendarService
 *
 * @package AmeliaBooking\Infrastructure\Services\Outlook
 */
class OutlookCalendarService
{
    /** @var Container $container */
    private $container;

    /** @var Graph */
    private $graph;

    /** @var SettingsService */
    private $settings;

    private static $providersOutlookEvents = [];

    const GUID = '{66f5a359-4659-4830-9070-00049ec6ac6e}';

    /**
     * OutlookCalendarService constructor.
     *
     * @param Container $container
     *
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->settings  = $this->container->get('domain.settings.service')->getCategorySettings('outlookCalendar');

        $this->graph = new Graph();
    }

    /**
     * Create a URL to obtain user authorization.
     *
     * @param $providerId
     *
     * @return string
     *
     * @throws ContainerException
     */
    public function createAuthUrl($providerId)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var array $outlookSettings */
        $outlookSettings = $settingsService->getCategorySettings('outlookCalendar');

        return add_query_arg(
            urlencode_deep(
                [
                'client_id'     => $outlookSettings['clientID'],
                'response_type' => 'code',
                'redirect_uri'  => str_replace('http://', 'https://', $outlookSettings['redirectURI']),
                'scope'         => 'offline_access calendars.readwrite',
                'response_mode' => 'query',
                'state'         => 'amelia-outlook-calendar-auth-' . $providerId,
                ]
            ),
            'https://login.microsoftonline.com/common/oauth2/v2.0/authorize'
        );
    }

    public static function handleCallback()
    {
        if (isset($_REQUEST['code'], $_REQUEST['state']) && !isset($_REQUEST['scope']) && !isset($_REQUEST['type'])) {
            wp_redirect(
                add_query_arg(
                    urlencode_deep(
                        [
                        'code'  => esc_attr($_REQUEST['code']),
                        'state' => esc_attr($_REQUEST['state']),
                        'type'  => 'outlook'
                        ]
                    ),
                    admin_url('admin.php?page=wpamelia-employees')
                )
            );
        }
    }

    /**
     * @param $authCode
     * @param $redirectUri
     *
     * @return bool
     */
    public function fetchAccessTokenWithAuthCode($authCode, $redirectUri)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var array $outlookSettings */
        $outlookSettings = $settingsService->getCategorySettings('outlookCalendar');

        $response = wp_remote_post(
            'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            [
                'timeout' => 25,
                'body'    => [
                    'client_id'     => $outlookSettings['clientID'],
                    'client_secret' => $outlookSettings['clientSecret'],
                    'grant_type'    => 'authorization_code',
                    'code'          => $authCode,
                    'redirect_uri'  => str_replace(
                        'http://',
                        'https://',
                        empty($redirectUri) ? $outlookSettings['redirectURI'] : explode('?', $redirectUri)[0]
                    ),
                    'scope'         => 'offline_access calendars.readwrite',
                ]
            ]
        );

        if ($response instanceof WP_Error) {
            return false;
        }

        if ($response['response']['code'] !== 200) {
            return false;
        }

        $decodedToken = json_decode($response['body'], true);

        $decodedToken['created'] = time();

        return json_encode($decodedToken);
    }

    /**
     * @param Provider $provider
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     */
    public function authorizeProvider($provider)
    {
        $token = $provider->getOutlookCalendar()->getToken()->getValue();

        if ($this->isAccessTokenExpired($token)) {
            $token = $this->refreshToken($provider, $token);
        }

        $tokenArray = json_decode($token, true);

        if ($tokenArray && isset($tokenArray['access_token'])) {
            $this->graph->setAccessToken($tokenArray['access_token']);
        } else {
            throw new \Exception();
        }
    }

    /**
     * @param Provider $provider
     *
     * @return array
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function listCalendarList($provider)
    {
        $calendars = [];

        if ($provider->getOutlookCalendar()) {
            $this->authorizeProvider($provider);

            $outlookCalendars = $this->graph
                ->createCollectionRequest('GET', '/me/calendars')
                ->setReturnType(Calendar::class)
                ->setPageSize(100)
                ->getPage();

            /** @var Calendar $calendar */
            foreach ($outlookCalendars as $outlookCalendar) {
                if ($outlookCalendar->getCanEdit()) {
                    $calendars[] = [
                        'id'   => $outlookCalendar->getId(),
                        'name' => $outlookCalendar->getName()
                    ];
                }
            }
        }

        return $calendars;
    }

    /**
     * Get Provider's Outlook Calendar ID.
     *
     * @param Provider $provider
     *
     * @return null|string
     * @throws GraphException|ContainerException|QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getProviderOutlookCalendarId($provider)
    {
        // If Outlook Calendar ID is not set, take the primary calendar and save it as Provider's Outlook Calendar ID
        if ($provider->getOutlookCalendar() && $provider->getOutlookCalendar()->getCalendarId()->getValue() === null) {
            $calendarList = $this->listCalendarList($provider);

            /** @var ProviderApplicationService $providerApplicationService */
            $providerApplicationService = $this->container->get('application.user.provider.service');

            $provider->getOutlookCalendar()->setCalendarId(new Label($calendarList[0]['id']));

            $providerApplicationService->updateProviderOutlookCalendar($provider);

            return $provider->getOutlookCalendar()->getCalendarId()->getValue();
        }

        // If Outlook Calendar is set, return it
        if ($provider->getOutlookCalendar() && $provider->getOutlookCalendar()->getCalendarId()->getValue() !== null) {
            return $provider->getOutlookCalendar()->getCalendarId()->getValue();
        }

        return null;
    }

    /**
     * @param Appointment $appointment
     * @param string      $commandSlug
     * @param null|string $oldStatus
     *
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function handleEvent($appointment, $commandSlug, $oldStatus = null)
    {
        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        $appointmentStatus = $appointment->getStatus()->getValue();

        $provider = $providerRepository->getById($appointment->getProviderId()->getValue());

        if ($provider && $provider->getOutlookCalendar() && $provider->getOutlookCalendar()->getCalendarId()->getValue()) {
            $this->authorizeProvider($provider);

            switch ($commandSlug) {
                case AppointmentAddedEventHandler::APPOINTMENT_ADDED:
                case BookingAddedEventHandler::BOOKING_ADDED:
                    if ($appointmentStatus === 'pending' && $this->settings['insertPendingAppointments'] === false) {
                        break;
                    }

                    // Add new appointment or update existing one
                    if (!$appointment->getOutlookCalendarEventId()) {
                        $this->insertEvent($appointment, $provider);
                    } else {
                        $this->updateEvent($appointment, $provider);
                    }

                    break;
                case AppointmentEditedEventHandler::APPOINTMENT_EDITED:
                case AppointmentTimeUpdatedEventHandler::TIME_UPDATED:
                case AppointmentStatusUpdatedEventHandler::APPOINTMENT_STATUS_UPDATED:
                case BookingCanceledEventHandler::BOOKING_CANCELED:
                    if ($appointmentStatus === 'canceled' || $appointmentStatus === 'rejected' ||
                        ($appointmentStatus === 'pending' && $this->settings['insertPendingAppointments'] === false)
                    ) {
                        $this->deleteEvent($appointment, $provider);
                        break;
                    }

                    if ($appointmentStatus === 'approved' && $oldStatus && $oldStatus !== 'approved' &&
                        $this->settings['insertPendingAppointments'] === false
                    ) {
                        $this->insertEvent($appointment, $provider);
                        break;
                    }

                    $this->updateEvent($appointment, $provider);
                    break;
                case AppointmentDeletedEventHandler::APPOINTMENT_DELETED:
                    $this->deleteEvent($appointment, $provider);
                    break;
            }
        }
    }

    /**
     * @param \AmeliaBooking\Domain\Entity\Booking\Event\Event $event
     * @param string $commandSlug
     * @param Collection $periods
     *
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function handleEventPeriod($event, $commandSlug, $periods, $newProviders = null, $removeProviders = null)
    {
        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        if ($event->getOrganizerId()) {
            $provider = $providerRepository->getById($event->getOrganizerId()->getValue());

            if ($provider->getOutlookCalendar() && $provider->getOutlookCalendar()->getCalendarId()) {
                $this->authorizeProvider($provider);

                /** @var EventPeriod $period */
                foreach ($periods->getItems() as $period) {
                    switch ($commandSlug) {
                        case EventAddedEventHandler::EVENT_ADDED:
                        case EventEditedEventHandler::TIME_UPDATED:
                        case EventEditedEventHandler::PROVIDER_CHANGED:
                            if (!$period->getOutlookCalendarEventId()) {
                                $this->insertEvent($event, $provider, $period);
                                break;
                            }

                            $this->updateEvent($event, $provider, $period, $newProviders, $removeProviders);
                            break;
                        case EventEditedEventHandler::EVENT_PERIOD_DELETED:
                            $this->deleteEvent($period, $provider);
                            break;
                        case BookingAddedEventHandler::BOOKING_ADDED:
                        case BookingCanceledEventHandler::BOOKING_CANCELED:
                            $this->updateEvent($event, $provider, $period);
                            break;
                        case EventStatusUpdatedEventHandler::EVENT_STATUS_UPDATED:
                            if ($event->getStatus()->getValue() === 'rejected') {
                                $this->deleteEvent($period, $provider);
                            } else if ($event->getStatus()->getValue() === 'approved') {
                                $this->insertEvent($event, $provider, $period);
                            }
                            break;
                        case EventEditedEventHandler::EVENT_PERIOD_ADDED:
                            $this->insertEvent($event, $provider, $period);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Get providers events within date range
     *
     * @param array $providerArr
     * @param string $dateStart
     * @param string $dateStartEnd
     * @param string $dateEnd
     * @param array $eventIds
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws GraphException
     * @throws Exception
     */
    public function getEvents($providerArr, $dateStart, $dateStartEnd, $dateEnd, $eventIds)
    {
        $finalEvents = [];
        $provider    = ProviderFactory::create($providerArr);
        if ($provider->getOutlookCalendar() && $provider->getOutlookCalendar()->getToken()) {
            $this->authorizeProvider($provider);
            $startDate    = DateTimeService::getCustomDateTimeObject($dateStart);
            $startDateEnd = DateTimeService::getCustomDateTimeObject($dateStartEnd);
            $endDate      = DateTimeService::getCustomDateTimeObject($dateEnd);

            $request = $this->graph->createCollectionRequest(
                'GET',
                sprintf(
                    '/me/calendars/%s/calendarView?startDateTime=%s&endDateTime=%s&$expand=%s&$orderby=%s',
                    $provider->getOutlookCalendar()->getCalendarId()->getValue(),
                    rawurlencode($startDate->format('c')),
                    rawurlencode($endDate->format('c')),
                    rawurlencode(
                        'singleValueExtendedProperties($filter=id eq \'Integer ' .
                        self::GUID . ' Name appointmentId\')'
                    ),
                    rawurlencode('start/dateTime')
                )
            )
                ->setReturnType(Event::class)
                ->setPageSize($this->settings['maximumNumberOfEventsReturned']);

            $events = $request->getPage();

            /** @var Event $event */
            foreach ($events as $event) {
                if ($event->getShowAs()->value() === 'free') {
                    continue;
                }
                $extendedProperties = $event->getSingleValueExtendedProperties();
                if ($extendedProperties !== null) {
                    foreach ($extendedProperties as $extendedProperty) {
                        if ($extendedProperty['id'] === 'Integer ' . self::GUID . ' Name appointmentId' && in_array((int)$extendedProperty['value'], $eventIds)) {
                            continue 2;
                        }
                    }
                }
                $eventStart = DateTimeService::getCustomDateTimeObject($event->getStart()->getDateTime());
                $eventEnd   = DateTimeService::getCustomDateTimeObject($event->getEnd()->getDateTime());

                $eventDateStart = DateTimeService::getCustomDateTimeObject($eventStart->format('Y-m-d') . ' ' . $startDate->format('H:i:s'));
                $eventDateEnd   = DateTimeService::getCustomDateTimeObject($eventEnd->format('Y-m-d') . ' ' . $startDateEnd->format('H:i:s'));

                if ($eventDateEnd <= $eventStart || $eventDateStart >= $eventEnd) {
                    continue;
                }
                $finalEvents[] = $event;
            }
        }

        return $finalEvents;
    }


    /**
     * Create fake appointments in provider's list so that these slots will not be available for booking
     *
     * @param Collection $providers
     * @param int        $excludeAppointmentId
     * @param \DateTime  $startDateTime
     * @param \DateTime  $endDateTime
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     * @throws ContainerException
     */
    public function removeSlotsFromOutlookCalendar(
        $providers,
        $excludeAppointmentId,
        $startDateTime,
        $endDateTime
    ) {
        if ($this->settings['removeOutlookCalendarBusySlots'] === true) {
            foreach ($providers->keys() as $providerKey) {
                /** @var Provider $provider */
                $provider = $providers->getItem($providerKey);

                if ($provider->getOutlookCalendar()) {
                    if (!array_key_exists($provider->getId()->getValue(), self::$providersOutlookEvents)) {
                        $this->authorizeProvider($provider);

                        $request = $this->graph->createCollectionRequest(
                            'GET',
                            sprintf(
                                '/me/calendars/%s/calendarView?startDateTime=%s&endDateTime=%s&$expand=%s&$orderby=%s',
                                $provider->getOutlookCalendar()->getCalendarId()->getValue(),
                                rawurlencode($startDateTime->format('c')),
                                rawurlencode($endDateTime->format('c')),
                                rawurlencode(
                                    'singleValueExtendedProperties($filter=id eq \'Integer ' .
                                    self::GUID . ' Name appointmentId\')'
                                ),
                                rawurlencode('start/dateTime')
                            )
                        )
                            ->setReturnType(Event::class)
                            ->setPageSize($this->settings['maximumNumberOfEventsReturned']);

                        $events = $request->getPage();
                        self::$providersOutlookEvents[$provider->getId()->getValue()] = $events;
                    } else {
                        $events = self::$providersOutlookEvents[$provider->getId()->getValue()];
                    }

                    /** @var Event $event */
                    foreach ($events as $event) {
                        // Continue if event is set to "Free"
                        if ($event->getShowAs() !== null && $event->getShowAs()->is(FreeBusyStatus::FREE)) {
                            continue;
                        }

                        $extendedProperties = $event->getSingleValueExtendedProperties();
                        if ($extendedProperties !== null) {
                            foreach ($extendedProperties as $extendedProperty) {
                                if ($extendedProperty['id'] === 'Integer ' . self::GUID . ' Name appointmentId') {
                                    continue;
                                }
                            }
                        }

                        $eventStartString = DateTimeService::getCustomDateTimeFromUtc($event->getStart()->getDateTime());

                        $eventEndString = DateTimeService::getCustomDateTimeFromUtc($event->getEnd()->getDateTime());

                        /** @var Appointment $appointment */
                        $appointment = AppointmentFactory::create(
                            [
                                'bookingStart'       => $eventStartString,
                                'bookingEnd'         => $eventEndString,
                                'notifyParticipants' => false,
                                'serviceId'          => 0,
                                'providerId'         => $provider->getId()->getValue(),
                            ]
                        );

                        $provider->getAppointmentList()->addItem($appointment);
                    }
                }
            }
        }
    }

    /**
     * @param Appointment|\AmeliaBooking\Domain\Entity\Booking\Event\Event $appointment
     * @param Provider    $provider
     * @param EventPeriod $period
     *
     * @return bool
     *
     * @throws ContainerException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    private function insertEvent($appointment, $provider, $period = null)
    {
        $event = $this->createEvent($appointment, $provider, $period);

        try {
            $event = $this->graph->createRequest(
                'POST',
                sprintf(
                    '/me/calendars/%s/events',
                    $provider->getOutlookCalendar()->getCalendarId()->getValue()
                )
            )->attachBody($event)->setReturnType(get_class($event))->execute();
        } catch (GraphException $e) {
            return false;
        }

        if ($period) {
            /** @var EventPeriodsRepository $eventPeriodsRepository */
            $eventPeriodsRepository = $this->container->get('domain.booking.event.period.repository');
            $period->setOutlookCalendarEventId(new Label($event->getId()));
            $eventPeriodsRepository->updateFieldById($period->getId()->getValue(), $period->getOutlookCalendarEventId()->getValue(), 'outlookCalendarEventId');
        } else {
            /** @var AppointmentRepository $appointmentRepository */
            $appointmentRepository = $this->container->get('domain.booking.appointment.repository');
            $appointment->setOutlookCalendarEventId(new Label($event->getId()));
            $appointmentRepository->update($appointment->getId()->getValue(), $appointment);
        }

        return true;
    }

    /**
     * Update an Event in Outlook Calendar.
     *
     * @param Appointment|\AmeliaBooking\Domain\Entity\Booking\Event\Event $appointment
     * @param Provider    $provider
     * @param EventPeriod $period
     * @param array $newProviders
     * @param array $removeProviders
     *
     * @return bool
     * @throws ContainerException
     * @throws QueryExecutionException
     */
    private function updateEvent($appointment, $provider, $period = null, $newProviders = null, $removeProviders = null)
    {
        $entity = $period ?: $appointment;
        if ($entity->getOutlookCalendarEventId()) {
            $event = $this->createEvent($appointment, $provider, $period, $newProviders, $removeProviders);
            try {
                $this->graph->createRequest(
                    'PATCH',
                    sprintf(
                        '/me/calendars/%s/events/%s',
                        $provider->getOutlookCalendar()->getCalendarId()->getValue(),
                        $entity->getOutlookCalendarEventId()->getValue()
                    )
                )->attachBody($event)->setReturnType(get_class($event))->execute();
            } catch (GraphException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete an Event from Outlook Calendar.
     *
     * @param Appointment|EventPeriod $appointment
     * @param Provider    $provider
     *
     * @throws GraphException
     * @throws QueryExecutionException
     */
    private function deleteEvent($appointment, $provider)
    {
        if ($appointment->getOutlookCalendarEventId()) {
            $this->graph->createRequest(
                'DELETE',
                sprintf(
                    '/me/calendars/%s/events/%s',
                    $provider->getOutlookCalendar()->getCalendarId()->getValue(),
                    $appointment->getOutlookCalendarEventId()->getValue()
                )
            )->execute();

            $appointment->setOutlookCalendarEventId(null);

            /** @var AppointmentRepository $repository */
            $repository = $this->container->get('domain.booking.appointment.repository');

            if (is_a($appointment, EventPeriod::class)) {
                /** @var EventPeriodsRepository $repository */
                $repository = $this->container->get('domain.booking.event.period.repository');
            }
            $repository->updateFieldById($appointment->getId()->getValue(), null, 'outlookCalendarEventId');
        }
    }

    /**
     * Create and return Outlook Calendar Event Object filled with appointments data.
     *
     * @param Appointment|\AmeliaBooking\Domain\Entity\Booking\Event\Event $appointment
     * @param Provider    $provider
     * @param EventPeriod $period
     *
     * @return Event
     *
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    private function createEvent($appointment, $provider, $period = null, $newProviders = null, $removeProviders = null)
    {
        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        $type = $period ? Entities::EVENT : Entities::APPOINTMENT;
        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.{$type}.service");

        $appointmentLocationId = $appointment->getLocationId() ? $appointment->getLocationId()->getValue() : null;
        $providerLocationId    = $provider->getLocationId() ? $provider->getLocationId()->getValue() : null;

        $locationId = $appointmentLocationId ?: $providerLocationId;

        /** @var \AmeliaBooking\Domain\Entity\Location\Location $location */
        $location = $locationId ? $locationRepository->getById($locationId) : null;

        $placeholderData = $placeholderService->getPlaceholdersData($appointment->toArray());

        $start = $period ?  clone $period->getPeriodStart()->getValue() : clone $appointment->getBookingStart()->getValue();

        if ($period) {
            $time = (int)$period->getPeriodEnd()->getValue()->format('H')*60 + (int)$period->getPeriodEnd()->getValue()->format('i');
            $end  = DateTimeService::getCustomDateTimeObject(
                $start->format('Y-m-d')
            )->add(new \DateInterval('PT' . $time . 'M'));
        } else {
            $end = clone $appointment->getBookingEnd()->getValue();
        }

        if ($this->settings['includeBufferTimeOutlookCalendar'] === true && $type === Entities::APPOINTMENT) {
            $timeBefore = $appointment->getService()->getTimeBefore() ?
                $appointment->getService()->getTimeBefore()->getValue() : 0;
            $timeAfter  = $appointment->getService()->getTimeAfter() ?
                $appointment->getService()->getTimeAfter()->getValue() : 0;
            $start->modify('-' . $timeBefore . ' second');
            $end->modify('+' . $timeAfter . ' second');
        }

        $startDateTime = new DateTimeTimeZone();
        $startDateTime->setDateTime($start)->setTimeZone('UTC');
        $endDateTime = new DateTimeTimeZone();
        $endDateTime->setDateTime($end)->setTimeZone('UTC');

        $event = new Event();

        $event->setStart($startDateTime);
        $event->setEnd($endDateTime);

        $event->setSubject(
            $placeholderService->applyPlaceholders(
                $period ? $this->settings['title']['event'] : $this->settings['title']['appointment'],
                $placeholderData
            )
        );

        $description = $placeholderService->applyPlaceholders(
            $period ? $this->settings['description']['event'] : $this->settings['description']['appointment'],
            $placeholderData
        );
        $body        = new ItemBody();
        $body->setContentType(new BodyType(BodyType::TEXT))->setContent($description);
        $event->setBody($body);

        if ($location) {
            $outlookLocation = new Location();
            $outlookLocation->setDisplayName($location->getName()->getValue());
            $address = new PhysicalAddress();
            $address->setStreet($location->getAddress()->getValue());
            $outlookLocation->setAddress($address);
            $event->setLocation($outlookLocation);
        }

        $property = new SingleValueLegacyExtendedProperty();
        $property
            ->setId('Integer ' . self::GUID . ' Name appointmentId')
            ->setValue((string)$appointment->getId()->getValue());
        $event->setSingleValueExtendedProperties([$property]);

        $outlookAttendees = new Attendee($this->getAttendees($appointment, $newProviders, $removeProviders));
        $event->setAttendees($outlookAttendees);

        if ($period && $period->getPeriodStart()->getValue()->diff($period->getPeriodEnd()->getValue())->format('%a') !== '0') {
            $recData = [
                "pattern" => [
                    "type" => "daily",
                    "interval" => 1
                ],
                "range" => [
                    "type" => "endDate",
                    "startDate" => $period->getPeriodStart()->getValue()->format('Y-m-d'),
                    "endDate" => $period->getPeriodEnd()->getValue()->format('Y-m-d'),
                    "recurrenceTimeZone" => $period->getPeriodStart()->getValue()->getTimezone()->getName()
                ]
            ];
            $event->setRecurrence($recData);
        }

        return $event;
    }

    /**
     * Get All Attendees that need to be added in Outlook Calendar Event based on "addAttendees" Settings.
     *
     * @param Appointment|\AmeliaBooking\Domain\Entity\Booking\Event\Event $appointment
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function getAttendees($appointment, $newProviders = null, $removeProviders = null)
    {
        $attendees = [];

        if ($this->settings['addAttendees'] === true) {
            /** @var ProviderRepository $providerRepository */
            $providerRepository = $this->container->get('domain.users.providers.repository');

            $providers = is_a($appointment, Appointment::class) ? [$providerRepository->getById($appointment->getProviderId()->getValue())] : $appointment->getProviders()->getItems();

            if ($newProviders) {
                $providers = array_merge($providers, $newProviders);
            }
            if ($removeProviders) {
                $providersRemoveIds = array_map(
                    function ($value) {
                        return $value->getId()->getValue();
                    },
                    $removeProviders
                );
            }

            foreach ($providers as $provider) {
                if (empty($providersRemoveIds) || !in_array($provider->getId()->getValue(), $providersRemoveIds)) {
                    $attendees[] = [
                        'emailAddress'    => [
                            'name'    => $provider->getFirstName()->getValue() . ' ' . $provider->getLastName()->getValue(),
                            'address' => $provider->getEmail()->getValue(),
                        ],
                        'type'            => 'required',
                        'status'  => [
                            'response' => 'accepted',
                            'time' => (new \DateTime('now'))->format(DATE_ATOM)
                        ]
                    ];
                }
            }

            /** @var CustomerRepository $customerRepository */
            $customerRepository = $this->container->get('domain.users.customers.repository');

            $bookings = $appointment->getBookings()->getItems();

            /** @var CustomerBooking $booking */
            foreach ($bookings as $booking) {
                $bookingStatus = $booking->getStatus()->getValue();

                if ($bookingStatus === 'approved' ||
                    ($bookingStatus === 'pending' && $this->settings['insertPendingAppointments'] === true)
                ) {
                    $customer = $customerRepository->getById($booking->getCustomerId()->getValue());

                    if ($customer->getEmail()->getValue()) {
                        $attendees[] = [
                            'emailAddress' => [
                                'name'    =>
                                    $customer->getFirstName()->getValue() . ' ' . $customer->getLastName()->getValue(),
                                'address' => $customer->getEmail()->getValue(),
                            ],
                            'type'         => 'required',
                            'status'  => [
                                'response' => 'accepted',
                                'time' => (new \DateTime('now'))->format(DATE_ATOM)
                            ]
                        ];
                    }
                }
            }
        }

        return $attendees;
    }

    /**
     * Refresh Provider's Token if it is expired and update it in database.
     *
     * @param Provider $provider
     * @param          $token
     *
     * @return bool
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    private function refreshToken($provider, $token)
    {
        /** @var ProviderApplicationService $providerApplicationService */
        $providerApplicationService = $this->container->get('application.user.provider.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var array $outlookSettings */
        $outlookSettings = $settingsService->getCategorySettings('outlookCalendar');

        $decodedToken = json_decode($token, true);

        $response = wp_remote_post(
            'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            array(
            'timeout' => 25,
            'body'    => array(
                'client_id'     => $outlookSettings['clientID'],
                'client_secret' => $outlookSettings['clientSecret'],
                'grant_type'    => 'refresh_token',
                'refresh_token' => $decodedToken['refresh_token'],
                'redirect_uri'  => str_replace('http://', 'https://', $outlookSettings['redirectURI']),
                'scope'         => 'offline_access calendars.readwrite',
            )
            )
        );

        if ($response instanceof WP_Error) {
            return false;
        }

        if ($response['response']['code'] !== 200) {
            return false;
        }

        $decodedToken            = json_decode($response['body'], true);
        $decodedToken['created'] = time();

        $encodedToken = json_encode($decodedToken);

        $provider->setOutlookCalendar(
            OutlookCalendarFactory::create(
                [
                'id'         => $provider->getOutlookCalendar()->getId()->getValue(),
                'token'      => $encodedToken,
                'calendarId' => $provider->getOutlookCalendar()->getCalendarId()->getValue()
                ]
            )
        );

        $providerApplicationService->updateProviderOutlookCalendar($provider);

        return $encodedToken;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private function isAccessTokenExpired($token)
    {
        $decodedToken = json_decode($token, true);

        if (!isset($decodedToken['created'])) {
            return true;
        }

        return ($decodedToken['created'] + ($decodedToken['expires_in'] - 30)) < time();
    }

    /**
     * @param DateTimeTimeZone $eventStart
     * @param DateTimeTimeZone $eventEnd
     *
     * @return array
     *
     * @throws Exception
     */
    private function removeTimeBasedEvents($eventStart, $eventEnd)
    {
        $timesToRemove = [];

        $daysBetweenStartAndEnd = (int)DateTimeService::getCustomDateTimeObjectFromUtc($eventEnd->getDateTime())
            ->diff(DateTimeService::getCustomDateTimeObjectFromUtc($eventStart->getDateTime()))->format('%a');

        // If event is in the same day, or not
        if ($daysBetweenStartAndEnd === 0) {
            $timesToRemove[] = [
                'eventStartDateTime' => DateTimeService::getCustomDateTimeFromUtc($eventStart->getDateTime()),
                'eventEndDateTime'   => DateTimeService::getCustomDateTimeFromUtc($eventEnd->getDateTime())
            ];
        } else {
            for ($i = 0; $i <= $daysBetweenStartAndEnd; $i++) {
                $startDateTime = DateTimeService::getCustomDateTimeObjectFromUtc(
                    $eventStart->getDateTime()
                )->modify('+' . $i . ' days');

                $timesToRemove[] = [
                    'eventStartDateTime' => $i === 0 ?
                        $startDateTime->format('Y-m-d H:i:s') :
                        $startDateTime->format('Y-m-d') . ' 00:00:01',
                    'eventEndDateTime'   => $i === $daysBetweenStartAndEnd ?
                        DateTimeService::getCustomDateTimeFromUtc($eventEnd->getDateTime()) :
                        $startDateTime->format('Y-m-d') . ' 23:59:59'
                ];
            }
        }

        return $timesToRemove;
    }
}
