<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;

/**
 * Class Label
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class Label
{
    const MAX_LENGTH = 65535;
    /**
     * @var string
     */
    private $label;

    /**
     * Name constructor.
     *
     * @param string $label
     *
     * @throws InvalidArgumentException
     */
    public function __construct($label)
    {
        if (strlen($label) > static::MAX_LENGTH) {
            throw new InvalidArgumentException("Label '$label' must be less than " . static::MAX_LENGTH . ' chars');
        }

        $this->label = $label;
    }

    /**
     * Return the name from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->label;
    }
}
