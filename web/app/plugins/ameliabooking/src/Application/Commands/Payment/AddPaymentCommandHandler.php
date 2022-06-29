<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Payment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Payment\PaymentFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;

/**
 * Class AddPaymentCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Payment
 */
class AddPaymentCommandHandler extends CommandHandler
{
    /** @var array */
    public $mandatoryFields = [];

    /**
     * @param AddPaymentCommand $command
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws AccessDeniedException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(AddPaymentCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::FINANCE)) {
            throw new AccessDeniedException('You are not allowed to add new payment.');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $payment = PaymentFactory::create($command->getFields());
        if (!$payment instanceof Payment) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Unable to create payment.');

            return $result;
        }

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');
        if ($paymentRepository->add($payment)) {
            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('New payment successfully created.');
            $result->setData(
                [
                    Entities::PAYMENT => $payment->toArray(),
                ]
            );
        }

        return $result;
    }
}
