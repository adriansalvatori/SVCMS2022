<?php

namespace AmeliaStripe\Reporting;

/**
 * Class ReportType
 *
 * @property string $id
 * @property string $object
 * @property int $data_available_end
 * @property int $data_available_start
 * @property string $name
 * @property int $updated
 * @property string $version
 *
 * @package AmeliaStripe\Reporting
 */
class ReportType extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_type";

    use \AmeliaStripe\ApiOperations\All;
    use \AmeliaStripe\ApiOperations\Retrieve;
}
