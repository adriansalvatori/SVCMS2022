<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Bookable\Service;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Factory\Payment\PaymentFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;

/**
 * Class PackageCustomerFactory
 *
 * @package AmeliaBooking\Domain\Factory\Bookable\Service
 */
class PackageCustomerFactory
{
    /**
     * @param $data
     *
     * @return PackageCustomer
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        /** @var PackageCustomer $packageCustomer */
        $packageCustomer = new PackageCustomer();

        if (isset($data['id'])) {
            $packageCustomer->setId(new Id($data['id']));
        }

        if (isset($data['packageId'])) {
            $packageCustomer->setPackageId(new Id($data['packageId']));
        }

        if (isset($data['customerId'])) {
            $packageCustomer->setCustomerId(new Id($data['customerId']));
        }

        if (isset($data['price'])) {
            $packageCustomer->setPrice(new Price($data['price']));
        }

        if (!empty($data['payment'])) {
            $packageCustomer->setPayment(PaymentFactory::create($data['payment']));
        }

        if (!empty($data['end'])) {
            $packageCustomer->setEnd(
                new DateTimeValue(DateTimeService::getCustomDateTimeObject($data['end']))
            );
        }

        if (!empty($data['start'])) {
            $packageCustomer->setStart(
                new DateTimeValue(DateTimeService::getCustomDateTimeObject($data['start']))
            );
        }

        if (!empty($data['purchased'])) {
            $packageCustomer->setPurchased(
                new DateTimeValue(DateTimeService::getCustomDateTimeObject($data['purchased']))
            );
        }

        if (!empty($data['status'])) {
            $packageCustomer->setStatus(
                new BookingStatus($data['status'])
            );
        }

        return $packageCustomer;
    }
}
