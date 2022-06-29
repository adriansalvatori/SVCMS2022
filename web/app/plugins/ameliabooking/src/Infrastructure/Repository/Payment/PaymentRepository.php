<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Repository\Payment;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Factory\Payment\PaymentFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;
use AmeliaBooking\Domain\Repository\Payment\PaymentRepositoryInterface;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Connection;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable\PackagesCustomersServicesTable;

/**
 * Class PaymentRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Payment
 */
class PaymentRepository extends AbstractRepository implements PaymentRepositoryInterface
{
    /** @var string */
    protected $appointmentsTable;

    /** @var string */
    protected $bookingsTable;

    /** @var string */
    protected $servicesTable;

    /** @var string */
    protected $usersTable;

    /** @var string */
    protected $eventsTable;

    /** @var string */
    protected $eventsProvidersTable;

    /** @var string */
    protected $eventsPeriodsTable;

    /** @var string */
    protected $customerBookingsToEventsPeriodsTable;

    /** @var string */
    protected $packagesTable;

    /** @var string */
    protected $packagesCustomersTable;

    /** @var string */
    protected $packagesCustomersServiceTable;


    /**
     * @param Connection $connection
     * @param string     $table
     * @param string     $appointmentsTable
     * @param string     $bookingsTable
     * @param string     $servicesTable
     * @param string     $usersTable
     * @param string     $eventsTable
     * @param string     $eventsProvidersTable
     * @param string     $eventsPeriodsTable
     * @param string     $customerBookingsToEventsPeriodsTable
     * @param string     $packagesTable
     * @param string     $packagesCustomersTable
     */
    public function __construct(
        Connection $connection,
        $table,
        $appointmentsTable,
        $bookingsTable,
        $servicesTable,
        $usersTable,
        $eventsTable,
        $eventsProvidersTable,
        $eventsPeriodsTable,
        $customerBookingsToEventsPeriodsTable,
        $packagesTable,
        $packagesCustomersTable
    ) {
        parent::__construct($connection, $table);

        $this->appointmentsTable = $appointmentsTable;
        $this->bookingsTable = $bookingsTable;
        $this->servicesTable = $servicesTable;
        $this->usersTable = $usersTable;
        $this->eventsTable = $eventsTable;
        $this->eventsProvidersTable = $eventsProvidersTable;
        $this->eventsPeriodsTable = $eventsPeriodsTable;
        $this->customerBookingsToEventsPeriodsTable = $customerBookingsToEventsPeriodsTable;
        $this->packagesTable = $packagesTable;
        $this->packagesCustomersTable = $packagesCustomersTable;
        $this->packagesCustomersServiceTable = PackagesCustomersServicesTable::getTableName();
    }

    const FACTORY = PaymentFactory::class;

