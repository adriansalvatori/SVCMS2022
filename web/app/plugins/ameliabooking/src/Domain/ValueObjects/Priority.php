<?php

namespace AmeliaBooking\Domain\ValueObjects;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;

/**
 * Class Priority
 *
 * @package AmeliaBooking\Domain\ValueObjects
 */
final class Priority
{
    const LEAST_EXPENSIVE = 'least_expensive';
    const MOST_EXPENSIVE = 'most_expensive';
    const LEAST_OCCUPIED = 'least_occupied';
    const MOST_OCCUPIED = 'most_occupied';

    /**
     * @var int
     */
    private $value;

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     */
    public function __construct($value)
    {
        if (!in_array($value, [
            self::LEAST_EXPENSIVE,
            self::MOST_EXPENSIVE,
            self::LEAST_OCCUPIED,
            self::MOST_OCCUPIED
        ], null)) {
            throw new InvalidArgumentException('Not valid priority option');
        }
        $this->value = $value;
    }

    /**
     * Return the value from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
