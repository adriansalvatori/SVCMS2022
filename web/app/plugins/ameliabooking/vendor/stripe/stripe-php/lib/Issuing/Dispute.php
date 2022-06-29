<?php

namespace AmeliaStripe\Issuing;

/**
 * Class Dispute
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property int $created
 * @property string $currency
 * @property mixed $evidence
 * @property bool $livemode
 * @property \AmeliaStripe\StripeObject $metadata
 * @property string $reason
 * @property string $status
 * @property Transaction $transaction
 *
 * @package AmeliaStripe\Issuing
 */
class Dispute extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "issuing.dispute";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Retrieve;
    use \AmeliaStripe\ApiOperations\Update;
}
