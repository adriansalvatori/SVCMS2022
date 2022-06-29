<?php

namespace AmeliaBooking\Application\Commands\Zoom;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Zoom\ZoomService;
use Interop\Container\Exception\ContainerException;
use mageekguy\atoum\asserters\boolean;

/**
 * Class GetUsersCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Zoom
 */
class GetUsersCommandHandler extends CommandHandler
{
    /**
     * @param GetUsersCommand $command
     *
     * @return CommandResult
     * @throws AccessDeniedException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function handle(GetUsersCommand $command)
    {
        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');

        /** @var boolean $isCabinetPage */
        $isCabinetPage = $command->getPage() === 'cabinet';

        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::EMPLOYEES)) {
            /** @var AbstractUser $user */
            $user = $userAS->getAuthenticatedUser($command->getToken(), false, 'providerCabinet');

            if (!$isCabinetPage || ($user === null || $user->getType() !== AbstractUser::USER_ROLE_PROVIDER)) {
                throw new AccessDeniedException('You are not allowed to read users.');
            }
        }

        $result = new CommandResult();

        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');

        $zoomSettings = $settingsDS->getCategorySettings('zoom');

        if (!$zoomSettings['apiKey'] || !$zoomSettings['apiSecret']) {
            $result->setResult(CommandResult::RESULT_SUCCESS);

            return $result;
        }

        /** @var ZoomService $zoomService */
        $zoomService = $this->container->get('infrastructure.zoom.service');

        if (!$zoomService) {
            $result->setResult(CommandResult::RESULT_SUCCESS);

            return $result;
        }

        $zoomResult = $zoomService->getUsers();

        if ((isset($zoomResult['code']) && $zoomResult['code'] === 124) ||
            ($zoomResult['users'] === null && isset($zoomResult['message']))
        ) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage($zoomResult['message']);

            return $result;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved users');
        $result->setData([
            'users' => $zoomResult['users']
        ]);

        return $result;
    }
}
