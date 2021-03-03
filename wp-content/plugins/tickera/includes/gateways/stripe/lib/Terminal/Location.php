<?php

namespace TCStripe\Terminal;

/**
 * Class Location
 *
 * @property string $id
 * @property string $object
 * @property mixed $address
 * @property bool $deleted
 * @property string $display_name
 *
 * @package Stripe\Terminal
 */
class Location extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "terminal.location";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Delete;
    use \TCStripe\ApiOperations\Retrieve;
    use \TCStripe\ApiOperations\Update;
}
