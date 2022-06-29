<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Bookable\Package;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Gallery\GalleryApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class UpdatePackageCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Bookable\Package
 */
class UpdatePackageCommandHandler extends CommandHandler
{
    /** @var array */
    public $mandatoryFields = [
        'name',
        'price',
        'calculatedPrice',
        'bookable',
    ];

    /**
     * @param UpdatePackageCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws AccessDeniedException
     * @throws ContainerException
     */
    public function handle(UpdatePackageCommand $command)
    {
        if (!$this->getContainer()->getPermissionsService()->currentUserCanWrite(Entities::PACKAGES)) {
            throw new AccessDeniedException('You are not allowed to update package.');
        }

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var Package $package */
        $package = PackageFactory::create($command->getFields());

        if (!($package instanceof Package)) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setMessage('Unable to update package.');

            return $result;
        }

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var BookableApplicationService $bookableService */
        $bookableService = $this->container->get('application.bookable.service');
        /** @var GalleryApplicationService $galleryService */
        $galleryService = $this->container->get('application.gallery.service');

        $bookableServices = [];

        foreach ($command->getField('bookable') as $bookable) {
            $bookableServices[$bookable['service']['id']] = [
                'quantity'         => $bookable['quantity'],
                'minimumScheduled' => $bookable['minimumScheduled'],
                'maximumScheduled' => $bookable['maximumScheduled'],
                'providers'        => !empty($bookable['providers']) ? $bookable['providers'] : [],
                'locations'        => !empty($bookable['locations']) ? $bookable['locations'] : [],
            ];
        }

        $packageRepository->beginTransaction();

        $packageId = $command->getArg('id');

        $packageRepository->update($packageId, $package);

        $bookableService->manageServicesForPackageUpdate($package, $bookableServices);

        $galleryService->manageGalleryForEntityUpdate($package->getGallery(), $packageId, Entities::PACKAGE);

        $packageRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully updated package.');
        $result->setData(
            [
                Entities::PACKAGE => $package->toArray(),
            ]
        );

        return $result;
    }
}
