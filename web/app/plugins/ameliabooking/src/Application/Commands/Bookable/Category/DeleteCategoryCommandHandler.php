<?php

namespace AmeliaBooking\Application\Commands\Bookable\Category;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;

/**
 * Class DeleteCategoryCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Bookable\Category
 */
class DeleteCategoryCommandHandler extends CommandHandler
{
    /**
     * @param DeleteCategoryCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws NotFoundException
     * @throws AccessDeniedException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public function handle(DeleteCategoryCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanDelete(Entities::SERVICES)) {
            throw new AccessDeniedException('You are not allowed to delete bookable category.');
        }

        $result = new CommandResult();

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');

        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var BookableApplicationService $bookableApplicationService */
        $bookableApplicationService = $this->getContainer()->get('application.bookable.service');

        /** @var Category $category */
        $category = $categoryRepository->getById($command->getArg('id'));

        if (!$category instanceof Category) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete bookable category.');

            return $result;
        }

        /** @var Collection $services */
        $services = $serviceRepository->getByCriteria(['categories' => [$command->getArg('id')]]);

        $category->setServiceList($services);

        $categoryServiceIds = [];

        /** @var Service $service */
        foreach ($services->getItems() as $service) {
            $categoryServiceIds[] = $service->getId()->getValue();
        }

        if ($categoryServiceIds) {
            $appointmentsCount = $bookableApplicationService->getAppointmentsCountForServices($categoryServiceIds);

            if ($appointmentsCount['futureAppointments'] || $appointmentsCount['packageAppointments']) {
                $result->setResult(CommandResult::RESULT_CONFLICT);
                $result->setMessage('Could not delete category.');
                $result->setData([]);

                return $result;
            }
        }

        $categoryRepository->beginTransaction();

        if (!$bookableApplicationService->deleteCategory($category)) {
            $categoryRepository->rollback();

            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Could not delete category.');

            return $result;
        }

        $categoryRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully deleted bookable category.');

        return $result;
    }
}
