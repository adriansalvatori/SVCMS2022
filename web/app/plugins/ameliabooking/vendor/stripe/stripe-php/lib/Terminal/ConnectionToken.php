<?php

namespace AmeliaStripe\Terminal;

/**
 * Class ConnectionToken
 *
 * @property string $secret
 *
 * @package AmeliaStripe\Terminal
 */
class ConnectionToken extends \AmeliaStripe\ApiResource
{
    const OBJECT_NAME = "terminal.connection_token";

    use \AmeliaStripe\ApiOperations\Create;
}
