<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Bookable\Service;

use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;

/**
 * Class PackageCustomer
 *
 * @package AmeliaBooking\Domain\Entity\Bookable\Service
 */
class PackageCustomer
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $packageId;

    /** @var Id */
    private $customerId;

    /** @var Price */
    private $price;

    /** @var DateTimeValue */
    private $end;

    /** @var DateTimeValue */
    private $start;

    /** @var DateTimeValue */
    private $purchased;

    /** @var Payment */
    private $payment;

    /** @var BookingStatus */
    protected $status;

    /**
     * @return Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Id $id
     */
    public function setId(Id $id)
    {
        $this->id = $id;
    }

    /**
     * @return Id
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * @param Id $packageId
     */
    public function setPackageId(Id $packageId)
    {
        $this->packageId = $packageId;
    }

    /**
     * @return Id
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param Id $customerId
     */
    public function setCustomerId(Id $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Price $price
     */
    public function setPrice(Price $price)
    {
        $this->price = $price;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return DateTimeValue
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param DateTimeValue $end
     */
    public function setEnd(DateTimeValue $end)
    {
        $this->end = $end;
    }

    /**
     * @return DateTimeValue
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param DateTimeValue $start
     */
    public function setStart(DateTimeValue $start)
    {
        $this->start = $start;
    }

    /**
     * @return DateTimeValue
     */
    public function getPurchased()
    {
        return $this->purchased;
    }

    /**
     * @param DateTimeValue $purchased
     */
    public function setPurchased(DateTimeValue $purchased)
    {
        $this->purchased = $purchased;
    }

    /**
     * @return BookingStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param BookingStatus $status
     */
    public function setStatus(BookingStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $dateTimeFormat = 'Y-m-d H:i:s';

        return [
            'id'         => $this->getId() ? $this->getId()->getValue() : null,
            'packageId'  => $this->getPackageId() ? $this->getPackageId()->getValue() : null,
            'customerId' => $this->getCustomerId() ? $this->getCustomerId()->getValue() : null,
            'price'      => $this->getPrice() ? $this->getPrice()->getValue() : null,
            'payment'    => $this->getPayment() ? $this->getPayment()->toArray() : null,
            'start'      => $this->getStart() ? $this->getStart()->getValue()->format($dateTimeFormat) : null,
            'end'        => $this->getEnd() ? $this->getEnd()->getValue()->format($dateTimeFormat) : null,
            'purchased'  => $this->getPurchased() ? $this->getPurchased()->getValue()->format($dateTimeFormat) : null,
            'status'     => $this->getStatus() ? $this->getStatus()->getValue() : null,
        ];
    }
}
