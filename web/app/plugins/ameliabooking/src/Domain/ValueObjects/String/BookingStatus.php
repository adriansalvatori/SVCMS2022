<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

/**
 * Class Status
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class BookingStatus
{
    const CANCELED = 'canceled';
    const APPROVED = 'approved';
    const PENDING = 'pending';
    const REJECTED = 'rejected';

    /**
     * @var string
     */
    private $status;

    /**
     * Status constructor.
     *
     * @param int $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Return the status from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->status;
    }
}
