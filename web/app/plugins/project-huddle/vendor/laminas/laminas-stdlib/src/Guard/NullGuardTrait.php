<?php

declare(strict_types=1);

namespace ProjectHuddle\Vendor\Laminas\Stdlib\Guard;

use Exception;
use ProjectHuddle\Vendor\Laminas\Stdlib\Exception\InvalidArgumentException;

use function sprintf;

/**
 * Provide a guard method against null data
 */
trait NullGuardTrait
{
    /**
     * Verify that the data is not null
     *
     * @param mixed  $data           the data to verify
     * @param string $dataName       the data name
     * @param string $exceptionClass FQCN for the exception
     * @return void
     * @throws Exception
     */
    protected function guardAgainstNull(
        $data,
        $dataName = 'Argument',
        $exceptionClass = InvalidArgumentException::class
    ) {
        if (null === $data) {
            $message = sprintf('%s cannot be null', $dataName);
            throw new $exceptionClass($message);
        }
    }
}
