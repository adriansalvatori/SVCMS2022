<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Booking\Appointment;

use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBookingExtra;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;

/**
 * Class CustomerBookingExtraFactory
 *
 * @package AmeliaBooking\Domain\Factory\Booking\Appointment
 */
class CustomerBookingExtraFactory
{

    /**
     * @param $data
     *
     * @return CustomerBookingExtra
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     */
    public static function create($data)
    {
        $customerBookingExtra = new CustomerBookingExtra(
            new Id($data['extraId']),
            new PositiveInteger($data['quantity'])
        );

        if (isset($data['id'])) {
            $customerBookingExtra->setId(new Id($data['id']));
        }

        if (isset($data['customerBookingId'])) {
            $customerBookingExtra->setCustomerBookingId(new Id($data['customerBookingId']));
        }

        if (isset($data['price'])) {
            $customerBookingExtra->setPrice(new Price($data['price']));
        }

        if (isset($data['aggregatedPrice'])) {
            $customerBookingExtra->setAggregatedPrice(new BooleanValueObject($data['aggregatedPrice']));
        }

        return $customerBookingExtra;
    }
}
