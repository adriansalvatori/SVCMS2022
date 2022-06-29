<?php

namespace AmeliaBooking\Domain\Factory\Bookable\Service;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Duration;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\String\Description;
use AmeliaBooking\Domain\ValueObjects\String\Name;

/**
 * Class ExtraFactory
 *
 * @package AmeliaBooking\Domain\Factory\Bookable\Service
 */
class ExtraFactory
{
    /**
     * @param array $data
     *
     * @return Extra
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        $extra = new Extra(
            new Name($data['name']),
            new Description($data['description']),
            new Price($data['price']),
            new PositiveInteger($data['maxQuantity']),
            new PositiveInteger($data['position'])
        );

        if (isset($data['id'])) {
            $extra->setId(new Id($data['id']));
        }

        if (!empty($data['duration'])) {
            $extra->setDuration(new Duration($data['duration']));
        }

        if (isset($data['serviceId'])) {
            $extra->setServiceId(new Id($data['serviceId']));
        }

        if (isset($data['aggregatedPrice'])) {
            $extra->setAggregatedPrice(new BooleanValueObject($data['aggregatedPrice']));
        }

        if (isset($data['translations'])) {
            $extra->setTranslations(new Json($data['translations']));
        }

        return $extra;
    }
}
