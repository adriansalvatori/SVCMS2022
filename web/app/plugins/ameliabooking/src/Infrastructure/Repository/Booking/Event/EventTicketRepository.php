<?php

namespace AmeliaBooking\Infrastructure\Repository\Booking\Event;

use AmeliaBooking\Domain\Entity\Booking\Event\EventTicket;
use AmeliaBooking\Domain\Factory\Booking\Event\EventTicketFactory;
use AmeliaBooking\Domain\Repository\Booking\Event\EventRepositoryInterface;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;

/**
 * Class EventTicketRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Booking\Event
 */
class EventTicketRepository extends AbstractRepository implements EventRepositoryInterface
{

    const FACTORY = EventTicketFactory::class;

    /**
     * @param EventTicket $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':eventId'        => $data['eventId'],
            ':name'           => $data['name'],
            ':enabled'        => $data['enabled'] ? 1 : 0,
            ':price'          => $data['price'],
            ':spots'          => $data['spots'],
            ':dateRanges'     => $data['dateRanges'],
            ':translations'   => $data['translations'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table} 
                (
                `eventId`,
                `name`,
                `enabled`,
                `price`,
                `spots`,
                `dateRanges`,
                `translations`
                )
                VALUES (
                :eventId,
                :name,
                :enabled,
                :price,
                :spots,
                :dateRanges,
                :translations
                )"
            );

            $res = $statement->execute($params);

            if (!$res) {
                throw new QueryExecutionException('Unable to add data in ' . __CLASS__);
            }

            return $this->connection->lastInsertId();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     * @param int         $id
     * @param EventTicket $entity
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity->toArray();

        $params = [
            ':id'           => $id,
            ':eventId'      => $data['eventId'],
            ':name'         => $data['name'],
            ':enabled'      => $data['enabled'] ? 1 : 0,
            ':price'        => $data['price'],
            ':spots'        => $data['spots'],
            ':dateRanges'   => $data['dateRanges'],
            ':translations' => $data['translations'],
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `eventId` = :eventId,
                `name` = :name,
                `enabled` = :enabled,
                `price` = :price,
                `spots` = :spots,
                `dateRanges` = :dateRanges,
                `translations` = :translations
                WHERE id = :id"
            );

            $res = $statement->execute($params);

            if (!$res) {
                throw new QueryExecutionException('Unable to save data in ' . __CLASS__);
            }

            return $res;
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__, $e->getCode(), $e);
        }
    }

    /**
     * @param int eventId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function deleteByEventId($eventId)
    {
        try {
            $statement = $this->connection->prepare("DELETE FROM {$this->table} WHERE eventId = :eventId");
            $statement->bindParam(':eventId', $eventId);
            return $statement->execute();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to delete data from ' . __CLASS__, $e->getCode(), $e);
        }
    }
}
