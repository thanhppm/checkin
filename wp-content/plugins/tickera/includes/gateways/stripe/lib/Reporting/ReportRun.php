<?php

namespace TCStripe\Reporting;

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
 * @package Stripe\Reporting
 */
class ReportRun extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "reporting.report_run";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Retrieve;
}
