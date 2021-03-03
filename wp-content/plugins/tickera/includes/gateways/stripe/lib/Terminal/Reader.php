<?php

namespace TCStripe\Terminal;

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
 * @package Stripe\Terminal
 */
class Reader extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "terminal.reader";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Delete;
    use \TCStripe\ApiOperations\Retrieve;
    use \TCStripe\ApiOperations\Update;
}
