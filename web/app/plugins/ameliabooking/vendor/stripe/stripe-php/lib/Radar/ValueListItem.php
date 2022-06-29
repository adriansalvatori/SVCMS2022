<?php

namespace AmeliaStripe\Radar;

/**
 * Class ValueListItem
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $created_by
 * @property string $list
 * @property bool $livemode
 * @property string $value
 *
 * @package AmeliaStripe\Radar
 */
class ValueListItem extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "radar.value_list_item";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Delete;
    use \AmeliaStripe\ApiOperations\Retrieve;
}
