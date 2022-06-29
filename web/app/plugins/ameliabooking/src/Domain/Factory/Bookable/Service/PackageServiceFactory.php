<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Bookable\Service;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageService;
use AmeliaBooking\Domain\Factory\Location\LocationFactory;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\WholeNumber;

/**
 * Class PackageServiceFactory
 *
 * @package AmeliaBooking\Domain\Factory\Bookable\Service
 */
class PackageServiceFactory
{
    /**
     * @param $data
     *
     * @return PackageService
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        /** @var PackageService $packageService */
        $packageService = new PackageService();

        if (isset($data['id'])) {
            $packageService->setId(new Id($data['id']));
        }

        if (isset($data['quantity'])) {
            $packageService->setQuantity(new PositiveInteger($data['quantity']));
        }

        if (isset($data['service'])) {
            $packageService->setService(ServiceFactory::create($data['service']));
        }

        if (isset($data['minimumScheduled'])) {
            $packageService->setMinimumScheduled(new WholeNumber($data['minimumScheduled']));
        }

        if (isset($data['maximumScheduled'])) {
            $packageService->setMaximumScheduled(new WholeNumber($data['maximumScheduled']));
        }

        $packageService->setProviders(new Collection());

        if (!empty($data['providers'])) {
            foreach ($data['providers'] as $providerData) {
                $packageService->getProviders()->addItem(UserFactory::create($providerData));
            }
        }

        $packageService->setLocations(new Collection());

        if (!empty($data['locations'])) {
            foreach ($data['locations'] as $locationData) {
                $packageService->getLocations()->addItem(LocationFactory::create($locationData));
            }
        }

        return $packageService;
    }
}
