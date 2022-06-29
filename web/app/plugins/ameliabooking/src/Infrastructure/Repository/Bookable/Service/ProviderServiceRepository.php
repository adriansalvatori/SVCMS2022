<?php

namespace AmeliaBooking\Infrastructure\Repository\Bookable\Service;

use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Factory\Bookable\Service\ServiceFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;

/**
 * Class ProviderServiceRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Bookable\Service
 */
class ProviderServiceRepository extends AbstractRepository
{
    const FACTORY = ServiceFactory::class;

    /**
     * @param Service $entity
     * @param int     $userId
     *
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity, $userId)
    {
        $data = $entity->toArray();

        $params = [
            ':userId'      => $userId,
            ':serviceId'   => $data['id'],
            ':minCapacity' => $data['minCapacity'],
            ':maxCapacity' => $data['maxCapacity'],
            ':price'       => $data['price']
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table}
                (`userId`, `serviceId`, `minCapacity`, `maxCapacity`, `price`)
                VALUES
                (:userId, :serviceId, :minCapacity, :maxCapacity, :price)"
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
     * @param Service $entity
     * @param int     $id
     *
     * @return int
     * @throws QueryExecutionException
     */
    public function update($entity, $id)
    {
        $data = $entity->toArray();

        $params = [
            ':id'          => $id,
            ':minCapacity' => $data['minCapacity'],
            ':maxCapacity' => $data['maxCapacity'],
            ':price'       => $data['price']
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET `minCapacity` = :minCapacity, `maxCapacity` = :maxCapacity, `price` = :price
                WHERE id = :id"
            );

            $res = $statement->execute($params);
            if (!$res) {
                throw new QueryExecutionException('Unable to save data in ' . __CLASS__);
            }
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__, $e->getCode(), $e);
        }

        return $this->connection->lastInsertId();
    }

    /**
     * @param $serviceId
     *
     * @return array
     * @throws QueryExecutionException
     */
    public function getAllForService($serviceId)
    {
        try {
            $statement = $this->connection->prepare("SELECT
                ps.id,
                ps.userId,
                ps.serviceId,
                ps.minCapacity,
                ps.maxCapacity,
                ps.price
              FROM {$this->table} ps 
              WHERE ps.serviceId = :serviceId");

            $params = [
                ':serviceId' => $serviceId
            ];

            $statement->execute($params);

            $rows = $statement->fetchAll();

            foreach ($rows as &$row) {
                $row['id'] = (int)$row['id'];
                $row['userId'] = (int)$row['userId'];
                $row['serviceId'] = (int)$row['serviceId'];
                $row['minCapacity'] = (int)$row['minCapacity'];
                $row['maxCapacity'] = (int)$row['maxCapacity'];
            }

            return $rows;
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     * @param $providerId
     *
     * @return array
     * @throws QueryExecutionException
     */
    public function getAllForProvider($providerId)
    {
        try {
            $statement = $this->connection->prepare("SELECT
                ps.id,
                ps.userId,
                ps.serviceId,
                ps.minCapacity,
                ps.maxCapacity,
                ps.price
              FROM {$this->table} ps 
              WHERE ps.userId = :providerId");

            $params = array(
                ':providerId' => $providerId
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();

            foreach ($rows as &$row) {
                $row['id'] = (int)$row['id'];
                $row['userId'] = (int)$row['userId'];
                $row['serviceId'] = (int)$row['serviceId'];
                $row['minCapacity'] = (int)$row['minCapacity'];
                $row['maxCapacity'] = (int)$row['maxCapacity'];
            }

            return $rows;
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     *
     * It will delete all relations for one service except ones that are sent in providers array
     *
     * @param array $providersIds
     * @param int   $serviceId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function deleteAllNotInProvidersArrayForService($providersIds, $serviceId)
    {
        $providers = ' ';

        if (!empty($providersIds)) {
            foreach ($providersIds as $index => $value) {
                ++$index;
                $providers .= ':providerId' . $index . ', ';
                $params[':providerId' . $index] = (int)$value;
            }
            $providers = 'AND `userId` NOT IN (' . rtrim($providers, ', ') . ')';
        }

        $params[':serviceId'] = $serviceId;

        try {
            $statement = $this->connection->prepare(
                "DELETE FROM {$this->table} WHERE 1 = 1 $providers AND serviceId = :serviceId"
            );

            return $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to delete data from ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     *
     * It will delete all relations for one service except ones that are sent in providers array
     *
     * @param array $servicesIds
     * @param int   $providerId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function deleteAllNotInServicesArrayForProvider($servicesIds, $providerId)
    {
        $services = ' ';

        if (!empty($servicesIds)) {
            foreach ($servicesIds as $index => $value) {
                ++$index;
                $services .= ':serviceId' . $index . ', ';
                $params[':serviceId' . $index] = $value;
            }
            $services = 'AND `serviceId` NOT IN (' . rtrim($services, ', ') . ')';
        }

        $params[':providerId'] = $providerId;

        try {
            $statement = $this->connection->prepare(
                "DELETE FROM {$this->table} WHERE 1 = 1 $services AND userId = :providerId"
            );

            return $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to delete data from ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     * @param Service $entity
     * @param int     $serviceId
     *
     * @return boolean
     * @throws QueryExecutionException
     */
    public function updateServiceForAllProviders($entity, $serviceId)
    {
        $data = $entity->toArray();

        $params = [
            ':serviceId'   => $serviceId,
            ':minCapacity' => $data['minCapacity'],
            ':maxCapacity' => $data['maxCapacity'],
            ':price'       => $data['price']
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET `minCapacity` = :minCapacity, `maxCapacity` = :maxCapacity, `price` = :price
                WHERE serviceId = :serviceId"
            );

            $res = $statement->execute($params);
            if (!$res) {
                throw new QueryExecutionException('Unable to save data in ' . __CLASS__);
            }
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__, $e->getCode(), $e);
        }

        return true;
    }
}
