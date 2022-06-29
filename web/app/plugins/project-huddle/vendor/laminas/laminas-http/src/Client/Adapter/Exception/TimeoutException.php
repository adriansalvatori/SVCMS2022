<?php

namespace ProjectHuddle\Vendor\Laminas\Http\Client\Adapter\Exception;

class TimeoutException extends RuntimeException implements ExceptionInterface
{
    public const READ_TIMEOUT = 1000;
}
