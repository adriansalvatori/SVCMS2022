<?php

namespace AmeliaBooking\Application\Commands\User\Customer;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\Password;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use Interop\Container\Exception\ContainerException;

/**
 * Class UpdateCustomerCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User\Customer
 */
class UpdateCustomerCommandHandler extends CommandHandler
{
    /**
     * @param UpdateCustomerCommand $command
     *
     * @return CommandResult
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws AccessDeniedException
     */
    public function handle(UpdateCustomerCommand $command)
    {
        /** @var CommandResult $result */
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');

        /** @var Customer $oldUser */
        $oldUser = null;

        /** @var UserRepository $userRepository */
        $userRepository = $this->getContainer()->get('domain.users.repository');

        $userRepository->beginTransaction();

        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::CUSTOMERS)) {
            $oldUser = $userAS->getAuthenticatedUser($command->getToken(), false, 'customerCabinet');

            if ($oldUser === null) {
                $result->setResult(CommandResult::RESULT_ERROR);
                $result->setMessage('Could not retrieve user');
                $result->setData(
                    [
                        'reauthorize' => true
                    ]
                );

                return $result;
            }
        } else {
            $oldUser = $userRepository->getById($command->getArg('id'));
        }

        if ($command->getField('externalId') === -1) {
            $command->setField('externalId', null);
        }

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var AbstractUser $currentUser */
        $currentUser = $this->container->get('logged.in.user');

        if ($command->getField('email') === '' &&
            !$settingsService->getSetting('roles', 'allowCustomerDeleteProfile') &&
            (!$currentUser || $currentUser->getType() === AbstractUser::USER_ROLE_CUSTOMER)
        ) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update user.');

            return $result;
        }

        /** @var Customer $newUser */
        $newUser = UserFactory::create(array_merge($oldUser->toArray(), $command->getFields()));

        if (!($newUser instanceof AbstractUser)) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update user.');

            return $result;
        }

        if ($oldUser &&
            $userRepository->getByEmail($newUser->getEmail()->getValue()) &&
            $oldUser->getEmail()->getValue() !== $newUser->getEmail()->getValue()
        ) {
            $result->setResult(CommandResult::RESULT_CONFLICT);
            $result->setMessage('Email already exist.');
            $result->setData('This email is already in use.');

            return $result;
        }

        if ($command->getField('password')) {
            /** @var Password $newPassword */
            $newPassword = new Password($command->getField('password'));

            $userRepository->updateFieldById($command->getArg('id'), $newPassword->getValue(), 'password');
        }

        if (!$userRepository->update($command->getArg('id'), $newUser)) {
            $userRepository->rollback();

            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update user.');

            return $result;
        }

        if ($command->getField('externalId') === 0) {
            /** @var UserApplicationService $userAS */
            $userAS = $this->getContainer()->get('application.user.service');

            $userAS->setWpUserIdForNewUser($command->getArg('id'), $newUser);
        }

        if ($command->getField('email') === '') {
            /** @var CustomerBookingRepository $bookingRepository */
            $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

            $bookingRepository->updateInfoByCustomerId($oldUser->getId()->getValue(), null);
        }

        $userRepository->commit();

        $result = $userAS->getAuthenticatedUserResponse(
            $newUser,
            $oldUser->getEmail()->getValue() !== $newUser->getEmail()->getValue(),
            true,
            $oldUser->getLoginType(),
            'customer'
        );

        $result->setMessage('Successfully updated user');

        return $result;
    }
}
