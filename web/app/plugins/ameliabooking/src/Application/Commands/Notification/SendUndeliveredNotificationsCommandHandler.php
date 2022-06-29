<?php

namespace AmeliaBooking\Application\Commands\Notification;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment\BookingAddedEventHandler;
use Exception;
use Interop\Container\Exception\ContainerException;

/**
 * Class SendUndeliveredNotificationsCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Notification
 */
class SendUndeliveredNotificationsCommandHandler extends CommandHandler
{
    /**
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle()
    {
        $result = new CommandResult();

        /** @var EmailNotificationService $emailNotificationService */
        $emailNotificationService = $this->getContainer()->get('application.emailNotification.service');

        /** @var SMSNotificationService $smsNotificationService */
        $smsNotificationService = $this->getContainer()->get('application.smsNotification.service');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $emailNotificationService->sendUndeliveredNotifications();

        if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
            $smsNotificationService->sendUndeliveredNotifications();
        }


        /** @var Collection $payments */
        $payments = $paymentRepository->getUncompletedActionsForPayments();

        /** @var Payment $payment */
        foreach ($payments->getItems() as $payment) {
            /** @var ReservationServiceInterface $reservationService */
            $reservationService = $this->container->get('application.reservation.service')->get(
                $payment->getEntity()->getValue()
            );

            BookingAddedEventHandler::handle(
                $reservationService->getReservationByPayment($payment),
                $this->container
            );
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Email notifications successfully sent');

        return $result;
    }
}
