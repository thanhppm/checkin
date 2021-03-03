<?php

namespace TCStripe\Radar;

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
 * @package Stripe\Radar
 */
class ValueListItem extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "radar.value_list_item";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Delete;
    use \TCStripe\ApiOperations\Retrieve;
}
