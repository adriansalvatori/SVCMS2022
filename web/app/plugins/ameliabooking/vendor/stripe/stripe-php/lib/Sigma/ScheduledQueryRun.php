<?php

namespace AmeliaStripe\Sigma;

/**
 * Class Authorization
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property int $data_load_time
 * @property string $error
 * @property \AmeliaStripe\FileUpload $file
 * @property bool $livemode
 * @property int $result_available_until
 * @property string $sql
 * @property string $status
 * @property string $title
 *
 * @package AmeliaStripe\Sigma
 */
class ScheduledQueryRun extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "scheduled_query_run";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/sigma/scheduled_query_runs";
    }
}
