<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Factory\Bookable\Service;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\WholeNumber;

/**
 * Class PackageCustomerFactory
 *
 * @package AmeliaBooking\Domain\Factory\Bookable\Service
 */
class PackageCustomerServiceFactory
{
    /**
     * @param $data
     *
     * @return PackageCustomerService
     * @throws InvalidArgumentException
     */
    public static function create($data)
    {
        /** @var PackageCustomerService $packageCustomerService */
        $packageCustomerService = new PackageCustomerService();

        if (isset($data['id'])) {
            $packageCustomerService->setId(new Id($data['id']));
        }

        if (isset($data['packageCustomer'])) {
            /** @var PackageCustomer $packageCustomer */
            $packageCustomer = PackageCustomerFactory::create($data['packageCustomer']);

            $packageCustomerService->setPackageCustomer($packageCustomer);
        }

        if (isset($data['serviceId'])) {
            $packageCustomerService->setServiceId(new Id($data['serviceId']));
        }

        if (isset($data['providerId'])) {
            $packageCustomerService->setProviderId(new Id($data['providerId']));
        }

        if (isset($data['locationId'])) {
            $packageCustomerService->setLocationId(new Id($data['locationId']));
        }

        if (isset($data['bookingsCount'])) {
            $packageCustomerService->setBookingsCount(new WholeNumber($data['bookingsCount']));
        }

        return $packageCustomerService;
    }

    /**
     * @param array $rows
     *
     * @return Collection
     * @throws InvalidArgumentException
     */
    public static function createCollection($rows)
    {
        $packagesCustomersServices = [];

        foreach ($rows as $row) {
            $packageCustomerServiceId = $row['package_customer_service_id'];

            if (!array_key_exists($packageCustomerServiceId, $packagesCustomersServices)) {
                $packagesCustomersServices[$packageCustomerServiceId] = [
                    'id'            => $packageCustomerServiceId,
                    'serviceId'     => $row['package_customer_service_serviceId'],
                    'providerId'    => $row['package_customer_service_providerId'],
                    'locationId'    => $row['package_customer_service_locationId'],
                    'bookingsCount' => $row['package_customer_service_bookingsCount'],
                    'packageCustomer' => [
                        'id'         => $row['package_customer_id'],
                        'customerId' => $row['package_customer_customerId'],
                        'packageId'  => $row['package_customer_packageId'],
                        'price'      => $row['package_customer_price'],
                        'payment'    => !empty($row['payment_id']) ? [
                            'id'                => $row['payment_id'],
                            'customerBookingId' => null,
                            'packageCustomerId' => $row['payment_packageCustomerId'],
                            'status'            => $row['payment_status'],
                            'dateTime'          => DateTimeService::getCustomDateTimeFromUtc($row['payment_dateTime']),
                            'gateway'           => $row['payment_gateway'],
                            'gatewayTitle'      => $row['payment_gatewayTitle'],
                            'amount'            => $row['payment_amount'],
                            'data'              => $row['payment_data'],
                            'wcOrderId'         => !empty($row['payment_wcOrderId']) ? $row['payment_wcOrderId'] : null
                        ] : null,
                        'start'      => $row['package_customer_start'],
                        'end'        => $row['package_customer_end'],
                        'purchased'  => DateTimeService::getCustomDateTimeFromUtc(
                            $row['package_customer_purchased']
                        ),
                        'status'     => $row['package_customer_status'],
                    ]
                ];
            }
        }

        /** @var Collection $collection */
        $collection = new Collection();

        foreach ($packagesCustomersServices as $key => $value) {
            $collection->addItem(
                self::create($value),
                $key
            );
        }

        return $collection;
    }
}
