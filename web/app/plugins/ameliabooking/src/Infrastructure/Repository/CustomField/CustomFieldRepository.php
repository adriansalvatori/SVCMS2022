<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Repository\CustomField;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\CustomField\CustomField;
use AmeliaBooking\Domain\Factory\CustomField\CustomFieldFactory;
use AmeliaBooking\Domain\Repository\CustomField\CustomFieldRepositoryInterface;
use AmeliaBooking\Infrastructure\Connection;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;

/**
 * Class CouponRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Coupon
 */
class CustomFieldRepository extends AbstractRepository implements CustomFieldRepositoryInterface
{

    const FACTORY = CustomFieldFactory::class;

    /** @var string */
    private $customFieldsOptionsTable;

    /** @var string */
    private $customFieldsServicesTable;

    /** @var string */
    private $customFieldsEventsTable;

    /** @var string */
    private $servicesTable;

    /** @var string */
    private $eventsTable;


    /**
     * @param Connection $connection
     * @param string     $table
     * @param string     $customFieldsOptionsTable
     * @param string     $customFieldsServicesTable
     * @param string     $serviceTable
     * @param string     $customFieldsEventsTable
     * @param string     $eventTable
     */
    public function __construct(
        Connection $connection,
        $table,
        $customFieldsOptionsTable,
        $customFieldsServicesTable,
        $serviceTable,
        $customFieldsEventsTable,
        $eventTable
    ) {
        parent::__construct($connection, $table);
        $this->customFieldsOptionsTable = $customFieldsOptionsTable;
        $this->customFieldsServicesTable = $customFieldsServicesTable;
        $this->servicesTable = $serviceTable;
        $this->customFieldsEventsTable = $customFieldsEventsTable;
        $this->eventsTable = $eventTable;
    }

    /**
     * @param CustomField $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':label'        => $data['label'],
            ':type'         => $data['type'],
            ':required'     => $data['required'] ? 1 : 0,
            ':position'     => $data['position'],
            ':translations' => $data['translations'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO
                {$this->table}
                (
                `label`, `type`, `required`, `position`, `translations`
                ) VALUES (
                :label, :type, :required, :position, :translations
                )"
            );


            $response = $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__, $e->getCode(), $e);
        }

        if (!$response) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__);
        }

        return $this->connection->lastInsertId();
    }

    /**
     * @param int         $id
     * @param CustomField $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity->toArray();

        $params = [
            ':label'        => $data['label'],
            ':required'     => $data['required'] ? 1 : 0,
            ':position'     => $data['position'],
            ':translations' => $data['translations'],
            ':id'           => $id,
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `label`    = :label,
                `required` = :required,
                `position` = :position,
                `translations` = :translations
                WHERE
                id = :id"
            );

            $response = $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__, $e->getCode(), $e);
        }

        if (!$response) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__);
        }

        return $response;
    }

    /**
     * @return Collection|mixed
     * @throws QueryExecutionException
     */
    public function getAll()
    {
        try {
            $statement = $this->connection->query(
                "SELECT
                    cf.id AS cf_id,
                    cf.label AS cf_label,
                    cf.type AS cf_type,
                    cf.required AS cf_required,
                    cf.position AS cf_position,
                    cf.translations AS cf_translations,
                    cfo.id AS cfo_id,
                    cfo.customFieldId AS cfo_custom_field_id,
                    cfo.label AS cfo_label,
                    cfo.position AS cfo_position,
                    cfo.translations AS cfo_translations,
                    s.id AS s_id,
                    s.name AS s_name,
                    s.description AS s_description,
                    s.color AS s_color,
                    s.price AS s_price,
                    s.status AS s_status,
                    s.categoryId AS s_categoryId,
                    s.minCapacity AS s_minCapacity,
                    s.maxCapacity AS s_maxCapacity,
                    s.duration AS s_duration,
                    e.id AS e_id,
                    e.name AS e_name,
                    e.price AS e_price,
                    e.parentId AS e_parentId
                FROM {$this->table} cf
                LEFT JOIN {$this->customFieldsOptionsTable} cfo ON cfo.customFieldId = cf.id
                LEFT JOIN {$this->customFieldsServicesTable} cfs ON cfs.customFieldId = cf.id
                LEFT JOIN {$this->customFieldsEventsTable} cfe ON cfe.customFieldId = cf.id
                LEFT JOIN {$this->servicesTable} s ON s.id = cfs.serviceId
                LEFT JOIN {$this->eventsTable} e ON e.id = cfe.eventId
                ORDER BY cf.position, cfo.position, cf.position"
            );

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }
}
