<?php

namespace AmeliaBooking\Application\Commands\User;

use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class GetCurrentUserCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User
 */
class GetCurrentUserCommandHandler extends CommandHandler
{
    /**
     * @param GetCurrentUserCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerException
     * @throws \InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(GetCurrentUserCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $userData = null;

        if ($command->getToken()) {
            /** @var UserApplicationService $userAS */
            $userAS = $this->getContainer()->get('application.user.service');

            try {
                /** @var AbstractUser $user */
                $user = $userAS->authorization(
                    $command->getToken(),
                    $command->getCabinetType() ? $command->getCabinetType() : 'customer'
                );
            } catch (AuthorizationException $e) {
                $user = null;
            }
        } else {
            /** @var AbstractUser $user */
            $user = $this->getContainer()->get('logged.in.user');
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved current user');
        $result->setData([
            Entities::USER => $user ? $user->toArray()  : null
        ]);

        return $result;
    }
}