    /**
     * @param Payment $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':customerBookingId' => $data['customerBookingId'] ? $data['customerBookingId'] : null,
            ':packageCustomerId' => $data['packageCustomerId'] ? $data['packageCustomerId'] : null,
            ':parentId'          => $data['parentId'] ? $data['parentId'] : null,
            ':amount'            => $data['amount'],
            ':dateTime'          => DateTimeService::getCustomDateTimeInUtc($data['dateTime']),
            ':status'            => $data['status'],
            ':gateway'           => $data['gateway'],
            ':gatewayTitle'      => $data['gatewayTitle'],
            ':data'              => $data['data'],
            ':entity'            => $data['entity'],
            ':created'           => DateTimeService::getNowDateTimeInUtc(),
            ':wcOrderId'         => !empty($data['wcOrderId']) ? $data['wcOrderId'] : null,
        ];

        if ($data['parentId']) {
            $params[':actionsCompleted'] = null;
        } else {
            $params[':actionsCompleted'] = !empty($data['actionsCompleted']) ? 1 : 0;
        }

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO
                {$this->table} 
                (
                `customerBookingId`, `packageCustomerId`, `parentId`, `amount`, `dateTime`, `status`, `gateway`, `gatewayTitle`, `data`, `entity`, `actionsCompleted`, `created`, `wcOrderId`
                ) VALUES (
                :customerBookingId, :packageCustomerId, :parentId, :amount, :dateTime, :status, :gateway, :gatewayTitle, :data, :entity, :actionsCompleted, :created, :wcOrderId
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
     * @param int     $id
     * @param Payment $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity->toArray();

        $params = [
            ':customerBookingId' => $data['customerBookingId'] ? $data['customerBookingId'] : null,
            ':packageCustomerId' => $data['packageCustomerId'] ? $data['packageCustomerId'] : null,
            ':parentId'          => $data['parentId'] ? $data['parentId'] : null,
            ':amount'            => $data['amount'],
            ':dateTime'          => DateTimeService::getCustomDateTimeInUtc($data['dateTime']),
            ':status'            => $data['status'],
            ':gateway'           => $data['gateway'],
            ':gatewayTitle'      => $data['gatewayTitle'],
            ':data'              => $data['data'],
            ':id'                => $id,
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `customerBookingId` = :customerBookingId,
                `packageCustomerId` = :packageCustomerId,
                `parentId`          = :parentId,
                `amount`            = :amount,
                `dateTime`          = :dateTime,
                `status`            = :status,
                `gateway`           = :gateway,
                `gatewayTitle`      = :gatewayTitle,
                `data`              = :data
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
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     */
    public function getByCriteria($criteria)
    {
        $result = new Collection();

        $params = [];

        $where = [];

        if (!empty($criteria['bookingIds'])) {
            $queryBookings = [];

            foreach ($criteria['bookingIds'] as $index => $value) {
                $param = ':id' . $index;

                $queryBookings[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'customerBookingId IN (' . implode(', ', $queryBookings) . ')';
        }

        if (!empty($criteria['ids'])) {
            $queryIds = [];

            foreach ($criteria['ids'] as $index => $value) {
                $param = ':id' . $index;

                $queryIds[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'id IN (' . implode(', ', $queryIds) . ')';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    id AS id,
                    customerBookingId AS customerBookingId,
                    packageCustomerId AS packageCustomerId,
                    parentId AS parentId,
                    amount AS amount,
                    dateTime AS dateTime,
                    status AS status,
                    gateway AS gateway,
                    gatewayTitle AS gatewayTitle,
                    data AS data
                FROM {$this->table}
                {$where}"
            );

            $statement->execute($params);

            while ($row = $statement->fetch()) {
                $result->addItem(call_user_func([static::FACTORY, 'create'], $row), $row['id']);
            }
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * @param array $criteria
     * @param int   $itemsPerPage
     *
     * @return array
     * @throws QueryExecutionException
     */
    public function getFiltered($criteria, $itemsPerPage = null)
    {
        $params = [];
        $appointmentParams1 = [];
        $appointmentParams2 = [];
        $eventParams = [];
        $whereAppointment1 = [];
        $whereAppointment2 = [];
        $whereEvent = [];

        if ($criteria['dates']) {
            $whereAppointment1[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentAppointmentFrom1 AND :paymentAppointmentTo1)";
            $whereAppointment2[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentAppointmentFrom2 AND :paymentAppointmentTo2)";
            $appointmentParams1[':paymentAppointmentFrom1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $appointmentParams2[':paymentAppointmentFrom2'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $appointmentParams1[':paymentAppointmentTo1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            $appointmentParams2[':paymentAppointmentTo2'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);

            $whereEvent[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentEventFrom AND :paymentEventTo)";
            $eventParams[':paymentEventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $eventParams[':paymentEventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
        }

        if (!empty($criteria['customerId'])) {
            $appointmentParams1[':customerAppointmentId1'] = $criteria['customerId'];
            $appointmentParams2[':customerAppointmentId2'] = $criteria['customerId'];
            $whereAppointment1[] = 'cb.customerId = :customerAppointmentId1';
            $whereAppointment2[] = 'pc.customerId = :customerAppointmentId2';

            $eventParams[':customerEventId'] = $criteria['customerId'];
            $whereEvent[] = 'cb.customerId = :customerEventId';
        }

        if (!empty($criteria['providerId'])) {
            $appointmentParams1[':providerAppointmentId1'] = $criteria['providerId'];
            $appointmentParams1[':providerAppointmentId2'] = $criteria['providerId'];
            $whereAppointment1[] = 'a.providerId = :providerAppointmentId1';
            $whereAppointment2[] = 'a.providerId = :providerAppointmentId2';

            $eventParams[':providerEventId'] = $criteria['providerId'];
            $whereEvent[] = 'epu.userId = :providerEventId';
        }

        if (!empty($criteria['services'])) {
            $queryServices1 = [];
            $queryServices2 = [];

            foreach ((array)$criteria['services'] as $index => $value) {
                $param1 = ':service0' . $index;
                $param2 = ':service1' . $index;
                $queryServices1[] = $param1;
                $queryServices2[] = $param2;
                $appointmentParams1[$param1] = $value;
                $appointmentParams2[$param2] = $value;
            }

            $whereAppointment1[] = 'a.serviceId IN (' . implode(', ', $queryServices1) . ')';
            $whereAppointment2[] = 'a.serviceId IN (' . implode(', ', $queryServices2) . ')';
        }

        if (!empty($criteria['status'])) {
            $appointmentParams1[':statusAppointment1'] = $criteria['status'];
            $appointmentParams2[':statusAppointment2'] = $criteria['status'];
            $whereAppointment1[] = 'p.status = :statusAppointment1';
            $whereAppointment2[] = 'p.status = :statusAppointment2';

            $eventParams[':statusEvent'] = $criteria['status'];
            $whereEvent[] = 'p.status = :statusEvent';
        }

        if (!empty($criteria['events'])) {
            $queryEvents = [];

            foreach ((array)$criteria['events'] as $index => $value) {
                $param = ':event' . $index;
                $queryEvents[] = $param;
                $eventParams[$param] = $value;
            }

            $whereEvent[] = "p.customerBookingId IN (SELECT cbe.customerBookingId
              FROM {$this->eventsTable} e
              INNER JOIN {$this->eventsPeriodsTable} ep ON ep.eventId = e.id
              INNER JOIN {$this->customerBookingsToEventsPeriodsTable} cbe ON cbe.eventPeriodId = ep.id 
              WHERE e.id IN (" . implode(', ', $queryEvents) . '))';
        }

        $whereAppointment1 = $whereAppointment1 ? ' AND ' . implode(' AND ', $whereAppointment1) : '';
        $whereAppointment2 = $whereAppointment2 ? ' AND ' . implode(' AND ', $whereAppointment2) : '';
        $whereEvent = $whereEvent ? ' AND ' . implode(' AND ', $whereEvent) : '';

        $appointmentQuery1 = "SELECT
                p.id AS id,
                p.customerBookingId AS customerBookingId,
                NULL AS packageCustomerId,
                p.amount AS amount,
                p.dateTime AS dateTime,
                p.status AS status,
                p.wcOrderId AS wcOrderId,
                p.gateway AS gateway,
                p.gatewayTitle AS gatewayTitle,
                NULL AS packageId,
                cb.price AS bookedPrice,
                a.providerId AS providerId,
                cb.customerId AS customerId,
                cb.info AS info,
                a.serviceId AS serviceId,
                a.id AS appointmentId,
                a.bookingStart AS bookingStart,
                s.name AS bookableName,
                cu.firstName AS customerFirstName,
                cu.lastName AS customerLastName,
                cu.email AS customerEmail,
                pu.firstName AS providerFirstName,
                pu.lastName AS providerLastName,
                pu.email AS providerEmail
            FROM {$this->table} p
            INNER JOIN {$this->bookingsTable} cb ON cb.id = p.customerBookingId
            INNER JOIN {$this->appointmentsTable} a ON a.id = cb.appointmentId
            INNER JOIN {$this->servicesTable} s ON s.id = a.serviceId
            INNER JOIN {$this->usersTable} cu ON cu.id = cb.customerId
            INNER JOIN {$this->usersTable} pu ON pu.id = a.providerId
            WHERE 1=1 {$whereAppointment1}";

        $appointmentQuery2 = "SELECT
                p.id AS id,
                NULL AS customerBookingId,
                p.packageCustomerId AS packageCustomerId,
                p.amount AS amount,
                p.dateTime AS dateTime,
                p.status AS status,
                p.wcOrderId AS wcOrderId,
                p.gateway AS gateway,
                p.gatewayTitle AS gatewayTitle,
                pc.packageId AS packageId,
                pc.price AS bookedPrice,
                NULL AS providerId,
                pc.customerId AS customerId,
                cb.info AS info,
                NULL AS serviceId,
                NULL AS appointmentId,
                NULL AS bookingStart,
                pa.name AS bookableName,
                cu.firstName AS customerFirstName,
                cu.lastName AS customerLastName,
                cu.email AS customerEmail,
                '' AS providerFirstName,
                '' AS providerLastName,
                '' AS providerEmail
            FROM {$this->table} p
            INNER JOIN {$this->packagesCustomersTable} pc ON p.packageCustomerId = pc.id
            INNER JOIN {$this->usersTable} cu ON cu.id = pc.customerId
            INNER JOIN {$this->packagesTable} pa ON pa.id = pc.packageId
            INNER JOIN {$this->packagesCustomersServiceTable} pcs ON pc.id = pcs.packageCustomerId
            LEFT JOIN {$this->bookingsTable} cb ON cb.packageCustomerServiceId = pcs.id
            LEFT JOIN {$this->appointmentsTable} a ON a.id = cb.appointmentId
            WHERE 1=1 {$whereAppointment2} GROUP BY packageCustomerId";

        $eventQuery = "SELECT
                p.id AS id,
                p.customerBookingId AS customerBookingId,
                NULL AS packageCustomerId,
                p.amount AS amount,
                p.dateTime AS dateTime,
                p.status AS status,
                p.wcOrderId AS wcOrderId,
                p.gateway AS gateway,
                p.gatewayTitle AS gatewayTitle,
                NULL AS packageId,
                cb.price AS bookedPrice,
                NULL AS providerId,
                cb.customerId AS customerId,
                cb.info AS info,
                NULL AS serviceId,
                NULL AS appointmentId,
                NULL AS bookingStart,
                NULL AS bookableName,
                cu.firstName AS customerFirstName,
                cu.lastName AS customerLastName,
                cu.email AS customerEmail,
                NULL AS providerFirstName,
                NULL AS providerLastName,
                NULL AS providerEmail
            FROM {$this->table} p
            INNER JOIN {$this->bookingsTable} cb ON cb.id = p.customerBookingId
            INNER JOIN {$this->usersTable} cu ON cu.id = cb.customerId
            INNER JOIN {$this->customerBookingsToEventsPeriodsTable} cbe ON cbe.customerBookingId = cb.id
            INNER JOIN {$this->eventsPeriodsTable} ep ON ep.id = cbe.eventPeriodId
            LEFT JOIN {$this->eventsProvidersTable} epu ON epu.eventId = ep.eventId
            WHERE 1=1 {$whereEvent} GROUP BY customerBookingId";

        if (isset($criteria['events'], $criteria['services'])) {
            return [];
        } elseif (isset($criteria['services'])) {
            $paymentQuery = "({$appointmentQuery1}) UNION ALL ({$appointmentQuery2})";
            $params = array_merge($params, $appointmentParams1, $appointmentParams2);
        } elseif (isset($criteria['events'])) {
            $paymentQuery = "{$eventQuery}";
            $params = array_merge($params, $eventParams);
        } else {
            $paymentQuery = "({$appointmentQuery1}) UNION ALL ({$appointmentQuery2}) UNION ALL ({$eventQuery})";
            $params = array_merge($params, $appointmentParams1, $appointmentParams2, $eventParams);
        }

        $limit = $this->getLimit(
            !empty($criteria['page']) ? (int)$criteria['page'] : 0,
            (int)$itemsPerPage
        );

        try {
            $statement = $this->connection->prepare(
                "{$paymentQuery}
                ORDER BY dateTime, id
                {$limit}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        $result = [];

        foreach ($rows as &$row) {
            $customerInfo = $row['info'] ? json_decode($row['info'], true) : null;

            $result[(int)$row['id']] = [
                'id' =>  (int)$row['id'],
                'dateTime' =>  DateTimeService::getCustomDateTimeFromUtc($row['dateTime']),
                'bookingStart' =>  DateTimeService::getCustomDateTimeFromUtc($row['bookingStart']),
                'status' =>  $row['status'],
                'wcOrderId' =>  $row['wcOrderId'],
                'gateway' =>  $row['gateway'],
                'gatewayTitle' =>  $row['gatewayTitle'],
                'name' => $row['bookableName'],
                'customerBookingId' =>  (int)$row['customerBookingId'] ? (int)$row['customerBookingId'] : null,
                'packageCustomerId' =>  (int)$row['packageCustomerId'] ? (int)$row['packageCustomerId'] : null,
                'amount' =>  (float)$row['amount'],
                'providers' =>  (int)$row['providerId'] ? [
                    [
                        'id' => (int)$row['providerId'],
                        'fullName' => $row['providerFirstName'] . ' ' . $row['providerLastName'],
                        'email' => $row['providerEmail'],
                    ]
                ] : [],
                'customerId' =>  (int)$row['customerId'],
                'serviceId' =>  (int)$row['serviceId'] ? (int)$row['serviceId'] : null,
                'appointmentId' =>  (int)$row['appointmentId'] ? (int)$row['appointmentId'] : null,
                'packageId' =>  (int)$row['packageId'] ? (int)$row['packageId'] : null,
                'bookedPrice' =>  $row['bookedPrice'] ? $row['bookedPrice'] : null,
                'bookableName' => $row['bookableName'],
                'customerFirstName' => $customerInfo ? $customerInfo['firstName'] : $row['customerFirstName'],
                'customerLastName' => $customerInfo ? $customerInfo['lastName'] : $row['customerLastName'],
                'info' => $row['info'],
                'customerEmail' => $row['customerEmail']
            ];
        }

        return $result;
    }

    /**
     * @param array $criteria
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function getCount($criteria)
    {
        $params = [];
        $appointmentParams1 = [];
        $appointmentParams2 = [];
        $eventParams = [];
        $whereAppointment1 = [];
        $whereAppointment2 = [];
        $whereEvent = [];

        if (isset($criteria['dates'])) {
            $whereAppointment1[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentAppointmentFrom1 AND :paymentAppointmentTo1)";
            $whereAppointment2[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentAppointmentFrom2 AND :paymentAppointmentTo2)";
            $appointmentParams1[':paymentAppointmentFrom1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $appointmentParams1[':paymentAppointmentTo1'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            $appointmentParams2[':paymentAppointmentFrom2'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $appointmentParams2[':paymentAppointmentTo2'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);

            $whereEvent[] = "(DATE_FORMAT(p.dateTime, '%Y-%m-%d %H:%i:%s') BETWEEN :paymentEventFrom AND :paymentEventTo)";
            $eventParams[':paymentEventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            $eventParams[':paymentEventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
        }

        if (!empty($criteria['customerId'])) {
            $appointmentParams1[':customerAppointmentId1'] = $criteria['customerId'];
            $appointmentParams2[':customerAppointmentId2'] = $criteria['customerId'];
            $whereAppointment1[] = 'cb.customerId = :customerAppointmentId1';
            $whereAppointment2[] = 'pc.customerId = :customerAppointmentId2';

            $eventParams[':customerEventId'] = $criteria['customerId'];
            $whereEvent[] = 'cb.customerId = :customerEventId';
        }

        if (!empty($criteria['providerId'])) {
            $appointmentParams1[':providerAppointmentId'] = $criteria['providerId'];
            $whereAppointment1[] = 'a.providerId = :providerAppointmentId';

            $eventParams[':providerEventId'] = $criteria['providerId'];
            $whereEvent[] = 'epu.userId = :providerEventId';
        }

        if (!empty($criteria['services'])) {
            $queryServices = [];

            foreach ((array)$criteria['services'] as $index => $value) {
                $param = ':service' . $index;
                $queryServices[] = $param;
                $appointmentParams1[$param] = $value;
            }

            $whereAppointment1[] = 'a.serviceId IN (' . implode(', ', $queryServices) . ')';
        }

        if (!empty($criteria['status'])) {
            $appointmentParams1[':statusAppointment1'] = $criteria['status'];
            $appointmentParams2[':statusAppointment2'] = $criteria['status'];
            $whereAppointment1[] = 'p.status = :statusAppointment1';
            $whereAppointment2[] = 'p.status = :statusAppointment2';

            $eventParams[':statusEvent'] = $criteria['status'];
            $whereEvent[] = 'p.status = :statusEvent';
        }

        if (!empty($criteria['events'])) {
            $queryEvents = [];

            foreach ((array)$criteria['events'] as $index => $value) {
                $param = ':event' . $index;
                $queryEvents[] = $param;
                $eventParams[$param] = $value;
            }

            $whereEvent[] = "p.customerBookingId IN (SELECT cbe.customerBookingId
              FROM {$this->eventsTable} e
              INNER JOIN {$this->eventsPeriodsTable} ep ON ep.eventId = e.id
              INNER JOIN {$this->customerBookingsToEventsPeriodsTable} cbe ON cbe.eventPeriodId = ep.id 
              WHERE e.id IN (" . implode(', ', $queryEvents) . '))';
        }

        $whereAppointment1 = $whereAppointment1 ? ' AND ' . implode(' AND ', $whereAppointment1) : '';
        $whereAppointment2 = $whereAppointment2 ? ' AND ' . implode(' AND ', $whereAppointment2) : '';
        $whereEvent = $whereEvent ? ' AND ' . implode(' AND ', $whereEvent) : '';

        $appointmentQuery1 = "SELECT
                COUNT(DISTINCT(p.id)) AS appointmentsCount1,
                0 AS appointmentsCount2,
                0 AS eventsCount
            FROM {$this->table} p
            INNER JOIN {$this->bookingsTable} cb ON cb.id = p.customerBookingId
            INNER JOIN {$this->appointmentsTable} a ON a.id = cb.appointmentId
            INNER JOIN {$this->servicesTable} s ON s.id = a.serviceId
            INNER JOIN {$this->usersTable} cu ON cu.id = cb.customerId
            INNER JOIN {$this->usersTable} pu ON pu.id = a.providerId
            WHERE 1=1 $whereAppointment1";

        $appointmentQuery2 = "SELECT
                0 AS appointmentsCount1,
                COUNT(DISTINCT(p.id)) AS appointmentsCount2,
                0 AS eventsCount
            FROM {$this->table} p
            INNER JOIN {$this->packagesCustomersTable} pc ON p.packageCustomerId = pc.id
            INNER JOIN {$this->usersTable} cu ON cu.id = pc.customerId
            INNER JOIN {$this->packagesTable} pa ON pa.id = pc.packageId
            WHERE 1=1 $whereAppointment2";

        $eventQuery = "SELECT
                0 AS appointmentsCount1,
                0 AS appointmentsCount2,
                COUNT(DISTINCT(p.id)) AS eventsCount
            FROM {$this->table} p
            INNER JOIN {$this->bookingsTable} cb ON cb.id = p.customerBookingId
            INNER JOIN {$this->usersTable} cu ON cu.id = cb.customerId
            INNER JOIN {$this->customerBookingsToEventsPeriodsTable} cbe ON cbe.customerBookingId = cb.id
            INNER JOIN {$this->eventsPeriodsTable} ep ON ep.id = cbe.eventPeriodId
            LEFT JOIN {$this->eventsProvidersTable} epu ON epu.eventId = ep.eventId
            WHERE 1=1 $whereEvent";

        if (isset($criteria['events'], $criteria['services'])) {
            return [];
        } elseif (isset($criteria['services'])) {
            $paymentQuery = "({$appointmentQuery1}) UNION ALL ({$appointmentQuery2})";
            $params = array_merge($params, $appointmentParams1, $appointmentParams2);
        } elseif (isset($criteria['events'])) {
            $paymentQuery = "{$eventQuery}";
            $params = array_merge($params, $eventParams);
        } else {
            $paymentQuery = "({$appointmentQuery1}) UNION ALL ({$appointmentQuery2}) UNION ALL ({$eventQuery})";
            $params = array_merge($params, $appointmentParams1, $appointmentParams2, $eventParams);
        }

        try {
            $statement = $this->connection->prepare(
                "{$paymentQuery}"
            );

            $statement->execute($params);

            $statementResult1 = $statement->fetch();
            $statementResult2 = $statement->fetch();
            $statementResult3 = $statement->fetch();

            $appointmentsCount1 = !empty($statementResult1['appointmentsCount1']) ?
                $statementResult1['appointmentsCount1'] : 0;

            $appointmentsCount2 = !empty($statementResult2['appointmentsCount2']) ?
                $statementResult2['appointmentsCount2'] : 0;

            $eventsCount = !empty($statementResult3['eventsCount']) ?
                $statementResult3['eventsCount'] : 0;
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        return $appointmentsCount1 + $appointmentsCount2 + $eventsCount;
    }

    /**
     * Returns a collection of customers that have birthday on today's date and where notification is not sent
     *
     * @return Collection
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws \Exception
     */
    public function getUncompletedActionsForPayments()
    {
        $params = [];

        $currentDateTime = "STR_TO_DATE('" . DateTimeService::getNowDateTimeInUtc() . "', '%Y-%m-%d %H:%i:%s')";

        $pastDateTime =
            "STR_TO_DATE('" .
            DateTimeService::getNowDateTimeObjectInUtc()->modify('-7 day')->format('Y-m-d H:i:s') .
            "', '%Y-%m-%d %H:%i:%s')";

        try {
            $statement = $this->connection->prepare(
                "SELECT * FROM {$this->table} 
                WHERE
                      actionsCompleted = 0 AND
                      {$currentDateTime} > DATE_ADD(created, INTERVAL 300 SECOND) AND
                      {$pastDateTime} < created AND
                      entity IS NOT NULL"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        $items = [];

        foreach ($rows as $row) {
            $items[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return new Collection($items);
    }

    /**
     * @param int $status
     */
    public function findByStatus($status)
    {
        // TODO: Implement findByStatus() method.
    }
}
