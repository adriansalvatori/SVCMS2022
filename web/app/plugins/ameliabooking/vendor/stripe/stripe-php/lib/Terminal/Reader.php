<?php

namespace AmeliaStripe\Terminal;

/**
 * Class Reader
 *
 * @property string $id
 * @property string $object
 * @property bool $deleted
 * @property string $device_sw_version
 * @property string $device_type
 * @property string $ip_address
 * @property string $label
 * @property string $location
 * @property string $serial_number
 * @property string $status
 *
 * @package AmeliaStripe\Terminal
 */
class Reader extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "terminal.reader";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Delete;
    use \AmeliaStripe\ApiOperations\Retrieve;
    use \AmeliaStripe\ApiOperations\Update;
}
