<?php

namespace AmeliaBooking\Application\Commands\Settings;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use Interop\Container\Exception\ContainerException;

/**
 * Class GetSettingsCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Settings
 */
class GetSettingsCommandHandler extends CommandHandler
{
    /**
     * @return CommandResult
     * @throws ContainerException
     * @throws AccessDeniedException
     */
    public function handle()
    {
        $result = new CommandResult();

        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::SETTINGS)) {
            throw new AccessDeniedException('You are not allowed to read settings.');
        }

        /** @var SettingsService $settingsService */
        $settingsService = $this->getContainer()->get('domain.settings.service');

        $settings = $settingsService->getAllSettingsCategorized();

        if ($settings['activation']['purchaseCodeStore'] !== '' && $settings['activation']['active']) {
            $settings['activation']['purchaseCodeStore'] = null;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved settings.');
        $result->setData(
            [
                'settings' => $settings
            ]
        );

        return $result;
    }
}
