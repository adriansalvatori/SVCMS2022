<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\WebHook;

use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use Exception;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class WebHookApplicationService
 *
 * @package AmeliaBooking\Application\Services\WebHook
 */
class WebHookApplicationService
{
    /** @var Container $container */
    private $container;

    /**
     * WebHookApplicationService constructor.
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
     * @param string   $action
     * @param array    $reservation
     * @param array    $bookings
     *
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws Exception
     */
    public function process($action, $reservation, $bookings)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        do_action('Amelia' . ucwords($action), $reservation, $bookings, $this->container);

        $webHooks = $settingsService->getCategorySettings('webHooks');

        $hasHooks = false;

        foreach ((array)$webHooks as $webHook) {
            if ($webHook['action'] === $action && $webHook['type'] === $reservation['type']) {
                $hasHooks = true;
                break;
            }
        }

        if ($hasHooks) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            $reservationEntity = $bookingApplicationService->getReservationEntity($reservation);

            $affectedBookingEntitiesArray = [];

            foreach ($bookings as $booking) {
                /** @var CustomerBooking $bookingEntity */
                $bookingEntity = $bookingApplicationService->getBookingEntity($booking);

                $bookingEntityArray = $bookingEntity->toArray();

                if (isset($booking['isRecurringBooking'])) {
                    $bookingEntityArray['isRecurringBooking'] = $booking['isRecurringBooking'];

                    $bookingEntityArray['isPackageBooking'] = $booking['isPackageBooking'];
                }

                $affectedBookingEntitiesArray[] = $bookingEntityArray;
            }

            $reservationEntityArray = $reservationEntity->toArray();

            switch ($reservation['type']) {
                case Entities::APPOINTMENT:
                    if (isset($reservationEntityArray['provider']['googleCalendar']['token'])) {
                        unset($reservationEntityArray['provider']['googleCalendar']['token']);
                    }

                    if (isset($reservationEntityArray['provider']['outlookCalendar']['token'])) {
                        unset($reservationEntityArray['provider']['outlookCalendar']['token']);
                    }

                    break;

                case Entities::EVENT:
                    break;
            }

            foreach ($affectedBookingEntitiesArray as $key => $booking) {
                if ($booking['customFields'] && json_decode($booking['customFields'], true) !== null) {
                    $affectedBookingEntitiesArray[$key]['customFields'] = json_decode($booking['customFields'], true);
                }

                $affectedBookingEntitiesArray[$key]['cancelUrl'] = !empty($booking['token']) ?
                    AMELIA_ACTION_URL .
                    '/bookings/cancel/' . $booking['id'] .
                    '&token=' . $booking['token'] .
                    "&type={$reservation['type']}" : '';

                $info = !empty($booking['info']) ?
                    json_decode($booking['info'], true) : null;

                $affectedBookingEntitiesArray[$key]['customerPanelUrl'] = $helperService->getCustomerCabinetUrl(
                    $booking['customer']['email'],
                    'email',
                    null,
                    null,
                    isset($info['locale']) ? $info['locale'] : ''
                );

                $affectedBookingEntitiesArray[$key]['infoArray'] = $info;
            }

            foreach ($reservationEntityArray['bookings'] as $key => $booking) {
                if ($booking['customFields'] && json_decode($booking['customFields'], true) !== null) {
                    $reservationEntityArray['bookings'][$key]['customFields'] = json_decode(
                        $booking['customFields'],
                        true
                    );
                }

                $reservationEntityArray['bookings'][$key]['cancelUrl'] = !empty($booking['token']) ?
                    AMELIA_ACTION_URL .
                    '/bookings/cancel/' . $booking['id'] .
                    '&token=' . $booking['token'] .
                    "&type={$reservation['type']}" : '';

                $info = !empty($booking['info']) ?
                    json_decode($booking['info'], true) : null;

                $reservationEntityArray['bookings'][$key]['customerPanelUrl'] = $helperService->getCustomerCabinetUrl(
                    $booking['customer']['email'],
                    'email',
                    null,
                    null,
                    isset($info['locale']) ? $info['locale'] : ''
                );

                $reservationEntityArray['bookings'][$key]['infoArray'] = $info;
            }

            foreach ($webHooks as $webHook) {
                if ($webHook['action'] === $action && $webHook['type'] === $reservation['type']) {
                    $ch = curl_init($webHook['url']);

                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    curl_setopt(
                        $ch,
                        CURLOPT_POSTFIELDS,
                        json_encode(
                            [
                                $reservationEntity->getType()->getValue() => $reservationEntityArray,
                                Entities::BOOKINGS                        => $affectedBookingEntitiesArray
                            ],
                            JSON_FORCE_OBJECT
                        )
                    );

                    curl_exec($ch);

                    curl_close($ch);
                }
            }
        }
    }
}
