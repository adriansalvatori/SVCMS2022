<?php

namespace AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class CustomerBookingsToEventsPeriodsTable
 *
 * @package AmeliaBooking\Infrastructure\WP\InstallActions\DB\Booking
 */
class CustomerBookingsToEventsPeriodsTable extends AbstractDatabaseTable
{

    const TABLE = 'customer_bookings_to_events_periods';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable()
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `customerBookingId` INT(11) NOT NULL,
                    `eventPeriodId` INT(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `bookingEventPeriod` (`customerBookingId` ,`eventPeriodId`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
