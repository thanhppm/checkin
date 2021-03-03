<?php

namespace TCStripe\Terminal;

/**
 * Class ConnectionToken
 *
 * @property string $secret
 *
 * @package Stripe\Terminal
 */
class ConnectionToken extends \TCStripe\ApiResource
{
    const OBJECT_NAME = "terminal.connection_token";

    use \TCStripe\ApiOperations\Create;
}
