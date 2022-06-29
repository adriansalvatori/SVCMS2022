<?php

namespace AmeliaBooking\Application\Services\Booking;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event as iCalEvent;
use \Eluceo\iCal\Property\Event\Organizer as iCalOrganizer;
use AmeliaBooking\Infrastructure\Common\Container;
use Exception;
use Interop\Container\Exception\ContainerException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;

/**
 * Class IcsApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class IcsApplicationService
{
    private $container;

    /**
     * IcsApplicationService constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @param int    $id
     * @param array  $recurring
     * @param bool   $separateCalendars
     * @param string $token
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getIcsData($type, $id, $recurring, $separateCalendars, $token = null)
    {
        $type = $type ?: Entities::APPOINTMENT;

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($type);

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.{$type}.service");

        /** @var Appointment|Event $reservation */
        $reservation = $reservationService->getReservationByBookingId((int)$id);

        /** @var CustomerBooking $booking */
        $booking = $reservation->getBookings()->getItem((int)$id);

        if ($token) {
            $bookingToken = $bookingRepository->getToken((int)$id);

            if (empty($bookingToken['token']) || $bookingToken['token'] !== $token) {
                throw new AccessDeniedException('You are not allowed to perform this action');
            }
        }

        $icsDescriptionData = $settingsService->getCategorySettings('ics');

        $description = !empty($icsDescriptionData['description'][$type]) ?
            $icsDescriptionData['description'][$type] : '';

        $descriptionTr = '';

        if (!empty($icsDescriptionData['description']['translations']) && $booking->getInfo()) {
            $descriptionTr = $helperService->getBookingTranslation(
                $booking->getInfo()->getValue(),
                json_encode($icsDescriptionData['description']['translations']),
                $type
            ) ?: $description;
        }

        /** @var Service|Event $reservation */
        $bookable = null;

        /** @var int $userId */
        $userId = $type === Entities::APPOINTMENT ? $reservation->getProviderId()->getValue() : null;

        /** @var Provider $provider */
        $provider = $type === Entities::APPOINTMENT ? $userRepository->getById($userId) : null;

        $locationName = '';

        switch ($type) {
            case Entities::APPOINTMENT:
                /** @var Service $bookable */
                $bookable = $reservationService->getBookableEntity(
                    [
                        'serviceId' => $reservation->getServiceId()->getValue(),
                        'providerId' => $reservation->getProviderId()->getValue()
                    ]
                );

                /** @var Location $location */
                $location = $reservation->getLocationId() ?
                    $locationRepository->getById($reservation->getLocationId()->getValue()) : null;

                $locationName = $location ? $location->getName()->getValue() : '';

                break;

            case Entities::EVENT:
                /** @var Event $bookable */
                $bookable = $reservationService->getBookableEntity(
                    [
                        'eventId' => $reservation->getId()->getValue()
                    ]
                );

                /** @var Location $location */
                $location = $bookable->getLocationId() ?
                    $locationRepository->getById($bookable->getLocationId()->getValue()) : null;

                if ($location) {
                    $locationName = $location->getName()->getValue();
                } elseif ($bookable->getCustomLocation()) {
                    $locationName = $bookable->getCustomLocation()->getValue();
                }

                break;
        }

        $placeholdersData = $description || $descriptionTr ? $placeholderService->getPlaceholdersData(
            $reservation->toArray(),
            0,
            'email',
            null
        ) : [];

        $periodsData = [
            [
                'name'     => $bookable->getName()->getValue(),
                'nameTr'   => $helperService->getBookingTranslation(
                    $booking->getInfo() ? $booking->getInfo()->getValue() : null,
                    $bookable->getTranslations() ? $bookable->getTranslations()->getValue() : null,
                    'name'
                ) ?: $bookable->getName()->getValue(),
                'location' => $locationName,
                'periods'  => $reservationService->getBookingPeriods($reservation, $booking, $bookable),
                'description'     => $description ? $placeholderService->applyPlaceholders(
                    $description,
                    $placeholdersData
                ) : '',
                'descriptionTr'   => $descriptionTr ? $placeholderService->applyPlaceholders(
                    $descriptionTr,
                    $placeholdersData
                ) : '',
            ]
        ];

        $recurring = $recurring ?: [];

        foreach ($recurring as $recurringId) {
            /** @var Appointment $recurringReservation */
            $recurringReservation = $reservationService->getReservationByBookingId((int)$recurringId);

            /** @var CustomerBooking $recurringBooking */
            $recurringBooking = $recurringReservation->getBookings()->getItem(
                (int)$recurringId
            );

            /** @var Service $bookableRecurring */
            $bookableRecurring = $reservationService->getBookableEntity(
                [
                    'serviceId' => $recurringReservation->getServiceId()->getValue(),
                    'providerId' => $recurringReservation->getProviderId()->getValue()
                ]
            );

            /** @var Location $recurringLocation */
            $recurringLocation = $recurringReservation->getLocationId() ?
                $locationRepository->getById($recurringReservation->getLocationId()->getValue()) : null;

            $locationName = $recurringLocation ? $recurringLocation->getName()->getValue() : '';

            $recurringPlaceholdersData = $description || $descriptionTr ? $placeholderService->getPlaceholdersData(
                $recurringReservation->toArray(),
                0,
                'email',
                null
            ) : [];

            $periodsData[] = [
                'name'     => $bookableRecurring->getName()->getValue(),
                'nameTr'   => $helperService->getBookingTranslation(
                    $recurringBooking->getInfo() ? $recurringBooking->getInfo()->getValue() : null,
                    $bookableRecurring->getTranslations() ? $bookableRecurring->getTranslations()->getValue() : null,
                    'name'
                ) ?: $bookableRecurring->getName()->getValue(),
                'location' => $locationName,
                'periods'  => $reservationService->getBookingPeriods(
                    $recurringReservation,
                    $recurringBooking,
                    $bookableRecurring
                ),
                'description'     => $description ? $placeholderService->applyPlaceholders(
                    $description,
                    $recurringPlaceholdersData
                ) : '',
                'descriptionTr'   => $descriptionTr ? $placeholderService->applyPlaceholders(
                    $descriptionTr,
                    $recurringPlaceholdersData
                ) : '',
            ];
        }

        return [
            'original'   => $this->getCalendar($periodsData, $separateCalendars, false, $provider),
            'translated' => $this->getCalendar($periodsData, $separateCalendars, true, $provider),
        ];
    }

    /**
     * @param array    $periodsData
     * @param bool     $separateCalendars
     * @param bool     $isTranslation
     * @param Provider $provider
     *
     * @return array
     * @throws Exception
     */
    public function getCalendar($periodsData, $separateCalendars, $isTranslation, $provider)
    {
        $vCalendars = $separateCalendars ? [] : [new Calendar(AMELIA_URL)];

        foreach ($periodsData as $periodData) {
            foreach ($periodData['periods'] as $period) {
                $vEvent = new iCalEvent();

                $vEvent
                    ->setDtStart(new \DateTime($period['start'], new \DateTimeZone('UTC')))
                    ->setDtEnd(new \DateTime($period['end'], new \DateTimeZone('UTC')))
                    ->setSummary(!$isTranslation ? $periodData['name'] : $periodData['nameTr']);

                if ($provider) {
                    $vOrganizer = new iCalOrganizer(
                        'MAILTO:' . $provider->getEmail()->getValue(),
                        array('CN' => $provider->getFullName())
                    );

                    $vEvent->setOrganizer($vOrganizer);
                }

                if ($periodData['location']) {
                    $vEvent->setLocation($periodData['location']);
                }

                if ($periodData['description'] || $periodData['descriptionTr']) {
                    $vEvent->setDescription(
                        !$isTranslation ? $periodData['description'] : $periodData['descriptionTr']
                    );
                }

                if ($separateCalendars) {
                    $vCalendar = new Calendar(AMELIA_URL);

                    $vCalendar->addComponent($vEvent);

                    $vCalendars[] = $vCalendar;
                } else {
                    $vCalendars[0]->addComponent($vEvent);
                }
            }
        }

        $result = [];

        foreach ($vCalendars as $index => $vCalendar) {
            $result[] = [
                'name'    => sizeof($vCalendars) === 1 ? 'cal.ics' : 'cal' . ($index + 1) . '.ics',
                'type'    => 'text/calendar; charset=utf-8',
                'content' => $vCalendar->render()
            ];
        }

        return $result;
    }
}
