<?php

namespace AmeliaBooking\Application\Commands\PaymentGateway;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Booking\Reservation;
use AmeliaBooking\Domain\Entity\Cache\Cache;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Factory\Cache\CacheFactory;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\PaymentType;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Cache\CacheRepository;
use AmeliaBooking\Infrastructure\Services\Payment\MollieService;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class MolliePaymentCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\PaymentGateway
 */
class MolliePaymentCommandHandler extends CommandHandler
{
    public $mandatoryFields = [
        'bookings',
        'payment'
    ];

    /**
     * @param MolliePaymentCommand $command
     *
     * @return CommandResult
     * @throws QueryExecutionException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws ContainerException
     */
    public function handle(MolliePaymentCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $type = $command->getField('type') ?: Entities::APPOINTMENT;

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($type);

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var MollieService $paymentService */
        $paymentService = $this->container->get('infrastructure.payment.mollie.service');

        /** @var CacheRepository $cacheRepository */
        $cacheRepository = $this->container->get('domain.cache.repository');


        /** @var Reservation $reservation */
        $reservation = $reservationService->getNew(true, true, true);

        $reservationService->processBooking(
            $result,
            $bookingAS->getAppointmentData($command->getFields()),
            $reservation,
            false
        );

        if ($result->getResult() === CommandResult::RESULT_ERROR) {
            return $result;
        }


        $paymentAmount = $reservationService->getReservationPaymentAmount($reservation);

        if (!$paymentAmount) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['payment_error']);
            $result->setData(
                [
                    'paymentSuccessful' => false,
                    'onSitePayment'     => true
                ]
            );

            return $result;
        }


        $token = new Token();

        /** @var Cache $cache */
        $cache = CacheFactory::create(
            [
                'name' => $token->getValue(),
                'data' => json_encode(
                    [
                        'status'  => null,
                        'request' => $command->getField('componentProps'),
                    ]
                ),
            ]
        );

        $cacheId = $cacheRepository->add($cache);

        $cache->setId(new Id($cacheId));


        $additionalInformation = $paymentAS->getBookingInformationForPaymentSettings(
            $reservation,
            PaymentType::MOLLIE
        );

        $identifier = $cacheId . '_' . $token->getValue() . '_' . $type;

        $response = $paymentService->execute(
            [
                'returnUrl'   => $command->getField('returnUrl') . '?ameliaCache=' . $identifier,
                'notifyUrl'   => AMELIA_ACTION_URL . '/payment/mollie/notify&name=' . $identifier,
                'amount'      => $paymentAmount,
                'locale'      => str_replace('-', '_', $reservation->getLocale()->getValue()),
                'description' => $additionalInformation['description'] ?:
                    $reservation->getBookable()->getName()->getValue(),
                'method'      => $settingsService->getSetting('payments', 'mollie')['method'],
                'metaData'    => $additionalInformation['metaData'] ?: [],
            ]
        );

        if ($response->isRedirect()) {
            /** @var Reservation $reservation */
            $reservation = $reservationService->getNew(true, true, true);

            $result = $reservationService->processRequest(
                $bookingAS->getAppointmentData($command->getFields()),
                $reservation,
                true
            );

            if ($result->getResult() === CommandResult::RESULT_ERROR) {
                return $result;
            }

            /** @var Payment $payment */
            $payment = null;

            switch ($reservation->getReservation()->getType()->getValue()) {
                case (Entities::APPOINTMENT):
                case (Entities::EVENT):
                    /** @var Payment $payment */
                    $payment = $reservation->getBooking()->getPayments()->getItem(0);

                    break;

                case (Entities::PACKAGE):
                    /** @var PackageCustomerService $packageCustomerService */
                    foreach ($reservation->getPackageCustomerServices()->getItems() as $packageCustomerService) {
                        /** @var Payment $payment */
                        $payment = $packageCustomerService->getPackageCustomer()->getPayment();

                        break;
                    }

                    break;
            }

            $cache->setPaymentId(new Id($payment->getId()->getValue()));

            $cache->setData(
                new Json(
                    json_encode(
                        [
                            'status'   => null,
                            'request'  => $command->getField('componentProps'),
                            'response' => $result->getData(),
                        ]
                    )
                )
            );

            $cacheRepository->update(
                $cache->getId()->getValue(),
                $cache
            );

            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('Proceed to Mollie Payment Page');
            $result->setData(
                [
                    'redirectUrl' => $response->getRedirectUrl(),
                ]
            );

            return $result;
        }

        $result->setResult(CommandResult::RESULT_ERROR);
        $result->setMessage(FrontendStrings::getCommonStrings()['payment_error']);
        $result->setData(
            [
                'message' => $response->getMessage() && json_decode($response->getMessage(), true) !== false?
                    json_decode($response->getMessage(), true)['detail'] : '',
                'paymentSuccessful' => false,
            ]
        );

        return $result;
    }
}
