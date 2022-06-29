<?php

namespace AmeliaBooking\Infrastructure\WP\InstallActions\DB\CustomField;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class CustomFieldsTable
 *
 * @package AmeliaBooking\Infrastructure\WP\InstallActions\DB\CustomField
 */
class CustomFieldsTable extends AbstractDatabaseTable
{

    const TABLE = 'custom_fields';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable()
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `label` TEXT NOT NULL DEFAULT '',
                   `type` ENUM('text', 'text-area', 'select', 'checkbox', 'radio', 'content', 'file', 'datepicker') NOT NULL DEFAULT 'text',
                   `required` TINYINT(1) NOT NULL DEFAULT 0,
                   `position` int(11) NOT NULL,
                   `translations` TEXT NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
