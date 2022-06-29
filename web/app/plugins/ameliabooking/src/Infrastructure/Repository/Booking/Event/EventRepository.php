<?php

namespace AmeliaBooking\Infrastructure\Repository\Booking\Event;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Factory\Booking\Appointment\CustomerBookingFactory;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Domain\Factory\User\ProviderFactory;
use AmeliaBooking\Domain\Repository\Booking\Event\EventRepositoryInterface;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\String\Status;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\CustomerBookingsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\CustomerBookingsToEventsPeriodsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\CustomerBookingToEventsTicketsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsPeriodsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsProvidersTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsTagsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking\EventsTicketsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Coupon\CouponsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Coupon\CouponsToEventsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Gallery\GalleriesTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Payment\PaymentsTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\Provider\ProvidersGoogleCalendarTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\Provider\ProvidersOutlookCalendarTable;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\User\UsersTable;

/**
 * Class EventRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Booking\Event
 */
class EventRepository extends AbstractRepository implements EventRepositoryInterface
{

    const FACTORY = EventFactory::class;

    /**
     * @param Event $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':bookingOpens'         => $data['bookingOpens'] ? DateTimeService::getCustomDateTimeInUtc($data['bookingOpens']) : null,
            ':bookingCloses'        => $data['bookingCloses'] ? DateTimeService::getCustomDateTimeInUtc($data['bookingCloses']) : null,
            ':bookingOpensRec'      => $data['bookingOpensRec'],
            ':bookingClosesRec'     => $data['bookingClosesRec'],
            ':status'               => $data['status'],
            ':name'                 => $data['name'],
            ':description'          => $data['description'],
            ':color'                => $data['color'],
            ':price'                => $data['price'],
            ':recurringCycle'       => $data['recurring'] && $data['recurring']['cycle'] ?
                $data['recurring']['cycle'] : null,
            ':recurringOrder'       => $data['recurring'] && $data['recurring']['order'] ?
                $data['recurring']['order'] : null,
            ':recurringInterval'    => $data['recurring'] && $data['recurring']['cycleInterval'] ?
                $data['recurring']['cycleInterval'] : null,
            ':recurringMonthly'     => $data['recurring'] && $data['recurring']['monthlyRepeat'] ?
                $data['recurring']['monthlyRepeat'] : null,
            ':monthlyDate'          => $data['recurring'] && $data['recurring']['monthDate'] ?
                DateTimeService::getCustomDateTimeInUtc($data['recurring']['monthDate']) : null,
            ':monthlyOnRepeat'      => $data['recurring'] && $data['recurring']['monthlyOnRepeat'] ?
                $data['recurring']['monthlyOnRepeat'] : null,
            ':monthlyOnDay'         => $data['recurring'] && $data['recurring']['monthlyOnDay'] ?
                $data['recurring']['monthlyOnDay'] : null,
            ':recurringUntil'       => $data['recurring'] && $data['recurring']['until'] ?
                DateTimeService::getCustomDateTimeInUtc($data['recurring']['until']) : null,
            ':bringingAnyone'       => $data['bringingAnyone'] ? 1 : 0,
            ':bookMultipleTimes'    => $data['bookMultipleTimes'] ? 1 : 0,
            ':maxCapacity'          => $data['maxCapacity'],
            ':maxCustomCapacity'    => $data['maxCustomCapacity'],
            ':show'                 => $data['show'] ? 1 : 0,
            ':notifyParticipants'   => $data['notifyParticipants'],
            ':locationId'           => $data['locationId'],
            ':customLocation'       => $data['customLocation'],
            ':parentId'             => $data['parentId'],
            ':created'              => $data['created'],
            ':settings'             => $data['settings'],
            ':zoomUserId'           => $data['zoomUserId'],
            ':organizerId'          => $data['organizerId'],
            ':translations'         => $data['translations'],
            ':deposit'              => $data['deposit'],
            ':depositPayment'       => $data['depositPayment'],
            ':depositPerPerson'     => $data['depositPerPerson'] ? 1 : 0,
            ':fullPayment'          => $data['fullPayment'] ? 1 : 0,
            ':customPricing'        => $data['customPricing'] ? 1 : 0,
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table} 
                (
                `bookingOpens`,
                `bookingCloses`,
                `bookingOpensRec`,
                `bookingClosesRec`,
                `status`,
                `name`,
                `description`,
                `color`,
                `price`,
                `recurringCycle`,
                `recurringOrder`,
                `recurringInterval`,
                `recurringMonthly`,
                `monthlyDate`,
                `monthlyOnRepeat`,
                `monthlyOnDay`,
                `recurringUntil`,
                `bringingAnyone`,
                `bookMultipleTimes`,
                `maxCapacity`,
                `maxCustomCapacity`,
                `show`,
                `notifyParticipants`,
                `locationId`,
                `customLocation`,
                `parentId`,
                `created`,
                `settings`,
                `zoomUserId`,
                `organizerId`,
                `translations`,
                `deposit`,
                `depositPayment`,
                `depositPerPerson`,
                `fullPayment`,
                `customPricing`
                 )
                VALUES (
                :bookingOpens,
                :bookingCloses,
                :bookingOpensRec,
                :bookingClosesRec,
                :status,
                :name,
                :description,
                :color,
                :price,
                :recurringCycle,
                :recurringOrder,
                :recurringInterval,
                :recurringMonthly,
                :monthlyDate,
                :monthlyOnRepeat,
                :monthlyOnDay,           
                :recurringUntil,
                :bringingAnyone,
                :bookMultipleTimes,
                :maxCapacity,
                :maxCustomCapacity,
                :show,
                :notifyParticipants,
                :locationId,
                :customLocation,
                :parentId,
                :created,
                :settings,
                :zoomUserId,
                :organizerId,
                :translations,
                :deposit,
                :depositPayment,
                :depositPerPerson,
                :fullPayment,
                :customPricing
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
     * @param Event $entity
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity->toArray();

        $params = [
            ':id'                   => $id,
            ':bookingOpens'         => $data['bookingOpens'] ? DateTimeService::getCustomDateTimeInUtc($data['bookingOpens']) : null,
            ':bookingCloses'        => $data['bookingCloses'] ? DateTimeService::getCustomDateTimeInUtc($data['bookingCloses']) : null,
            ':bookingOpensRec'      => $data['bookingOpensRec'],
            ':bookingClosesRec'     => $data['bookingClosesRec'],
            ':status'               => $data['status'],
            ':name'                 => $data['name'],
            ':description'          => $data['description'],
            ':color'                => $data['color'],
            ':price'                => $data['price'],
            ':recurringCycle'       => $data['recurring'] ? $data['recurring']['cycle'] : null,
            ':recurringOrder'       => $data['recurring'] ? $data['recurring']['order'] : null,
            ':recurringInterval'    => $data['recurring'] ? $data['recurring']['cycleInterval'] : null,
            ':monthlyDate'          => $data['recurring'] && $data['recurring']['monthDate'] ?
                DateTimeService::getCustomDateTimeInUtc($data['recurring']['monthDate']) : null,
            ':recurringUntil'       => $data['recurring'] ?
                DateTimeService::getCustomDateTimeInUtc($data['recurring']['until']) : null,
            ':bringingAnyone'       => $data['bringingAnyone'] ? 1 : 0,
            ':bookMultipleTimes'    => $data['bookMultipleTimes'] ? 1 : 0,
            ':maxCapacity'          => $data['maxCapacity'],
            ':maxCustomCapacity'    => $data['maxCustomCapacity'],
            ':show'                 => $data['show'] ? 1 : 0,
            ':notifyParticipants'   => $data['notifyParticipants'],
            ':locationId'           => $data['locationId'],
            ':customLocation'       => $data['customLocation'],
            ':parentId'             => $data['parentId'],
            ':settings'             => $data['settings'],
            ':zoomUserId'           => $data['zoomUserId'],
            ':organizerId'          => $data['organizerId'],
            ':translations'         => $data['translations'],
            ':deposit'              => $data['deposit'],
            ':depositPayment'       => $data['depositPayment'],
            ':depositPerPerson'     => $data['depositPerPerson'] ? 1 : 0,
            ':fullPayment'          => $data['fullPayment'] ? 1 : 0,
            ':customPricing'        => $data['customPricing'] ? 1 : 0,
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `bookingOpens` = :bookingOpens,
                `bookingCloses` = :bookingCloses, 
                `bookingOpensRec` = :bookingOpensRec,
                `bookingClosesRec` = :bookingClosesRec, 
                `status` = :status,
                `name` = :name,
                `description` = :description,
                `color` = :color,
                `price` = :price,
                `recurringCycle` = :recurringCycle,
                `recurringOrder` = :recurringOrder,
                `recurringInterval` = :recurringInterval,
                `monthlyDate` = :monthlyDate,    
                `recurringUntil` = :recurringUntil,
                `bringingAnyone` = :bringingAnyone,
                `bookMultipleTimes` = :bookMultipleTimes,
                `maxCapacity` = :maxCapacity,
                `maxCustomCapacity` = :maxCustomCapacity,
                `show` = :show,
                `notifyParticipants` = :notifyParticipants,
                `locationId` = :locationId,
                `customLocation` = :customLocation,
                `parentId` = :parentId,
                `settings` = :settings,
                `zoomUserId` = :zoomUserId,
                `organizerId` = :organizerId,
                `translations` = :translations,
                `deposit` = :deposit,
                `depositPayment` = :depositPayment,
                `depositPerPerson` = :depositPerPerson,
                `fullPayment` = :fullPayment,
                `customPricing` = :customPricing
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
     * @param int $id
     * @param int $status
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function updateStatusById($id, $status)
    {
        $params = [
            ':id'     => $id,
            ':status' => $status
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `status` = :status
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
     * @param int $id
     * @param int $parentId
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function updateParentId($id, $parentId)
    {
        $params = [
            ':id'             => $id,
            ':parentId'       => $parentId,
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `parentId` = :parentId
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
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getFiltered($criteria)
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsTagsTable = EventsTagsTable::getTableName();
        $eventsTicketTable = EventsTicketsTable::getTableName();

        $galleriesTable = GalleriesTable::getTableName();
        $paymentsTable = PaymentsTable::getTableName();
        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();
        $customerBookingsTable = CustomerBookingsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $usersTable = UsersTable::getTableName();

        $params = [];
        $where = [];

        if (!empty($criteria['ids'])) {
            $queryIds = [];

            foreach ((array)$criteria['ids'] as $index => $value) {
                $param = ':id' . $index;
                $queryIds[] = $param;
                $params[$param] = $value;
            }

            $where[] = 'e.id IN (' . implode(', ', $queryIds) . ')';
        }

        if (isset($criteria['parentId'])) {
            $params[':parentId'] = $criteria['parentId'];
            $params[':originParentId'] = $criteria['parentId'];

            $where[] = 'e.parentId = :parentId OR e.id = :originParentId';
        }

        if (isset($criteria['search'])) {
            $params[':search'] = "%{$criteria['search']}%";

            $where[] = 'e.name LIKE :search';
        }

        if (isset($criteria['status'])) {
            $params[':status'] = $criteria['status'];

            $where[] = 'e.status = :status';
        }

        if (!empty($criteria['dates'])) {
            if (isset($criteria['dates'][0], $criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') BETWEEN :eventFrom AND :eventTo)";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } elseif (isset($criteria['dates'][0])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') >= :eventFrom OR (DATE_FORMAT(ep.periodEnd, '%Y-%m-%d %H:%i:%s') >= :eventTo))";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            } elseif (isset($criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') <= :eventTo)";
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } else {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') > :eventFrom)";
                $params[':eventFrom'] = DateTimeService::getNowDateTimeInUtc();
            }
        }

        if (!empty($criteria['locations'])) {
            $queryLocations = [];

            foreach ((array)$criteria['locations'] as $index => $value) {
                $param = ':location' . $index;
                $queryLocations[] = $param;
                $params[$param] = $value;
            }

            $where[] = 'e.locationId IN (' . implode(', ', $queryLocations) . ')';
        }

        $providerJoin = '';
        $providerFields = '';

        if (!empty($criteria['providers']) || !empty($criteria['allProviders'])) {
            $joinType = !empty($criteria['providers']) ? 'INNER' : 'LEFT';

            $providerJoin = "
            LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id 
            $joinType JOIN {$usersTable} pu ON pu.id = epr.userId OR pu.id = e.organizerId";

            $queryProviders = [];

            $providerFields = '
                pu.id AS provider_id,
                pu.firstName AS provider_firstName,
                pu.lastName AS provider_lastName,
                pu.email AS provider_email,
                pu.note AS provider_note,
                pu.phone AS provider_phone,
                pu.gender AS provider_gender,
                pu.pictureFullPath AS provider_pictureFullPath,
                pu.pictureThumbPath AS provider_pictureThumbPath,
                pu.translations AS provider_translations,
            ';

            if (!empty($criteria['providers'])) {
                foreach ((array)$criteria['providers'] as $index => $value) {
                    $param = ':provider' . $index;

                    $queryProviders[] = $param;

                    $params[$param] = $value;
                }

                $where1 = 'epr.userId IN (' . implode(', ', $queryProviders) . ')';


                $queryProviders = [];
                foreach ((array)$criteria['providers'] as $index => $value) {
                    $param = ':organizer' . $index;
                    $queryProviders[] = $param;
                    $params[$param] = $value;
                }

                $where2 = 'e.organizerId IN (' . implode(', ', $queryProviders) . ')';

                $where[] = '(' . $where1 . ' OR ' . $where2 . ')';
            }
        }

        if (isset($criteria['tag'])) {
            $params[':tag'] = $criteria['tag'];

            $tagJoin = "INNER JOIN {$eventsTagsTable} et ON et.eventId = e.id AND et.name = :tag";
        } else {
            $tagJoin = "LEFT JOIN {$eventsTagsTable} et ON et.eventId = e.id";
        }

        if (isset($criteria['bookingCouponId'])) {
            $where[] = "cb.couponId = {$criteria['bookingCouponId']}";
        }

        $paymentJoin = '';
        $paymentFields = '';

        if (!empty($criteria['fetchPayments'])) {
            $paymentFields = '
                p.id AS payment_id,
                p.amount AS payment_amount,
                p.dateTime AS payment_dateTime,
                p.status AS payment_status,
                p.gateway AS payment_gateway,
                p.gatewayTitle AS payment_gatewayTitle,
                p.data AS payment_data,
                p.wcOrderId AS payment_wcOrderId,
            ';

            $paymentJoin = "LEFT JOIN {$paymentsTable} p ON p.customerBookingId = cb.id";
        }

        $couponJoin = '';
        $couponFields = '';

        if (!empty($criteria['fetchCoupons'])) {
            $couponsTable = CouponsTable::getTableName();

            $couponFields = '
                c.id AS coupon_id,
                c.code AS coupon_code,
                c.discount AS coupon_discount,
                c.deduction AS coupon_deduction,
                c.limit AS coupon_limit,
                c.customerLimit AS coupon_customerLimit,
                c.status AS coupon_status,
            ';

            $couponJoin = "LEFT JOIN {$couponsTable} c ON c.id = cb.couponId";
        }

        if (!empty($criteria['customerId'])) {
            $params[':customerId'] = $criteria['customerId'];

            $where[] = 'cb.customerId = :customerId';
        }

        if (array_key_exists('bookingStatus', $criteria)) {
            $where[] = 'cb.status = :bookingStatus';
            $params[':bookingStatus'] = $criteria['bookingStatus'];
        }

        if (array_key_exists('show', $criteria)) {
            $where[] = 'e.show = :show';

            $params[':show'] = $criteria['show'];
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    e.id AS event_id,
                    e.name AS event_name,
                    e.status AS event_status,
                    e.bookingOpens AS event_bookingOpens,
                    e.bookingCloses AS event_bookingCloses,
                    e.bookingOpensRec AS event_bookingOpensRec,
                    e.bookingClosesRec AS event_bookingClosesRec,
                    e.recurringCycle AS event_recurringCycle,
                    e.recurringOrder AS event_recurringOrder,
                    e.recurringInterval AS event_recurringInterval,
                    e.recurringMonthly AS event_recurringMonthly, 
                    e.monthlyDate AS event_monthlyDate,
                    e.monthlyOnRepeat AS event_monthlyOnRepeat,
                    e.monthlyOnDay AS event_monthlyOnDay,
                    e.recurringUntil AS event_recurringUntil,
                    e.bringingAnyone AS event_bringingAnyone,
                    e.bookMultipleTimes AS event_bookMultipleTimes,
                    e.maxCapacity AS event_maxCapacity,
                    e.maxCustomCapacity AS event_maxCustomCapacity,
                    e.price AS event_price,
                    e.description AS event_description,
                    e.color AS event_color,
                    e.show AS event_show,
                    e.locationId AS event_locationId,
                    e.customLocation AS event_customLocation,
                    e.parentId AS event_parentId,
                    e.created AS event_created,
                    e.notifyParticipants AS event_notifyParticipants,
                    e.settings AS event_settings,
                    e.zoomUserId AS event_zoomUserId,
                    e.organizerId AS event_organizerId,
                    e.translations AS event_translations,
                    e.deposit AS event_deposit,
                    e.depositPayment AS event_depositPayment,
                    e.depositPerPerson AS event_depositPerPerson,
                    e.fullPayment AS event_fullPayment,
                    e.customPricing AS event_customPricing,
                    
                    ep.id AS event_periodId,
                    ep.periodStart AS event_periodStart,
                    ep.periodEnd AS event_periodEnd,
                    ep.zoomMeeting AS event_periodZoomMeeting,
                    ep.lessonSpace AS event_periodLessonSpace,
                    ep.googleCalendarEventId AS event_googleCalendarEventId,
                    ep.googleMeetUrl AS event_googleMeetUrl,
                    ep.outlookCalendarEventId AS event_outlookCalendarEventId,

                    et.id AS event_tagId,
                    et.name AS event_tagName,
                    
                    cb.id AS booking_id,
                    cb.customerId AS booking_customerId,
                    cb.status AS booking_status,
                    cb.price AS booking_price,
                    cb.persons AS booking_persons,
                    cb.customFields AS booking_customFields,
                    cb.info AS booking_info,
                    cb.aggregatedPrice AS booking_aggregatedPrice,
                    cb.couponId AS booking_couponId,
       
                    cu.id AS customer_id,
                    cu.firstName AS customer_firstName,
                    cu.lastName AS customer_lastName,
                    cu.email AS customer_email,
                    cu.note AS customer_note,
                    cu.phone AS customer_phone,
                    cu.gender AS customer_gender,
       
                    t.id AS ticket_id,
                    t.name AS ticket_name,
                    t.enabled AS ticket_enabled,
                    t.price AS ticket_price,
                    t.spots AS ticket_spots,
                    t.dateRanges AS ticket_dateRanges,
                    t.translations AS ticket_translations,
                    
                    {$couponFields}
                    
                    {$paymentFields}
                    
                    {$providerFields}
                    
                    g.id AS gallery_id,
                    g.pictureFullPath AS gallery_picture_full,
                    g.pictureThumbPath AS gallery_picture_thumb,
                    g.position AS gallery_position
                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                LEFT JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
                LEFT JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId
                LEFT JOIN {$usersTable} cu ON cu.id = cb.customerId
                LEFT JOIN {$galleriesTable} g ON g.entityId = e.id AND g.entityType = 'event'
                LEFT JOIN {$eventsTicketTable} t ON t.eventId = e.id
                {$providerJoin}
                {$couponJoin}
                {$paymentJoin}
                {$tagJoin}
                {$where}
                ORDER BY ep.periodStart"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getProvidersEvents($criteria)
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $usersTable = UsersTable::getTableName();

        $params = [];
        $where = [];

        if (!empty($criteria['dates'])) {
            if (isset($criteria['dates'][0], $criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') BETWEEN :eventFrom AND :eventTo)";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } elseif (isset($criteria['dates'][0])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') >= :eventFrom)";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            } elseif (isset($criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') <= :eventTo)";
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } else {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') > :eventFrom)";
                $params[':eventFrom'] = DateTimeService::getNowDateTimeInUtc();
            }
        }

        if (!empty($criteria['providers'])) {
            $queryProviders = [];

            foreach ((array)$criteria['providers'] as $index => $value) {
                $param = ':provider' . $index;
                $queryProviders[] = $param;
                $params[$param] = $value;
            }

            $where[] = 'epr.userId IN (' . implode(', ', $queryProviders) . ')';
        }

        if (!empty($criteria['status'])) {
            $params[':status'] = $criteria['status'];

            $where[] = 'e.status = :status';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    e.id AS event_id,
                    e.name AS event_name,
                    e.status AS event_status,
                    e.bookingOpens AS event_bookingOpens,
                    e.bookingCloses AS event_bookingCloses,
                    e.recurringCycle AS event_recurringCycle,
                    e.recurringOrder AS event_recurringOrder,
                    e.recurringInterval AS event_recurringInterval,
                    e.recurringUntil AS event_recurringUntil,
                    e.recurringMonthly AS event_recurringMonthly, 
                    e.monthlyDate AS event_monthlyDate,
                    e.monthlyOnRepeat AS event_monthlyOnRepeat,
                    e.monthlyOnDay AS event_monthlyOnDay,
                    e.bringingAnyone AS event_bringingAnyone,
                    e.bookMultipleTimes AS event_bookMultipleTimes,
                    e.maxCapacity AS event_maxCapacity,
                    e.maxCustomCapacity AS event_maxCustomCapacity,
                    e.price AS event_price,
                    e.description AS event_description,
                    e.color AS event_color,
                    e.show AS event_show,
                    e.locationId AS event_locationId,
                    e.customLocation AS event_customLocation,
                    e.parentId AS event_parentId,
                    e.created AS event_created,
                    e.notifyParticipants AS event_notifyParticipants,
                    e.translations AS event_translations,
                    e.deposit AS event_deposit,
                    e.depositPayment AS event_depositPayment,
                    e.depositPerPerson AS event_depositPerPerson,
                    e.fullPayment AS event_fullPayment,
                    e.customPricing AS event_customPricing,
                    
                    ep.id AS event_periodId,
                    ep.periodStart AS event_periodStart,
                    ep.periodEnd AS event_periodEnd,
                    
                    pu.id AS provider_id,
                    pu.firstName AS provider_firstName,
                    pu.lastName AS provider_lastName,
                    pu.email AS provider_email,
                    pu.note AS provider_note,
                    pu.phone AS provider_phone,
                    pu.gender AS provider_gender,
                    pu.pictureFullPath AS provider_pictureFullPath,
                    pu.pictureThumbPath AS provider_pictureThumbPath,
                    pu.translations AS provider_translations
                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                INNER JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                INNER JOIN {$usersTable} pu ON pu.id = epr.userId
                {$where}
                ORDER BY ep.periodStart"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param array $criteria
     * @param int   $itemsPerPage
     *
     * @return array
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getFilteredIds($criteria, $itemsPerPage)
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsTagsTable = EventsTagsTable::getTableName();
        $customerBookingsTable = CustomerBookingsTable::getTableName();
        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $usersTable = UsersTable::getTableName();

        $params = [];

        $where = [];

        if (!empty($criteria['search'])) {
            $where = ["e.name LIKE '%" . $criteria['search'] . "%'"];
        }

        if (isset($criteria['show'])) {
            $where = ['e.show = 1'];
        }

        if (!empty($criteria['dates'])) {
            if (isset($criteria['dates'][0], $criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') BETWEEN :eventFrom AND :eventTo)";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } elseif (isset($criteria['dates'][0])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') >= :eventFrom OR (DATE_FORMAT(ep.periodEnd, '%Y-%m-%d %H:%i:%s') >= :eventTo))";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            } elseif (isset($criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') <= :eventTo)";
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } else {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') > :eventFrom)";
                $params[':eventFrom'] = DateTimeService::getNowDateTimeInUtc();
            }
        }

        $tagJoin = '';

        if (isset($criteria['tag'])) {
            $params[':tag'] = $criteria['tag'];

            $tagJoin = "INNER JOIN {$eventsTagsTable} et ON et.eventId = e.id AND et.name = :tag";
        }

        if (!empty($criteria['id'])) {
            if ($criteria['recurring']) {
                $params[':id1'] = (int)$criteria['id'];
                $params[':id2'] = (int)$criteria['id'];
                $params[':id3'] = (int)$criteria['id'];
                $params[':id4'] = (int)$criteria['id'];

                $where[] = "((e.id = :id1 AND e.parentId IS NULL) OR 
                    (e.parentId IN (SELECT parentId FROM {$this->table} WHERE parentId = :id2)) OR
                    (e.id >= :id3  AND e.parentId IN (SELECT parentId FROM {$this->table} WHERE id = :id4)))";
            } else {
                $params[':id'] = (int)$criteria['id'];

                $where[] = 'e.id = :id';
            }
        }

        $customerJoin = '';

        if (!empty($criteria['customerId'])) {
            $customerJoin = "
            LEFT JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
            LEFT JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId";

            $params[':customerId'] = $criteria['customerId'];

            $where[] = 'cb.customerId = :customerId';
        }

        if (!empty($criteria['locationId'])) {
            $params[':locationId'] = $criteria['locationId'];

            $where[] = 'e.locationId = :locationId';
        }

        $providerJoin = '';

        if (!empty($criteria['providers'])) {
            $providerJoin = "
            LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
            INNER JOIN {$usersTable} pu ON pu.id = epr.userId OR pu.id = e.organizerId";
            $queryProviders = [];

            foreach ((array)$criteria['providers'] as $index => $value) {
                $param = ':provider' . $index;
                $queryProviders[] = $param;
                $params[$param] = $value;
            }

            $where1 = 'epr.userId IN (' . implode(', ', $queryProviders) . ')';

            $queryProviders = [];
            foreach ((array)$criteria['providers'] as $index => $value) {
                $param = ':organizer' . $index;
                $queryProviders[] = $param;
                $params[$param] = $value;
            }

            $where2 = 'e.organizerId IN (' . implode(', ', $queryProviders) . ')';

            $where[] = '(' . $where1 . ' OR ' . $where2 . ')';

        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $limit = $this->getLimit(
            !empty($criteria['page']) ? (int)$criteria['page'] : 0,
            (int)$itemsPerPage
        );

        try {
            $statement = $this->connection->prepare(
                "SELECT
                     e.id
                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                {$tagJoin}
                {$providerJoin}
                {$customerJoin}
                {$where}
                GROUP BY e.id
                ORDER BY ep.periodStart, e.id
                {$limit}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return $rows;
    }

    /**
     * @param array $criteria
     *
     * @return int
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getFilteredIdsCount($criteria)
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsTagsTable = EventsTagsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $usersTable = UsersTable::getTableName();

        $where = [];
        $params = [];

        if (isset($criteria['show'])) {
            $where = ['e.show = 1'];
        }

        if (!empty($criteria['dates'])) {
            if (isset($criteria['dates'][0], $criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') BETWEEN :eventFrom AND :eventTo)";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } elseif (isset($criteria['dates'][0])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') >= :eventFrom OR (DATE_FORMAT(ep.periodEnd, '%Y-%m-%d %H:%i:%s') >= :eventTo))";
                $params[':eventFrom'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][0]);
            } elseif (isset($criteria['dates'][1])) {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') <= :eventTo)";
                $params[':eventTo'] = DateTimeService::getCustomDateTimeInUtc($criteria['dates'][1]);
            } else {
                $where[] = "(DATE_FORMAT(ep.periodStart, '%Y-%m-%d %H:%i:%s') > :eventFrom)";
                $params[':eventFrom'] = DateTimeService::getNowDateTimeInUtc();
            }
        }

        if (!empty($criteria['locationId'])) {
            $params[':locationId'] = $criteria['locationId'];

            $where[] = 'e.locationId = :locationId';
        }

        $tagJoin = '';

        if (isset($criteria['tag'])) {
            $params[':tag'] = $criteria['tag'];

            $tagJoin = "INNER JOIN {$eventsTagsTable} et ON et.eventId = e.id AND et.name = :tag";
        }

        if (!empty($criteria['id'])) {
            if ($criteria['recurring']) {
                $params[':id1'] = (int)$criteria['id'];
                $params[':id2'] = (int)$criteria['id'];
                $params[':id3'] = (int)$criteria['id'];
                $params[':id4'] = (int)$criteria['id'];

                $where[] = "((e.id = :id1 AND e.parentId IS NULL) OR 
                    (e.parentId IN (SELECT parentId FROM {$this->table} WHERE parentId = :id2)) OR
                    (e.id >= :id3  AND e.parentId IN (SELECT parentId FROM {$this->table} WHERE id = :id4)))";
            } else {
                $params[':id'] = (int)$criteria['id'];

                $where[] = 'e.id = :id';
            }
        }

        $providerJoin = '';

        if (!empty($criteria['providers'])) {
            $providerJoin = "
            LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
            INNER JOIN {$usersTable} pu ON pu.id = epr.userId OR pu.id = e.organizerId";

            $queryProviders = [];

            foreach ((array)$criteria['providers'] as $index => $value) {
                $param = ':provider' . $index;
                $queryProviders[] = $param;
                $params[$param] = $value;
            }
            $where1 = 'epr.userId IN (' . implode(', ', $queryProviders) . ')';

            $queryProviders = [];
            foreach ((array)$criteria['providers'] as $index => $value) {
                $param = ':organizer' . $index;
                $queryProviders[] = $param;
                $params[$param] = $value;
            }
            $where2 = 'e.organizerId IN (' . implode(', ', $queryProviders) . ')';

            $where[] = '(' . $where1 . ' OR ' . $where2 . ')';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT e.id
                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                {$tagJoin}
                {$providerJoin}
                {$where}
                GROUP BY e.id
                ORDER BY ep.periodStart"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return sizeOf($rows);
    }

    /**
     * @param int $id
     *
     * @return Event
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getById($id)
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsTagsTable = EventsTagsTable::getTableName();
        $eventsTicketTable = EventsTicketsTable::getTableName();

        $customerBookingsTable = CustomerBookingsTable::getTableName();
        $paymentsTable = PaymentsTable::getTableName();
        $usersTable = UsersTable::getTableName();
        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();
        $galleriesTable = GalleriesTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $couponsTable = CouponsTable::getTableName();

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    e.id AS event_id,
                    e.name AS event_name,
                    e.status AS event_status,
                    e.bookingOpens AS event_bookingOpens,
                    e.bookingCloses AS event_bookingCloses, 
                    e.bookingOpensRec AS event_bookingOpensRec,
                    e.bookingClosesRec AS event_bookingClosesRec,
                    e.ticketRangeRec AS event_ticketRangeRec,
                    e.recurringCycle AS event_recurringCycle,
                    e.recurringOrder AS event_recurringOrder,
                    e.recurringInterval AS event_recurringInterval,
                    e.recurringMonthly AS event_recurringMonthly, 
                    e.monthlyDate AS event_monthlyDate,
                    e.monthlyOnRepeat AS event_monthlyOnRepeat,
                    e.monthlyOnDay AS event_monthlyOnDay,
                    e.recurringUntil AS event_recurringUntil,
                    e.bringingAnyone AS event_bringingAnyone,
                    e.bookMultipleTimes AS event_bookMultipleTimes,
                    e.maxCapacity AS event_maxCapacity,
                    e.maxCustomCapacity AS event_maxCustomCapacity,
                    e.price AS event_price,
                    e.description AS event_description,
                    e.color AS event_color,
                    e.show AS event_show,
                    e.notifyParticipants AS event_notifyParticipants,
                    e.locationId AS event_locationId,
                    e.customLocation AS event_customLocation,
                    e.parentId AS event_parentId,
                    e.created AS event_created,
                    e.settings AS event_settings,
                    e.zoomUserId AS event_zoomUserId,
                    e.organizerId AS event_organizerId,
                    e.translations AS event_translations,
                    e.deposit AS event_deposit,
                    e.depositPayment AS event_depositPayment,
                    e.depositPerPerson AS event_depositPerPerson,
                    e.fullPayment AS event_fullPayment,
                    e.customPricing AS event_customPricing,
                    
                    ep.id AS event_periodId,
                    ep.periodStart AS event_periodStart,
                    ep.periodEnd AS event_periodEnd,
                    ep.zoomMeeting AS event_periodZoomMeeting,
                    ep.lessonSpace AS event_periodLessonSpace,
                    ep.googleCalendarEventId AS event_googleCalendarEventId,
                    ep.googleMeetUrl AS event_googleMeetUrl,
                    ep.outlookCalendarEventId AS event_outlookCalendarEventId,
                    
                    et.id AS event_tagId,
                    et.name AS event_tagName,
                                        
                    cb.id AS booking_id,
                    cb.customerId AS booking_customerId,
                    cb.status AS booking_status,
                    cb.price AS booking_price,
                    cb.persons AS booking_persons,
                    cb.customFields AS booking_customFields,
                    cb.info AS booking_info,
                    cb.aggregatedPrice AS booking_aggregatedPrice,
                    cb.token AS booking_token,
                    cb.utcOffset AS booking_utcOffset,
                    
                    cu.id AS customer_id,
                    cu.firstName AS customer_firstName,
                    cu.lastName AS customer_lastName,
                    cu.email AS customer_email,
                    cu.note AS customer_note,
                    cu.phone AS customer_phone,
                    cu.gender AS customer_gender,
                    cu.birthday AS customer_birthday,
                    
                    p.id AS payment_id,
                    p.amount AS payment_amount,
                    p.dateTime AS payment_dateTime,
                    p.status AS payment_status,
                    p.gateway AS payment_gateway,
                    p.gatewayTitle AS payment_gatewayTitle,
                    p.data AS payment_data,
                    p.wcOrderId AS payment_wcOrderId,
                    
                    pu.id AS provider_id,
                    pu.firstName AS provider_firstName,
                    pu.lastName AS provider_lastName,
                    pu.email AS provider_email,
                    pu.note AS provider_note,
                    pu.phone AS provider_phone,
                    pu.gender AS provider_gender,
                    pu.translations AS provider_translations,
                    pu.timeZone AS provider_timeZone,
       
                    g.id AS gallery_id,
                    g.pictureFullPath AS gallery_picture_full,
                    g.pictureThumbPath AS gallery_picture_thumb,
                    g.position AS gallery_position,
                    
                    c.id AS coupon_id,
                    c.code AS coupon_code,
                    c.discount AS coupon_discount,
                    c.deduction AS coupon_deduction,
                    c.limit AS coupon_limit,
                    c.customerLimit AS coupon_customerLimit,
                    c.status AS coupon_status,
       
                    t.id AS ticket_id,
                    t.name AS ticket_name,
                    t.enabled AS ticket_enabled,
                    t.price AS ticket_price,
                    t.spots AS ticket_spots,
                    t.dateRanges AS ticket_dateRanges,
                    t.translations AS ticket_translations

                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                LEFT JOIN {$eventsTagsTable} et ON et.eventId = e.id
                LEFT JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
                LEFT JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId
                LEFT JOIN {$usersTable} cu ON cu.id = cb.customerId
                LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                LEFT JOIN {$usersTable} pu ON pu.id = epr.userId
                LEFT JOIN {$paymentsTable} p ON p.customerBookingId = cb.id
                LEFT JOIN {$galleriesTable} g ON g.entityId = e.id AND g.entityType = 'event'
                LEFT JOIN {$couponsTable} c ON c.id = cb.couponId
                LEFT JOIN {$eventsTicketTable} t ON t.eventId = e.id
                
                WHERE e.id = :eventId"
            );

            $statement->bindParam(':eventId', $id);

            $statement->execute();

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows)->getItem($id);
    }


    /**
     * @param int $id
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function isRecurring($id)
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT
                  e.recurringOrder AS event_recurringOrder, 
                  e.parentId AS event_parentId 
                FROM {$this->table} e 
                WHERE e.id = :eventId"
            );

            $statement->bindParam(':eventId', $id);

            $statement->execute();

            return $statement->fetch();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }
    }


    /**
     * @param int $id
     * @param int $parentId
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function getRecurringIds($id, $parentId)
    {
        $whereParent = empty($parentId) ? '' : ' OR e.parentId = :parentId';
        try {
            $statement = $this->connection->prepare(
                "SELECT
                  e.id AS eventId 
                FROM {$this->table} e 
                WHERE e.parentId = :eventId" . $whereParent
            );

            $statement->bindParam(':eventId', $id);
            if ($parentId) {
                $statement->bindParam(':parentId', $parentId);
            }

            $statement->execute();

            $events = $statement->fetchAll();

            return array_column($events, 'eventId');
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }
    }


    /**
     * @param array $ids
     *
     * @return Event
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getByBookingIds($ids)
    {
        $paymentsTable = PaymentsTable::getTableName();
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();
        $eventsTagsTable = EventsTagsTable::getTableName();
        $providersGoogleCalendarTable = ProvidersGoogleCalendarTable::getTableName();

        $usersTable = UsersTable::getTableName();
        $customerBookingsTable = CustomerBookingsTable::getTableName();
        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $couponsTable = CouponsTable::getTableName();
        $providersOutlookCalendarTable = ProvidersOutlookCalendarTable::getTableName();

        $params = [];
        $where = [];

        foreach ($ids as $key => $id) {
            $params[":customerBookingId$key"] = $id;
            $where[] = "cb.id = :customerBookingId$key";
        }

        $where = $where ? 'WHERE ' . implode(' OR ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    e.id AS event_id,
                    e.name AS event_name,
                    e.status AS event_status,
                    e.bookingOpens AS event_bookingOpens,
                    e.bookingCloses AS event_bookingCloses,
                    e.recurringCycle AS event_recurringCycle,
                    e.recurringOrder AS event_recurringOrder,
                    e.recurringInterval AS event_recurringInterval,
                    e.recurringUntil AS event_recurringUntil,
                    e.bringingAnyone AS event_bringingAnyone,
                    e.bookMultipleTimes AS event_bookMultipleTimes,
                    e.maxCapacity AS event_maxCapacity,
                    e.maxCustomCapacity AS event_maxCustomCapacity,
                    e.price AS event_price,
                    e.description AS event_description,
                    e.color AS event_color,
                    e.show AS event_show,
                    e.notifyParticipants AS event_notifyParticipants,
                    e.locationId AS event_locationId,
                    e.customLocation AS event_customLocation,
                    e.parentId AS event_parentId,
                    e.created AS event_created,
                    e.settings AS event_settings,
                    e.zoomUserId AS event_zoomUserId,
                    e.organizerId AS event_organizerId,
                    e.translations AS event_translations,
                    e.deposit AS event_deposit,
                    e.depositPayment AS event_depositPayment,
                    e.depositPerPerson AS event_depositPerPerson,
                    e.fullPayment AS event_fullPayment,
                    e.customPricing AS event_customPricing,
                    
                    ep.id AS event_periodId,
                    ep.periodStart AS event_periodStart,
                    ep.periodEnd AS event_periodEnd,
                    ep.zoomMeeting AS event_periodZoomMeeting,
                    ep.lessonSpace AS event_periodLessonSpace,
                    ep.googleCalendarEventId AS event_googleCalendarEventId,
                    ep.googleMeetUrl AS event_googleMeetUrl,
                    ep.outlookCalendarEventId AS event_outlookCalendarEventId,
       
                    et.id AS event_tagId,
                    et.name AS event_tagName,
                    
                    cb.id AS booking_id,
                    cb.appointmentId AS booking_appointmentId,
                    cb.customerId AS booking_customerId,
                    cb.status AS booking_status,
                    cb.price AS booking_price,
                    cb.persons AS booking_persons,
                    cb.persons AS booking_couponId,
                    cb.customFields AS booking_customFields,
                    cb.info AS booking_info,
                    cb.utcOffset AS booking_utcOffset,
                    cb.aggregatedPrice AS booking_aggregatedPrice,
                    
                    cu.id AS customer_id,
                    cu.firstName AS customer_firstName,
                    cu.lastName AS customer_lastName,
                    cu.email AS customer_email,
                    cu.note AS customer_note,
                    cu.phone AS customer_phone,
                    cu.gender AS customer_gender,
                    cu.birthday AS customer_birthday,
       
                    p.id AS payment_id,
                    p.amount AS payment_amount,
                    p.dateTime AS payment_dateTime,
                    p.status AS payment_status,
                    p.gateway AS payment_gateway,
                    p.gatewayTitle AS payment_gatewayTitle,
                    p.data AS payment_data,
                    p.wcOrderId AS payment_wcOrderId,
                    
                    pu.id AS provider_id,
                    pu.firstName AS provider_firstName,
                    pu.lastName AS provider_lastName,
                    pu.email AS provider_email,
                    pu.note AS provider_note,
                    pu.phone AS provider_phone,
                    pu.gender AS provider_gender,
                    pu.translations AS provider_translations,
                    
                    gd.id AS google_calendar_id,
                    gd.token AS google_calendar_token,
                    gd.calendarId AS google_calendar_calendar_id,
       
                    od.id AS outlook_calendar_id,
                    od.token AS outlook_calendar_token,
                    od.calendarId AS outlook_calendar_calendar_id,
       
                    c.id AS coupon_id,
                    c.code AS coupon_code,
                    c.discount AS coupon_discount,
                    c.deduction AS coupon_deduction,
                    c.limit AS coupon_limit,
                    c.customerLimit AS coupon_customerLimit,
                    c.status AS coupon_status

                FROM {$this->table} e
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
                INNER JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
                INNER JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId
                INNER JOIN {$usersTable} cu ON cu.id = cb.customerId
                LEFT JOIN {$couponsTable} c ON c.id = cb.couponId
                LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                LEFT JOIN {$usersTable} pu ON pu.id = epr.userId
                LEFT JOIN {$providersGoogleCalendarTable} gd ON gd.userId = pu.id
                LEFT JOIN {$providersOutlookCalendarTable} od ON od.userId = pu.id
                LEFT JOIN {$paymentsTable} p ON p.customerBookingId = cb.id
                LEFT JOIN {$eventsTagsTable} et ON et.eventId = e.id
                
                {$where}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param      $criteria
     *
     * @return Collection
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getWithCoupons($criteria)
    {
        $couponToEventsTable = CouponsToEventsTable::getTableName();
        $couponsTable = CouponsTable::getTableName();
        $eventsProvidersTable = EventsProvidersTable::getTableName();
        $usersTable = UsersTable::getTableName();
        $eventsTicketTable = EventsTicketsTable::getTableName();

        $params = [];

        $where = [];

        foreach ((array)$criteria as $index => $value) {
            $params[':event' . $index] = $value['eventId'];

            if ($value['couponId']) {
                $params[':coupon' . $index] = $value['couponId'];
                $params[':couponStatus' . $index] = Status::VISIBLE;
            }

            $where[] = "(e.id = :event$index"
                . ($value['couponId'] ? " AND c.id = :coupon$index AND c.status = :couponStatus$index" : '') . ')';
        }

        $where = $where ? 'WHERE ' . implode(' OR ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    e.id AS event_id,
                    e.name AS event_name,
                    e.status AS event_status,
                    e.bookingOpens AS event_bookingOpens,
                    e.bookingCloses AS event_bookingCloses,
                    e.recurringCycle AS event_recurringCycle,
                    e.recurringOrder AS event_recurringOrder,
                    e.recurringInterval AS event_recurringInterval,
                    e.recurringUntil AS event_recurringUntil,
                    e.bringingAnyone AS event_bringingAnyone,
                    e.bookMultipleTimes AS event_bookMultipleTimes,
                    e.maxCapacity AS event_maxCapacity,
                    e.maxCustomCapacity AS event_maxCustomCapacity,
                    e.price AS event_price,
                    e.description AS event_description,
                    e.color AS event_color,
                    e.show AS event_show,
                    e.notifyParticipants AS event_notifyParticipants,
                    e.locationId AS event_locationId,
                    e.customLocation AS event_customLocation,
                    e.parentId AS event_parentId,
                    e.created AS event_created,
                    e.translations AS event_translations,
                    e.deposit AS event_deposit,
                    e.depositPayment AS event_depositPayment,
                    e.depositPerPerson AS event_depositPerPerson,
                    e.fullPayment AS event_fullPayment,
                    e.customPricing AS event_customPricing,
                    
                    pu.id AS provider_id,
                    pu.firstName AS provider_firstName,
                    pu.lastName AS provider_lastName,
                    pu.email AS provider_email,
                    pu.note AS provider_note,
                    pu.phone AS provider_phone,
                    pu.gender AS provider_gender,
                    pu.translations AS provider_translations,
       
                    t.id AS ticket_id,
                    t.name AS ticket_name,
                    t.enabled AS ticket_enabled,
                    t.price AS ticket_price,
                    t.spots AS ticket_spots,
                    t.dateRanges AS ticket_dateRanges,
                    t.translations AS ticket_translations,

                    c.id AS coupon_id,
                    c.code AS coupon_code,
                    c.discount AS coupon_discount,
                    c.deduction AS coupon_deduction,
                    c.limit AS coupon_limit,
                    c.customerLimit AS coupon_customerLimit,
                    c.status AS coupon_status
                FROM {$this->table} e
                LEFT JOIN {$couponToEventsTable} ce ON ce.eventId = e.id
                LEFT JOIN {$couponsTable} c ON c.id = ce.couponId
                LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                LEFT JOIN {$usersTable} pu ON pu.id = epr.userId
                LEFT JOIN {$eventsTicketTable} t ON t.eventId = e.id
                {$where}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param int   $bookingId
     * @param array $criteria
     *
     * @return Event
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getByBookingId($bookingId, $criteria = [])
    {
        $eventsPeriodsTable = EventsPeriodsTable::getTableName();

        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();

        $fields = '';

        $joins = '';

        if (!empty($criteria['fetchEventsCoupons'])) {
            $couponsTable = CouponsTable::getTableName();

            $fields .= '
                ec.id AS coupon_id,
                ec.code AS coupon_code,
                ec.discount AS coupon_discount,
                ec.deduction AS coupon_deduction,
                ec.limit AS coupon_limit,
                ec.customerLimit AS coupon_customerLimit,
                ec.status AS coupon_status,
            ';

            $joins .= "
                LEFT JOIN {$couponsTable} ec ON ec.id = cb.couponId
            ";
        }

        if (!empty($criteria['fetchEventsTickets'])) {
            $ticketsTable = EventsTicketsTable::getTableName();

            $fields .= '
                eti.id AS ticket_id,
                eti.name AS ticket_name,
                eti.enabled AS ticket_enabled,
                eti.price AS ticket_price,
                eti.spots AS ticket_spots,
                eti.dateRanges AS ticket_dateRanges,
                eti.translations AS ticket_translations,
            ';

            $joins .= "
                LEFT JOIN {$ticketsTable} eti ON eti.eventId = e.id
            ";
        }

        if (!empty($criteria['fetchEventsTags'])) {
            $tagsTable = EventsTagsTable::getTableName();

            $fields .= '
                eta.id AS event_tagId,
                eta.name AS event_tagName,
            ';

            $joins .= "
                LEFT JOIN {$tagsTable} eta ON eta.eventId = e.id
            ";
        }

        if (!empty($criteria['fetchEventsImages'])) {
            $galleriesTable = GalleriesTable::getTableName();

            $fields .= '
                eg.id AS gallery_id,
                eg.pictureFullPath AS gallery_picture_full,
                eg.pictureThumbPath AS gallery_picture_thumb,
                eg.position AS gallery_position,
            ';

            $joins .= "
                LEFT JOIN {$galleriesTable} eg ON eg.entityId = e.id AND eg.entityType = 'event'
            ";
        }

        if (!empty($criteria['fetchEventsProviders'])) {
            $eventsProvidersTable = EventsProvidersTable::getTableName();

            $usersTable = UsersTable::getTableName();

            $joins .= "
                LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                LEFT JOIN {$usersTable} pu ON pu.id = epr.userId
            ";

            $fields .= '
                pu.id AS provider_id,
                pu.firstName AS provider_firstName,
                pu.lastName AS provider_lastName,
                pu.email AS provider_email,
                pu.note AS provider_note,
                pu.phone AS provider_phone,
                pu.gender AS provider_gender,
                pu.pictureFullPath AS provider_pictureFullPath,
                pu.pictureThumbPath AS provider_pictureThumbPath,
                pu.translations AS provider_translations,
                pu.timeZone AS provider_timeZone,
            ';
        }

        $fields .= "
            e.id AS event_id,
            e.name AS event_name,
            e.status AS event_status,
            e.bookingOpens AS event_bookingOpens,
            e.bookingCloses AS event_bookingCloses,
            e.recurringCycle AS event_recurringCycle,
            e.recurringOrder AS event_recurringOrder,
            e.recurringInterval AS event_recurringInterval,
            e.recurringUntil AS event_recurringUntil,
            e.bringingAnyone AS event_bringingAnyone,
            e.bookMultipleTimes AS event_bookMultipleTimes,
            e.maxCapacity AS event_maxCapacity,
            e.maxCustomCapacity AS event_maxCustomCapacity,
            e.price AS event_price,
            e.description AS event_description,
            e.color AS event_color,
            e.show AS event_show,
            e.notifyParticipants AS event_notifyParticipants,
            e.locationId AS event_locationId,
            e.customLocation AS event_customLocation,
            e.customPricing AS event_customPricing,
            e.parentId AS event_parentId,
            e.created AS event_created,
            e.settings AS event_settings,
            e.zoomUserId AS event_zoomUserId,
            e.translations AS event_translations,
            e.deposit AS event_deposit,
            e.depositPayment AS event_depositPayment,
            e.depositPerPerson AS event_depositPerPerson,
            e.fullPayment AS event_fullPayment,
            e.organizerId AS event_organizerId,
            
            ep.id AS event_periodId,
            ep.periodStart AS event_periodStart,
            ep.periodEnd AS event_periodEnd,
            ep.zoomMeeting AS event_periodZoomMeeting,
            ep.lessonSpace AS event_periodLessonSpace,
            ep.googleCalendarEventId AS event_googleCalendarEventId,
            ep.googleMeetUrl AS event_googleMeetUrl,
            ep.outlookCalendarEventId AS event_outlookCalendarEventId
        ";

        $params = [
            ':customerBookingId' => $bookingId,
        ];

        try {
            $statement = $this->connection->prepare(
                "SELECT
                {$fields}
                FROM {$customerBookingsEventsPeriods} cbe
                INNER JOIN {$eventsPeriodsTable} ep ON ep.id = cbe.eventPeriodId
                INNER JOIN {$this->table} e ON e.id = ep.eventId
                {$joins}
                WHERE cbe.customerBookingId = :customerBookingId"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by booking id in ' . __CLASS__, $e->getCode(), $e);
        }

        /** @var Collection $events */
        $events = call_user_func([static::FACTORY, 'createCollection'], $rows);

