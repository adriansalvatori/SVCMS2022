<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

/**
 * Class Cycle
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class Cycle
{
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const YEARLY = 'yearly';
    /**
     * @var string
     */
    private $cycle;

    /**
     * Cycle constructor.
     *
     * @param string $cycle
     */
    public function __construct($cycle)
    {
        $this->cycle = $cycle;
    }

    /**
     * Return the cycle from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->cycle;
    }
}
