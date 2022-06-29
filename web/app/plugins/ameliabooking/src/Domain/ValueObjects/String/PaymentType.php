<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

/**
 * Class PaymentType
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class PaymentType
{
    const PAY_PAL = 'payPal';

    const STRIPE = 'stripe';

    const ON_SITE = 'onSite';

    const WC = 'wc';

    const MOLLIE = 'mollie';

    const RAZORPAY = 'razorpay';

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
