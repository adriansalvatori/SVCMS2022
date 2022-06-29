<?php

namespace AmeliaBooking\Application\Commands\Notification;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Notification\NotificationHelperService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Notification\Notification;
use AmeliaBooking\Domain\Factory\Notification\NotificationFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationsToEntitiesRepository;
use \Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class UpdateNotificationCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Notification
 */
class UpdateNotificationCommandHandler extends CommandHandler
{
    public $mandatoryFields = [
        'subject',
        'content'
    ];

    /**
     * @param UpdateNotificationCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws ContainerException
     */
    public function handle(UpdateNotificationCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::NOTIFICATIONS)) {
            throw new AccessDeniedException('You are not allowed to update notification');
        }

        $notificationId = (int)$command->getArg('id');

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->container->get('domain.notification.repository');
        /** @var NotificationsToEntitiesRepository $notificationEntitiesRepo */
        $notificationEntitiesRepo = $this->container->get('domain.notificationEntities.repository');
        /** @var EventRepository $eventRepo */
        $eventRepo = $this->container->get('domain.booking.event.repository');
        /** @var NotificationHelperService $notificationHelper */
        $notificationHelper = $this->container->get('application.notificationHelper.service');

        $currentNotification = $notificationRepo->getById($notificationId);
        $currentEntityList   = $notificationEntitiesRepo->getEntities($notificationId);

        $content = $command->getField('content');

        if ($command->getField('type') === 'email') {
            $content = preg_replace("/\r|\n/", "", $content);
        }

        $contentRes = $notificationHelper->parseAndReplace($content);

        $parsedContent = $contentRes[0];

        $content = $contentRes[1];

        $isCustom = $command->getField('customName') !== null ;

        $notification = NotificationFactory::create(
            [
            'id'           => $notificationId,
            'name'         => $isCustom ? $command->getField('name') : $currentNotification->getName()->getValue(),
            'customName'   => $command->getField('customName'),
            'status'       => $command->getField('status') ?: $currentNotification->getStatus()->getValue(),
            'type'         => $currentNotification->getType()->getValue(),
            'time'         => $command->getField('time'),
            'timeBefore'   => $command->getField('timeBefore'),
            'timeAfter'    => $command->getField('timeAfter'),
            'sendTo'       => $currentNotification->getSendTo()->getValue(),
            'subject'      => $command->getField('subject'),
            'entity'       => $command->getField('entity'),
            'content'      => $content,
            'translations' => $command->getField('translations'),
            'entityIds'    => $command->getField('entityIds'),
            'sendOnlyMe'   => $command->getField('sendOnlyMe')
            ]
        );

        if (!$notification instanceof Notification) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not update notification entity.');

            return $result;
        }

        if ($notificationRepo->update($notificationId, $notification)) {
            $result->setResult(CommandResult::RESULT_SUCCESS);
            $result->setMessage('Successfully updated notification.');
            $result->setData(
                [
                Entities::NOTIFICATION => $notification->toArray(),
                'update'               => $parsedContent !== null
                ]
            );
        }

        if ($notification->getCustomName()) {
            $removeEntities = array_diff($currentEntityList, $notification->getEntityIds());
            $addEntities    = array_diff($notification->getEntityIds(), $currentEntityList);

            foreach ($removeEntities as $removeEntity) {
                $notificationEntitiesRepo->removeEntity($notificationId, $removeEntity, $notification->getEntity()->getValue());
            }
            foreach ($addEntities as $addEntity) {
                $recurringMain = null;
                if ($notification->getEntity()->getValue() === Entities::EVENT) {
                    $recurring = $eventRepo->isRecurring($addEntity);
                    if ($recurring['event_recurringOrder'] !== null) {
                        $recurringMain = $recurring['event_recurringOrder'] === 1 ? $addEntity : $recurring['event_parentId'];
                    }
                }
                $notificationEntitiesRepo->addEntity($notificationId, $recurringMain ?: $addEntity, $notification->getEntity()->getValue());
            }
        }

        return $result;
    }
}
