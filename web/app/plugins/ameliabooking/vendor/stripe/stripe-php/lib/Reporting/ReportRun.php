<?php

namespace AmeliaStripe\Reporting;

/**
 * Class ReportRun
 *
 * @property string $id
 * @property string $object
 * @property int $created
 * @property string $error
 * @property bool $livemode
 * @property mixed $parameters
 * @property string $report_type
 * @property mixed $result
 * @property string $status
 * @property int $succeeded_at
 *
 * @package AmeliaStripe\Reporting
 */
class ReportRun extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_run";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Create;
    use \AmeliaStripe\ApiOperations\Retrieve;
}
