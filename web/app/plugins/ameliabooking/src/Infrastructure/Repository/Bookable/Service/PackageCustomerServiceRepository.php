<?php

namespace AmeliaBooking\Infrastructure\Repository\Bookable\Service;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerServiceFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Connection;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\PackagesCustomersTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\CustomerBookingsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Payment\PaymentsTable;

/**
 * Class PackageCustomerServiceRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Bookable\Service
 */
class PackageCustomerServiceRepository extends AbstractRepository
{
    const FACTORY = PackageCustomerServiceFactory::class;

    /** @var string */
    protected $packagesCustomersTable;

    /** @var string */
    protected $paymentsTable;

    /**
     * @param Connection $connection
     * @param string     $table
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        Connection $connection,
        $table
    ) {
        parent::__construct($connection, $table);

        $this->packagesCustomersTable = PackagesCustomersTable::getTableName();

        $this->paymentsTable = PaymentsTable::getTableName();
    }

    /**
     * @param PackageCustomerService $entity
     *
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':packageCustomerId' => $data['packageCustomer']['id'],
            ':serviceId'         => $data['serviceId'],
            ':providerId'        => $data['providerId'],
            ':locationId'        => $data['locationId'],
            ':bookingsCount'     => $data['bookingsCount'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table}
                (`packageCustomerId`, `serviceId`, `providerId`, `locationId`, `bookingsCount`)
                VALUES
                (:packageCustomerId, :serviceId, :providerId, :locationId, :bookingsCount)"
            );

            $res = $statement->execute($params);
            if (!$res) {
                throw new QueryExecutionException('Unable to add data in ' . __CLASS__);
            }
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__, $e->getCode(), $e);
        }

        return $this->connection->lastInsertId();
    }

    /**
     * @param array $criteria
     * @param bool  $empty
     * @return Collection
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function getByCriteria($criteria, $empty = false)
    {
        $bookingsTable = CustomerBookingsTable::getTableName();

        $params = [];

        $where = [];

        if (!empty($criteria['ids'])) {
            $queryIds = [];

            foreach ($criteria['ids'] as $index => $value) {
                $param = ':id' . $index;

                $queryIds[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'pcs.id IN (' . implode(', ', $queryIds) . ')';
        }

        if (!empty($criteria['purchased'])) {
            $where[] = "(DATE_FORMAT(pc.purchased, '%Y-%m-%d %H:%i:%s') BETWEEN :purchasedFrom AND :purchasedTo)";

            $params[':purchasedFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['purchased'][0]);

            $params[':purchasedTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['purchased'][1]);
        }

        if (!empty($criteria['dates'])) {
            $where[] = "((:from1 >= DATE_FORMAT(pc.start, '%Y-%m-%d %H:%i:%s') AND
            :from2 <= DATE_FORMAT(pc.end, '%Y-%m-%d %H:%i:%s')
            ) OR (
            :from3 <= DATE_FORMAT(pc.start, '%Y-%m-%d %H:%i:%s') AND
            :to1 >= DATE_FORMAT(pc.start, '%Y-%m-%d %H:%i:%s'))) ";

            $params[':from1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $params[':from2'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $params[':from3'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);

            $params[':to1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
        }

        if (!empty($criteria['customerId'])) {
            $params[':customerId'] = $criteria['customerId'];

            $where[] = 'pc.customerId = :customerId';
        }

        if (!empty($criteria['services'])) {
            $queryServices = [];

            foreach ($criteria['services'] as $index => $value) {
                $param = ':service' . $index;

                $queryServices[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'pcs.serviceId IN (' . implode(', ', $queryServices) . ')';
        }

        if (!empty($criteria['packages'])) {
            $queryServices = [];

            foreach ($criteria['packages'] as $index => $value) {
                $param = ':package' . $index;

                $queryServices[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'pc.packageId IN (' . implode(', ', $queryServices) . ')';
        }

        if (!empty($criteria['packagesCustomers'])) {
            $queryServices = [];

            foreach ($criteria['packagesCustomers'] as $index => $value) {
                $param = ':packageCustomerId' . $index;

                $queryServices[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'pc.id IN (' . implode(', ', $queryServices) . ')';
        }

        if ($empty) {
            $where[] = 'pcs.id NOT IN (SELECT packageCustomerServiceId FROM ' . $bookingsTable . ' WHERE packageCustomerServiceId IS NOT NULL)';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    pc.id AS package_customer_id,
                    pc.packageId AS package_customer_packageId,
                    pc.customerId AS package_customer_customerId,
                    pc.price AS package_customer_price,
                    pc.end AS package_customer_end,
                    pc.start AS package_customer_start,
                    pc.purchased AS package_customer_purchased,
                    pc.status AS package_customer_status,
                    
                    pcs.id AS package_customer_service_id,
                    pcs.serviceId AS package_customer_service_serviceId,
                    pcs.providerId AS package_customer_service_providerId,
                    pcs.locationId AS package_customer_service_locationId,
                    pcs.bookingsCount AS package_customer_service_bookingsCount,
                    
                    p.id AS payment_id,
                    p.packageCustomerId AS payment_packageCustomerId,
                    p.amount AS payment_amount,
                    p.dateTime AS payment_dateTime,
                    p.status AS payment_status,
                    p.gateway AS payment_gateway,
                    p.gatewayTitle AS payment_gatewayTitle,
                    p.data AS payment_data,
                    p.wcOrderId AS payment_wcOrderId
                FROM {$this->table} pcs
                INNER JOIN {$this->packagesCustomersTable} pc ON pcs.packageCustomerId = pc.id
                LEFT JOIN {$this->paymentsTable} p ON p.packageCustomerId = pc.id
                {$where}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }
}