        return $events->length() ? $events->getItem($events->keys()[0]) : null;
    }

    /**
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getByCriteria($criteria = [])
    {
        $params = [];

        $where = [];

        $fields = '';

        $joins = '';

        $orderBy = '';

        if (!empty($criteria['fetchBookings']) || !empty($criteria['fetchEventsPeriods'])) {
            $eventsPeriodsTable = EventsPeriodsTable::getTableName();

            $fields .= '
                ep.id AS event_periodId,
                ep.periodStart AS event_periodStart,
                ep.periodEnd AS event_periodEnd,
                ep.zoomMeeting AS event_periodZoomMeeting,
                ep.lessonSpace AS event_periodLessonSpace,
                ep.googleCalendarEventId AS event_googleCalendarEventId,
                ep.googleMeetUrl AS event_googleMeetUrl,
                ep.outlookCalendarEventId AS event_outlookCalendarEventId,
            ';

            $joins .= "
                INNER JOIN {$eventsPeriodsTable} ep ON ep.eventId = e.id
            ";

            $orderBy = 'ORDER BY ep.periodStart';
        }

        if (!empty($criteria['fetchBookings'])) {
            $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();

            $customerBookingsTable = CustomerBookingsTable::getTableName();

            $fields .= '
                cb.id AS booking_id,
                cb.appointmentId AS booking_appointmentId,
                cb.customerId AS booking_customerId,
                cb.status AS booking_status,
                cb.price AS booking_price,
                cb.persons AS booking_persons,
                cb.couponId AS booking_couponId,
                cb.customFields AS booking_customFields,
                cb.info AS booking_info,
                cb.utcOffset AS booking_utcOffset,
                cb.aggregatedPrice AS booking_aggregatedPrice,
            ';

            if (!empty($criteria['fetchApprovedBookings'])) {
                $where[] = "cb.status = 'approved'";
            }

            if (!empty($criteria['customerBookingId'])) {
                $params[':customerBookingId'] = $criteria['customerBookingId'];

                $where[] = 'cb.id = :customerBookingId';
            }

            $joins .= "
                INNER JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
                INNER JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId
            ";

            if (!empty($criteria['fetchBookingsPayments'])) {
                $paymentsTable = PaymentsTable::getTableName();

                $fields .= '
                    p.id AS payment_id,
                    p.amount AS payment_amount,
                    p.dateTime AS payment_dateTime,
                    p.status AS payment_status,
                    p.gateway AS payment_gateway,
                    p.gatewayTitle AS payment_gatewayTitle,
                    p.data AS payment_data,
                    p.wcOrderId AS payment_wcOrderId,
                ';

                $joins .= "
                    LEFT JOIN {$paymentsTable} p ON p.customerBookingId = cb.id
                ";
            }

            if (!empty($criteria['fetchBookingsCoupons'])) {
                $couponsTable = CouponsTable::getTableName();

                $fields .= '
                    c.id AS coupon_id,
                    c.code AS coupon_code,
                    c.discount AS coupon_discount,
                    c.deduction AS coupon_deduction,
                    c.limit AS coupon_limit,
                    c.customerLimit AS coupon_customerLimit,
                    c.status AS coupon_status,
                ';

                $joins .= "
                    LEFT JOIN {$couponsTable} c ON c.id = cb.couponId
                ";
            }

            if (!empty($criteria['fetchBookingsUsers'])) {
                $usersTable = UsersTable::getTableName();

                $fields .= '
                    cu.id AS customer_id,
                    cu.type AS customer_type,
                    cu.firstName AS customer_firstName,
                    cu.lastName AS customer_lastName,
                    cu.email AS customer_email,
                    cu.note AS customer_note,
                    cu.phone AS customer_phone,
                    cu.gender AS customer_gender,
                    cu.birthday AS customer_birthday,
                ';

                $joins .= "
                    INNER JOIN {$usersTable} cu ON cu.id = cb.customerId
                ";
            }

            if (!empty($criteria['fetchBookingsTickets'])) {
                $bookingsTicketsTable = CustomerBookingToEventsTicketsTable::getTableName();

                $fields .= '
                    cbt.id AS booking_ticket_id,
                    cbt.eventTicketId AS booking_ticket_eventTicketId,
                    cbt.price AS booking_ticket_price,
                    cbt.persons AS booking_ticket_persons,
                ';

                $joins .= "
                    LEFT JOIN {$bookingsTicketsTable} cbt ON cbt.customerBookingId = cb.id
                ";
            }
        }

        if (!empty($criteria['fetchEventsCoupons'])) {
            $couponsTable = CouponsTable::getTableName();

            $fields .= '
                ec.id AS coupon_id,
                ec.code AS coupon_code,
                ec.discount AS coupon_discount,
                ec.deduction AS coupon_deduction,
                ec.limit AS coupon_limit,
                ec.customerLimit AS coupon_customerLimit,
                ec.status AS coupon_status,
            ';

            $joins .= "
                LEFT JOIN {$couponsTable} ec ON ec.id = cb.couponId
            ";
        }

        if (!empty($criteria['fetchEventsTickets'])) {
            $ticketsTable = EventsTicketsTable::getTableName();

            $fields .= '
                eti.id AS ticket_id,
                eti.name AS ticket_name,
                eti.enabled AS ticket_enabled,
                eti.price AS ticket_price,
                eti.spots AS ticket_spots,
                eti.dateRanges AS ticket_dateRanges,
                eti.translations AS ticket_translations,
            ';

            $joins .= "
                LEFT JOIN {$ticketsTable} eti ON eti.eventId = e.id
            ";
        }

        if (!empty($criteria['fetchEventsTags'])) {
            $tagsTable = EventsTagsTable::getTableName();

            $fields .= '
                eta.id AS event_tagId,
                eta.name AS event_tagName,
            ';

            $joins .= "
                LEFT JOIN {$tagsTable} eta ON eta.eventId = e.id
            ";
        }

        if (!empty($criteria['fetchEventsImages'])) {
            $galleriesTable = GalleriesTable::getTableName();

            $fields .= '
                eg.id AS gallery_id,
                eg.pictureFullPath AS gallery_picture_full,
                eg.pictureThumbPath AS gallery_picture_thumb,
                eg.position AS gallery_position,
            ';

            $joins .= "
                LEFT JOIN {$galleriesTable} eg ON eg.entityId = e.id AND eg.entityType = 'event'
            ";
        }

        if (!empty($criteria['fetchEventsProviders'])) {
            $eventsProvidersTable = EventsProvidersTable::getTableName();

            $usersTable = UsersTable::getTableName();

            $joins .= "
                LEFT JOIN {$eventsProvidersTable} epr ON epr.eventId = e.id
                LEFT JOIN {$usersTable} pu ON pu.id = epr.userId
            ";

            $fields .= '
                pu.id AS provider_id,
                pu.firstName AS provider_firstName,
                pu.lastName AS provider_lastName,
                pu.email AS provider_email,
                pu.note AS provider_note,
                pu.phone AS provider_phone,
                pu.gender AS provider_gender,
                pu.pictureFullPath AS provider_pictureFullPath,
                pu.pictureThumbPath AS provider_pictureThumbPath,
                pu.translations AS provider_translations,
                pu.timeZone AS provider_timeZone,
            ';
        }

        $fields .= "
            e.id AS event_id,
            e.name AS event_name,
            e.status AS event_status,
            e.bookingOpens AS event_bookingOpens,
            e.bookingCloses AS event_bookingCloses, 
            e.bookingOpensRec AS event_bookingOpensRec,
            e.bookingClosesRec AS event_bookingClosesRec,
            e.ticketRangeRec AS event_ticketRangeRec,
            e.recurringCycle AS event_recurringCycle,
            e.recurringOrder AS event_recurringOrder,
            e.recurringInterval AS event_recurringInterval,
            e.recurringMonthly AS event_recurringMonthly, 
            e.monthlyDate AS event_monthlyDate,
            e.monthlyOnRepeat AS event_monthlyOnRepeat,
            e.monthlyOnDay AS event_monthlyOnDay,
            e.recurringUntil AS event_recurringUntil,
            e.bringingAnyone AS event_bringingAnyone,
            e.bookMultipleTimes AS event_bookMultipleTimes,
            e.maxCapacity AS event_maxCapacity,
            e.maxCustomCapacity AS event_maxCustomCapacity,
            e.price AS event_price,
            e.description AS event_description,
            e.color AS event_color,
            e.show AS event_show,
            e.notifyParticipants AS event_notifyParticipants,
            e.locationId AS event_locationId,
            e.customLocation AS event_customLocation,
            e.parentId AS event_parentId,
            e.created AS event_created,
            e.settings AS event_settings,
            e.zoomUserId AS event_zoomUserId,
            e.organizerId AS event_organizerId,
            e.translations AS event_translations,
            e.deposit AS event_deposit,
            e.depositPayment AS event_depositPayment,
            e.depositPerPerson AS event_depositPerPerson,
            e.fullPayment AS event_fullPayment,
            e.customPricing AS event_customPricing
        ";

        if (!empty($criteria['ids'])) {
            $queryIds = [];

            foreach ($criteria['ids'] as $index => $value) {
                $param = ':id' . $index;

                $queryIds[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'e.id IN (' . implode(', ', $queryIds) . ')';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                {$fields}
                FROM {$this->table} e
                {$joins}
                {$where}
                {$orderBy}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getBookingsByCriteria($criteria = [])
    {
        $params = [];

        $where = [];

        $fields = '';

        $joins = '';

        $eventsPeriodsTable = EventsPeriodsTable::getTableName();

        $customerBookingsEventsPeriods = CustomerBookingsToEventsPeriodsTable::getTableName();

        $customerBookingsTable = CustomerBookingsTable::getTableName();

        if (!empty($criteria['fetchApprovedBookings'])) {
            $where[] = "cb.status = 'approved'";
        }

        if (!empty($criteria['customerBookingId'])) {
            $params[':customerBookingId'] = $criteria['customerBookingId'];

            $where[] = 'cb.id = :customerBookingId';
        }

        if (!empty($criteria['fetchBookingsPayments'])) {
            $paymentsTable = PaymentsTable::getTableName();

            $fields .= '
                p.id AS payment_id,
                p.amount AS payment_amount,
                p.dateTime AS payment_dateTime,
                p.status AS payment_status,
                p.gateway AS payment_gateway,
                p.gatewayTitle AS payment_gatewayTitle,
                p.data AS payment_data,
                p.wcOrderId AS payment_wcOrderId,
            ';

            $joins .= "
                LEFT JOIN {$paymentsTable} p ON p.customerBookingId = cb.id
            ";
        }

        if (!empty($criteria['fetchBookingsCoupons'])) {
            $couponsTable = CouponsTable::getTableName();

            $fields .= '
                c.id AS coupon_id,
                c.code AS coupon_code,
                c.discount AS coupon_discount,
                c.deduction AS coupon_deduction,
                c.limit AS coupon_limit,
                c.customerLimit AS coupon_customerLimit,
                c.status AS coupon_status,
            ';

            $joins .= "
                LEFT JOIN {$couponsTable} c ON c.id = cb.couponId
            ";
        }

        if (!empty($criteria['fetchBookingsUsers'])) {
            $usersTable = UsersTable::getTableName();

            $fields .= '
                cu.id AS customer_id,
                cu.type AS customer_type,
                cu.firstName AS customer_firstName,
                cu.lastName AS customer_lastName,
                cu.email AS customer_email,
                cu.note AS customer_note,
                cu.phone AS customer_phone,
                cu.gender AS customer_gender,
                cu.birthday AS customer_birthday,
            ';

            $joins .= "
                INNER JOIN {$usersTable} cu ON cu.id = cb.customerId
            ";
        }

        if (!empty($criteria['fetchBookingsTickets'])) {
            $bookingsTicketsTable = CustomerBookingToEventsTicketsTable::getTableName();

            $fields .= '
                cbt.id AS booking_ticket_id,
                cbt.eventTicketId AS booking_ticket_eventTicketId,
                cbt.price AS booking_ticket_price,
                cbt.persons AS booking_ticket_persons,
            ';

            $joins .= "
                LEFT JOIN {$bookingsTicketsTable} cbt ON cbt.customerBookingId = cb.id
            ";
        }

        $fields .= '
            ep.eventId AS eventId,
            cb.id AS booking_id,
            cb.appointmentId AS booking_appointmentId,
            cb.customerId AS booking_customerId,
            cb.status AS booking_status,
            cb.price AS booking_price,
            cb.persons AS booking_persons,
            cb.couponId AS booking_couponId,
            cb.customFields AS booking_customFields,
            cb.info AS booking_info,
            cb.utcOffset AS booking_utcOffset,
            cb.aggregatedPrice AS booking_aggregatedPrice
        ';

        if (!empty($criteria['ids'])) {
            $queryIds = [];

            foreach ($criteria['ids'] as $index => $value) {
                $param = ':id' . $index;

                $queryIds[] = $param;

                $params[$param] = $value;
            }

            $where[] = 'ep.eventId IN (' . implode(', ', $queryIds) . ')';
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                {$fields}
                FROM {$eventsPeriodsTable} ep
                INNER JOIN {$customerBookingsEventsPeriods} cbe ON cbe.eventPeriodId = ep.id
                INNER JOIN {$customerBookingsTable} cb ON cb.id = cbe.customerBookingId
                {$joins}
                {$where}
                ORDER BY cb.id"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find event by id in ' . __CLASS__, $e->getCode(), $e);
        }

        $reformattedData = [];

        foreach ($rows as $row) {
            if (empty($reformattedData[$row['eventId']])) {
                $reformattedData[$row['eventId']] = [];
            }

            $reformattedData[$row['eventId']][] = $row;
        }

        $result = new Collection();

        foreach ($reformattedData as $eventId => $bookingsData) {
            $reformattedBookingsData = CustomerBookingFactory::reformat($bookingsData);

            $eventBookings = new Collection();

            foreach ($reformattedBookingsData as $bookingId => $data) {
                $eventBookings->addItem(CustomerBookingFactory::create($data), $bookingId);
            }

            $result->addItem($eventBookings, $eventId);
        }

        return $result;
    }
}
