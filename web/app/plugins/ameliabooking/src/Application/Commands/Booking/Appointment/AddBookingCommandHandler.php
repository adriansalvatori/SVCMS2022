<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AddBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class AddBookingCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'bookings',
    ];

    /**
     * @param AddBookingCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(AddBookingCommand $command)
    {
        $this->checkMandatoryFields($command);

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(
            $command->getField('type') ?: Entities::APPOINTMENT
        );

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        $appointmentData = $bookingAS->getAppointmentData($command->getFields());

        $validateCoupon = true;

        if ($command->getField('validateCoupon') === false &&
            $this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::COUPONS)
        ) {
            $validateCoupon = false;
        }

        $reservation = $reservationService->getNew(
            $validateCoupon,
            true,
            true
        );

        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');

        if ($command->getToken() &&
            $command->getPage() === 'cabinet' &&
            $command->getCabinetType() === Entities::PROVIDER
        ) {
            try {
                /** @var AbstractUser $user */
                $user = $userAS->authorization(
                    $command->getToken(),
                    Entities::PROVIDER
                );

                $reservation->setLoggedInUser($user);
            } catch (AuthorizationException $e) {
                $result = new CommandResult();

                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setData(
                    [
                        'reauthorize' => true
                    ]
                );

                return $result;
            }
        }

        if (!empty($appointmentData['bookings'][0]['packageCustomerService']['id']) && $command->getToken()) {
            try {
                /** @var AbstractUser $user */
                $user = $userAS->authorization(
                    $command->getToken(),
                    Entities::CUSTOMER
                );

                $reservation->setLoggedInUser($user);
            } catch (AuthorizationException $e) {
                $result = new CommandResult();

                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setData(
                    [
                        'reauthorize' => true
                    ]
                );

                return $result;
            }

            if ($user->getId()->getValue() !== (int)$appointmentData['bookings'][0]['customer']['id']) {
                $result = new CommandResult();

                $result->setResult(CommandResult::RESULT_ERROR);

                return $result;
            }

            $appointmentData['payment'] = null;

            $appointmentData['isCabinetBooking'] = true;
        } else {
            $appointmentData['isCabinetBooking'] = false;

            unset($appointmentData['bookings'][0]['packageCustomerService']['id']);
        }

        $result = $reservationService->processRequest($appointmentData, $reservation, true);

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        if ($settingsService->getSetting('general', 'runInstantPostBookingActions')) {
            $reservationService->runPostBookingActions($result);
        }

        if ($result->getResult() === CommandResult::RESULT_SUCCESS && $reservation) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            /** @var AbstractUser $customer */
            $customer = $reservation->getCustomer();

            if ($customer && $customer->getEmail() && $customer->getEmail()->getValue()) {
                $data = $result->getData();

                $data['customerCabinetUrl'] = $helperService->getCustomerCabinetUrl(
                    $customer->getEmail()->getValue(),
                    'email',
                    null,
                    null,
                    $reservation->getLocale() ? $reservation->getLocale()->getValue() : ''
                );

                $result->setData($data);
            }
        }

        return $result;
    }
}
