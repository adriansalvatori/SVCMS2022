<?php

namespace AmeliaStripe\Issuing;

/**
 * Class Transaction
 *
 * @property string $id
 * @property string $object
 * @property int $amount
 * @property string $authorization
 * @property string $balance_transaction
 * @property string $card
 * @property string $cardholder
 * @property int $created
 * @property string $currency
 * @property string $dispute
 * @property bool $livemode
 * @property mixed $merchant_data
 * @property int $merchant_amount
 * @property string $merchant_currency
 * @property \AmeliaStripe\StripeObject $metadata
 * @property string $type
 *
 * @package AmeliaStripe\Issuing
 */
class Transaction extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "issuing.transaction";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Retrieve;
    use \AmeliaStripe\ApiOperations\Update;
}
