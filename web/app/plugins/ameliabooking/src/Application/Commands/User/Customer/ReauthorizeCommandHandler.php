<?php

namespace AmeliaBooking\Application\Commands\User\Customer;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;

/**
 * Class ReauthorizeCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User\Customer
 */
class ReauthorizeCommandHandler extends CommandHandler
{
    /**
     * @param ReauthorizeCommand $command
     *
     * @return CommandResult
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Slim\Exception\ContainerException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Exception
     */
    public function handle(ReauthorizeCommand $command)
    {
        $result = new CommandResult();

        /** @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get('domain.users.repository');

        /** @var EmailNotificationService $notificationService */
        $notificationService = $this->getContainer()->get('application.emailNotification.service');

        /** @var Customer $customer */
        $customer = $userRepository->getByEmail($command->getField('email'));


        if ($customer !== null) {
            $notificationService->sendRecoveryEmail($customer, $command->getField('email'), $command->getField('cabinetType'));
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully sent recovery email');

        return $result;
    }
}
