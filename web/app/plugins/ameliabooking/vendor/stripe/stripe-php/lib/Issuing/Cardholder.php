<?php

namespace AmeliaStripe\Issuing;

/**
 * Class Cardholder
 *
 * @property string $id
 * @property string $object
 * @property mixed $billing
 * @property int $created
 * @property string $email
 * @property bool $livemode
 * @property \AmeliaStripe\StripeObject $metadata
 * @property string $name
 * @property string $phone_number
 * @property string $status
 * @property string $type
 *
 * @package AmeliaStripe\Issuing
 */
class Cardholder extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "issuing.cardholder";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Retrieve;
    use \AmeliaStripe\ApiOperations\Update;
}
