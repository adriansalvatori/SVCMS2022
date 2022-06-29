<?php

namespace AmeliaBooking\Domain\ValueObjects\Number\Integer;

/**
 * Class LoginType
 *
 * @package AmeliaBooking\Domain\ValueObjects\Number\Integer
 */
final class LoginType
{
    const WP_CREDENTIALS     = 1;
    const WP_USER            = 2;
    const AMELIA_CREDENTIALS = 3;
    const AMELIA_URL_TOKEN   = 4;

    /**
     * @var int
     */
    private $type;

    /**
     * Status constructor.
     *
     * @param int $type
     */
    public function __construct($type)
    {
        $this->type = (int)$type;
    }

    /**
     * Return the type from the value object
     *
     * @return int
     */
    public function getValue()
    {
        return $this->type;
    }
}
