<?php

namespace TCStripe\Radar;

/**
 * Class ValueList
 *
 * @property string $id
 * @property string $object
 * @property string $alias
 * @property int $created
 * @property string $created_by
 * @property string $item_type
 * @property Collection $list_items
 * @property bool $livemode
 * @property StripeObject $metadata
 * @property mixed $name
 * @property int $updated
 * @property string $updated_by
 *
 * @package Stripe\Radar
 */
class ValueList extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "radar.value_list";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Delete;
    use \TCStripe\ApiOperations\Retrieve;
    use \TCStripe\ApiOperations\Update;
}
