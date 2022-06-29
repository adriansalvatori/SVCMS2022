<?php

namespace AmeliaBooking\Infrastructure\WP\InstallActions\DB\User;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\ValueObjects\Picture;
use AmeliaBooking\Domain\ValueObjects\String\Email;
use AmeliaBooking\Domain\ValueObjects\String\Name;
use AmeliaBooking\Domain\ValueObjects\String\Password;
use AmeliaBooking\Domain\ValueObjects\String\Phone;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class UsersTable
 *
 * @package AmeliaBooking\Infrastructure\WP\InstallActions\DB\User
 */
class UsersTable extends AbstractDatabaseTable
{

    const TABLE = 'users';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable()
    {
        $table = self::getTableName();

        $name = Name::MAX_LENGTH;
        $email = Email::MAX_LENGTH;
        $phone = Phone::MAX_LENGTH;
        $picture = Picture::MAX_LENGTH;
        $password = Password::MAX_LENGTH;

        return "CREATE TABLE {$table}  (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `status` ENUM('hidden', 'visible', 'disabled') NOT NULL default 'visible',
                  `type` ENUM('customer', 'provider', 'manager', 'admin') NOT NULL,
                  `externalId` int(11) DEFAULT NULL,
                  `firstName` varchar({$name}) NOT NULL DEFAULT '',
                  `lastName` varchar({$name}) NOT NULL DEFAULT '',
                  `email` varchar({$email}) DEFAULT NULL,
                  `birthday` date DEFAULT NULL,
                  `phone` varchar({$phone}) DEFAULT NULL,
                  `gender` ENUM('male', 'female') DEFAULT NULL,
                  `note` text,
                  `pictureFullPath` varchar ({$picture}) NULL,
                  `pictureThumbPath` varchar ({$picture}) NULL,
                  `password` varchar ({$password}) NULL,
                  `usedTokens` text NULL,
                  `zoomUserId` varchar({$name}) DEFAULT NULL,
                  `countryPhoneIso` varchar(2) DEFAULT NULL,
                  `translations` TEXT NULL DEFAULT NULL,
                  `timeZone` varchar({$name}) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `email` (`email`),
                  UNIQUE KEY `id` (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;";
    }
}
