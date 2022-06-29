<?php

namespace AmeliaBooking\Application\Commands\PaymentGateway;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\CustomField\CustomFieldApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\ForbiddenFileUploadException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\WP\Integrations\WooCommerce\WooCommerceService;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class WooCommercePaymentCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\PaymentGateway
 */
class WooCommercePaymentCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'bookings',
        'payment'
    ];

    /**
     * @param WooCommercePaymentCommand $command
     *
     * @return CommandResult
     * @throws ForbiddenFileUploadException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(WooCommercePaymentCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $type = $command->getField('type') ?: Entities::APPOINTMENT;

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($type);

        WooCommerceService::setContainer($this->container);

        $reservation = $reservationService->getNew(true, true, true);

        $appointmentData = $bookingAS->getAppointmentData($command->getFields());

        $reservationService->processBooking(
            $result,
            $appointmentData,
            $reservation,
            false
        );

        if ($result->getResult() === CommandResult::RESULT_ERROR) {
            return $result;
        }

        /** @var CustomFieldApplicationService $customFieldService */
        $customFieldService = $this->container->get('application.customField.service');

        $uploadedCustomFieldFilesNames = $customFieldService->saveUploadedFiles(
            0,
            $reservation->getUploadedCustomFieldFilesInfo(),
            '/tmp',
            false
        );

        $appointmentData = $reservationService->getWooCommerceData(
            $reservation,
            $command->getFields()['payment']['gateway'],
            $appointmentData
        );

        $appointmentData['uploadedCustomFieldFilesInfo'] = $uploadedCustomFieldFilesNames;

        $appointmentData['returnUrl'] = $command->getField('returnUrl');

        $appointmentData['cacheData'] = json_encode(
            [
                'status'  => null,
                'request' => $command->getField('componentProps'),
            ]
        );

        try {
            $bookableSettings = $reservation->getBookable()->getSettings() ?
                json_decode($reservation->getBookable()->getSettings()->getValue(), true) : null;

            WooCommerceService::addToCart(
                $appointmentData,
                $bookableSettings && isset($bookableSettings['payments']['wc']['productId']) ?
                    $bookableSettings['payments']['wc']['productId'] : null
            );
        } catch (Exception $e) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage(FrontendStrings::getCommonStrings()['wc_error']);
            $result->setData(
                [
                    'wooCommerceError' => true
                ]
            );

            return $result;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Proceed to WooCommerce Cart');
        $result->setData(
            [
                'cartUrl' => WooCommerceService::getPageUrl(
                    !empty($appointmentData['locale']) ? $appointmentData['locale'] : ''
                )
            ]
        );

        return $result;
    }
}
