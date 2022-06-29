<?php

namespace AmeliaBooking\Application\Commands\Location;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Location\LocationApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Domain\Repository\Location\LocationRepositoryInterface;

/**
 * Class DeleteLocationCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Location
 */
class DeleteLocationCommandHandler extends CommandHandler
{
    /**
     * @param DeleteLocationCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws AccessDeniedException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     */
    public function handle(DeleteLocationCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanDelete(Entities::LOCATIONS)) {
            throw new AccessDeniedException('You are not allowed to delete location');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var LocationRepositoryInterface $locationRepository */
        $locationRepository = $this->getContainer()->get('domain.locations.repository');

        /** @var LocationApplicationService $locationApplicationService */
        $locationApplicationService = $this->container->get('application.location.service');

        /** @var Location $location */
        $location = $locationRepository->getById($command->getArg('id'));

        if (!$location instanceof Location) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete location.');

            return $result;
        }

        $locationRepository->beginTransaction();

        if (!$locationApplicationService->delete($location)) {
            $locationRepository->rollback();

            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Unable to delete location.');

            return $result;
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully deleted location.');
        $result->setData(
            [
                Entities::LOCATION => $location->toArray()
            ]
        );

        $locationRepository->commit();

        return $result;
    }
}
