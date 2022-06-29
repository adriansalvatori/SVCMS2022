<?php

namespace AmeliaBooking\Infrastructure\Repository\Bookable\Service;

use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;

/**
 * Class PackageCustomerRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Bookable\Service
 */
class PackageCustomerRepository extends AbstractRepository
{
    const FACTORY = PackageCustomerFactory::class;

    /**
     * @param PackageCustomer $entity
     *
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':packageId'        => $data['packageId'],
            ':customerId'       => $data['customerId'],
            ':price'            => $data['price'],
            ':start'            => $data['start'],
            ':end'              => $data['end'],
            ':purchased'        => $data['purchased'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table}
                (`packageId`, `customerId`, `price`, `start`, `end`, `purchased`, `status`)
                VALUES
                (:packageId, :customerId, :price, :start, :end, :purchased, 'approved')"
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
}
