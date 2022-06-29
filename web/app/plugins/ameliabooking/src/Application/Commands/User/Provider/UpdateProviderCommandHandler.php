<?php

namespace AmeliaBooking\Application\Commands\User\Provider;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\ProviderApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\Password;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class UpdateProviderCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User\Provider
 */
class UpdateProviderCommandHandler extends CommandHandler
{
    /**
     * @param UpdateProviderCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     */
    public function handle(UpdateProviderCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        /** @var ProviderApplicationService $providerAS */
        $providerAS = $this->container->get('application.user.provider.service');

        $userId = (int)$command->getArg('id');

        /** @var AbstractUser $currentUser */
        $currentUser = $this->container->get('logged.in.user');

        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');

        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::EMPLOYEES) ||
            (
                !$this->getContainer()->getPermissionsService()->currentUserCanWriteOthers(Entities::EMPLOYEES) &&
                $currentUser->getId()->getValue() !== $userId
            )
        ) {
            $oldUser = $userAS->getAuthenticatedUser($command->getToken(), false, 'providerCabinet');

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

            $oldUser = $providerAS->getProviderWithServicesAndSchedule($oldUser->getId()->getValue());
        } else {
            $oldUser = $providerAS->getProviderWithServicesAndSchedule($userId);
        }

        $command->setField('id', $userId);

        /** @var Provider $newUser */
        $newUser = UserFactory::create(array_merge($oldUser->toArray(), $command->getFields()));

        if (!$newUser instanceof AbstractUser) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update user.');

            return $result;
        }

        if (($currentUser === null && $command->getToken()) ||
            $currentUser->getType() === AbstractUser::USER_ROLE_PROVIDER
        ) {
            /** @var SettingsService $settingsDS */
            $settingsDS = $this->container->get('domain.settings.service');

            $rolesSettings = $settingsDS->getCategorySettings('roles');

            if (!$rolesSettings['allowConfigureServices']) {
                $newUser->setServiceList($oldUser->getServiceList());
            }

            if (!$rolesSettings['allowConfigureSchedule']) {
                $newUser->setWeekDayList($oldUser->getWeekDayList());
            }

            if (!$rolesSettings['allowConfigureDaysOff']) {
                $newUser->setDayOffList($oldUser->getDayOffList());
            }

            if (!$rolesSettings['allowConfigureSpecialDays']) {
                $newUser->setSpecialDayList($oldUser->getSpecialDayList());
            }
        }

        $providerRepository->beginTransaction();

        if ($providerRepository->getByEmail($newUser->getEmail()->getValue()) &&
            $oldUser->getEmail()->getValue() !== $newUser->getEmail()->getValue()) {
            $result->setResult(CommandResult::RESULT_CONFLICT);
            $result->setMessage('Email already exist.');
            $result->setData('This email is already in use.');

            return $result;
        }

        if ($command->getField('password')) {
            $newPassword = new Password($command->getField('password'));

            $providerRepository->updateFieldById($command->getArg('id'), $newPassword->getValue(), 'password');
        }

        try {
            if (!$providerAS->update($oldUser, $newUser)) {
                $providerRepository->rollback();
                return $result;
            }

            if ($command->getField('externalId') === 0) {
                /** @var UserApplicationService $userAS */
                $userAS = $this->getContainer()->get('application.user.service');

                $userAS->setWpUserIdForNewUser($userId, $newUser);
            }
        } catch (QueryExecutionException $e) {
            $providerRepository->rollback();
            throw $e;
        }

        $result = $userAS->getAuthenticatedUserResponse(
            $newUser,
            $oldUser->getEmail()->getValue() !== $newUser->getEmail()->getValue(),
            true,
            $oldUser->getLoginType(),
            'provider'
        );

        $result->setData(
            array_merge(
                $result->getData(),
                ['sendEmployeePanelAccessEmail' =>
                     $command->getField('password') && $command->getField('sendEmployeePanelAccessEmail'),
                 'password'                     => $command->getField('password')
                ]
            )
        );

        $providerRepository->commit();

        return $result;
    }
}
