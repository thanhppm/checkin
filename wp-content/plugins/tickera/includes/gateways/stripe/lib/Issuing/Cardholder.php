<?php

namespace TCStripe\Issuing;

/**
 * Class Cardholder
 *
 * @property string $id
 * @property string $object
 * @property mixed $billing
 * @property int $created
 * @property string $email
 * @property bool $livemode
 * @property \TCStripe\StripeObject $metadata
 * @property string $name
 * @property string $phone_number
 * @property string $status
 * @property string $type
 *
 * @package Stripe\Issuing
 */
class Cardholder extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "issuing.cardholder";

    use \TCStripe\ApiOperations\All;
    use \TCStripe\ApiOperations\Create;
    use \TCStripe\ApiOperations\Retrieve;
    use \TCStripe\ApiOperations\Update;
}
