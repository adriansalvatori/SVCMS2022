<?php

namespace ProjectHuddle\Vendor\Laminas\XmlRpc\Client\Exception;

use ProjectHuddle\Vendor\Laminas\XmlRpc\Exception;

/**
 * Thrown by Laminas\XmlRpc\Client when an XML-RPC fault response is returned.
 */
class FaultException extends Exception\BadMethodCallException implements ExceptionInterface
{
}
