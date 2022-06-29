<?php

namespace AmeliaBooking\Application\Commands\Notification;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Domain\Collection\AbstractCollection;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationsToEntitiesRepository;

/**
 * Class GetNotificationsCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Notification
 */
class GetNotificationsCommandHandler extends CommandHandler
{
    /**
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle()
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::NOTIFICATIONS)) {
            throw new AccessDeniedException('You are not allowed to read notifications');
        }

        $result = new CommandResult();

        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->container->get('domain.notification.repository');
        /** @var NotificationsToEntitiesRepository $notificationEntitiesRepo */
        $notificationEntitiesRepo = $this->container->get('domain.notificationEntities.repository');

        /** @var Collection $notifications */
        $notifications = $notificationRepo->getAll();
        foreach ($notifications->getItems() as $key => $notification) {
            if ($notification->getCustomName()) {
                $notification->setEntityIds($notificationEntitiesRepo->getEntities($notification->getId()->getValue()));
                $notification->setEntityIds(array_map('intval', $notification->getEntityIds()));
            }
        }

        if (!$notifications instanceof AbstractCollection) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not get notifications');

            return $result;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved notifications.');
        $result->setData(
            [
            Entities::NOTIFICATIONS => $notifications->toArray()
            ]
        );

        return $result;
    }
}
