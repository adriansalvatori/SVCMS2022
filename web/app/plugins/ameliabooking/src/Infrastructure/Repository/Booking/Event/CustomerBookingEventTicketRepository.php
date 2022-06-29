<?php

namespace AmeliaBooking\Infrastructure\Repository\Booking\Event;

use AmeliaBooking\Domain\Entity\Booking\Event\CustomerBookingEventTicket;
use AmeliaBooking\Domain\Factory\Booking\Event\CustomerBookingEventTicketFactory;
use AmeliaBooking\Domain\Repository\Booking\Event\EventRepositoryInterface;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;

/**
 * Class CustomerBookingEventTicketRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Booking\Event
 */
class CustomerBookingEventTicketRepository extends AbstractRepository implements EventRepositoryInterface
{

    const FACTORY = CustomerBookingEventTicketFactory::class;

    /**
     * @param CustomerBookingEventTicket $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':eventTicketId'        => $data['eventTicketId'],
            ':customerBookingId'    => $data['customerBookingId'],
            ':price'                => $data['price'],
            ':persons'              => $data['persons']
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table} 
                (
                `eventTicketId`,
                `customerBookingId`,
                `price`,
                `persons`
                )
                VALUES (
                :eventTicketId,
                :customerBookingId,
                :price,
                :persons
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
     * @param Event   $entity
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity;

        $params = [
            ':id'                   => $id,
            ':eventTicketId'        => $data['eventTicketId'],
            ':customerBookingId'    => $data['customerBookingId'],
            ':price'                => $data['price'],
            ':persons'              => $data['persons'],
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `eventTicketId` = :eventTicketId,
                `customerBookingId` = :customerBookingId,
                `price` = :price,
                `persons` = :persons
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
