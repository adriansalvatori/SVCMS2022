<?php

namespace AmeliaBooking\Domain\Factory\Bookable\Service;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\String\Status;
use AmeliaBooking\Domain\ValueObjects\String\Name;

/**
 * Class CategoryFactory
 *
 * @package AmeliaBooking\Domain\Factory\Bookable\Service
 */
class CategoryFactory
{
    /**
     * @param array $data
     *
     * @return Category
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public static function create($data)
    {
        $category = new Category(
            new Status($data['status']),
            new Name($data['name']),
            new PositiveInteger($data['position'])
        );

        if (isset($data['id'])) {
            $category->setId(new Id($data['id']));
        }

        if (isset($data['serviceList'])) {
            $services = [];
            /** @var array $serviceList */
            $serviceList = $data['serviceList'];
            foreach ($serviceList as $service) {
                $services[] = ServiceFactory::create($service);
            }

            $category->setServiceList(new Collection($services));
        }

        if (isset($data['translations'])) {
            $category->setTranslations(new Json($data['translations']));
        }

        return $category;
    }
}
