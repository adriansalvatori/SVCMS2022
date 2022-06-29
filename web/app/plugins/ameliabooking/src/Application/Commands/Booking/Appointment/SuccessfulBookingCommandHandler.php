<?php

namespace AmeliaBooking\Application\Commands\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use Slim\Exception\ContainerValueNotFoundException;
use Exception;

/**
 * Class SuccessfulBookingCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Booking\Appointment
 */
class SuccessfulBookingCommandHandler extends CommandHandler
{
    /**
     * @var array
     */
    public $mandatoryFields = [
        'appointmentStatusChanged',
    ];

    /**
     * @param SuccessfulBookingCommand $command
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws Exception
     */
    public function handle(SuccessfulBookingCommand $command)
    {
        $this->checkMandatoryFields($command);

        $type = $command->getField('type') ?: Entities::APPOINTMENT;

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($type);

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        $paymentId = $command->getField('paymentId');

        if ($paymentId) {
            /** @var Payment $payment */
            $payment = $paymentRepository->getById($paymentId);

            if ($payment && $payment->getActionsCompleted() && $payment->getActionsCompleted()->getValue()) {
                $result = new CommandResult();

                $result->setResult(CommandResult::RESULT_SUCCESS);
                $result->setMessage('Successfully get booking');
                $result->setDataInResponse(false);

                return $result;
            }
        }

        return $reservationService->getSuccessBookingResponse(
            (int)$command->getArg('id'),
            $command->getField('type') ?: Entities::APPOINTMENT,
            !empty($command->getFields()['recurring']) ? $command->getFields()['recurring'] : [],
            $command->getFields()['appointmentStatusChanged'],
            $command->getField('packageId'),
            $command->getField('customer'),
            $command->getField('paymentId'),
            $command->getField('packageCustomerId')
        );
    }
}
