<?php

namespace AmeliaBooking\Infrastructure\WP\EventListeners\User\Provider;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use Interop\Container\Exception\ContainerException;

/**
 * Class ProviderUpdatedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\User\Provdier
 */
class ProviderUpdatedEventHandler
{
    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws ContainerException
     * @throws QueryExecutionException
     */
    public static function handle($commandResult, $container)
    {
        if ($commandResult->getData()['sendEmployeePanelAccessEmail'] === true) {
            /** @var EmailNotificationService $emailNotificationService */
            $emailNotificationService = $container->get('application.emailNotification.service');

            $emailNotificationService->sendEmployeePanelAccess(
                $commandResult->getData()['user'],
                $commandResult->getData()['password']
            );
        }
    }
}
