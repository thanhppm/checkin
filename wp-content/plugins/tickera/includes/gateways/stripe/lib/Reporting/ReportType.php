<?php

namespace TCStripe\Reporting;

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
 * @package Stripe\Reporting
 */
class ReportType extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_type";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Retrieve;
}
