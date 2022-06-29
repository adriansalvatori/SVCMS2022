<?php

namespace AmeliaBooking\Application\Commands\User\Customer;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Domain\Repository\User\UserRepositoryInterface;

/**
 * Class GetCustomerCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User\Customer
 */
class GetCustomerCommandHandler extends CommandHandler
{
    /**
     * @param GetCustomerCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(GetCustomerCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $this->getContainer()->get('domain.users.repository');

        $user = $userRepository->getById((int)$command->getField('id'));

        if (!$user instanceof AbstractUser) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not retrieve user');

            return $result;
        }

        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead($user->getType())) {
            throw new AccessDeniedException('You are not allowed to read user');
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved user');
        $result->setData([
            Entities::USER => $user->toArray()
        ]);

        return $result;
    }
}
