<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Booking;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;

/**
 * Class AbstractCustomerBooking
 *
 * @package AmeliaBooking\Domain\Entity\Booking
 */
abstract class AbstractCustomerBooking
{
    /** @var Id */
    private $id;

    /** @var Id */
    protected $customerId;

    /** @var Customer */
    protected $customer;

    /** @var BookingStatus */
    protected $status;

    /** @var  Collection */
    protected $extras;

    /** @var Id */
    protected $couponId;

    /** @var Price */
    protected $price;

    /** @var Coupon */
    protected $coupon;

    /** @var Json */
    protected $customFields;

    /** @var Json */
    protected $info;

    /**
     * AbstractCustomerBooking constructor.
     *
     * @param Id            $customerId
     * @param BookingStatus $status
     */
    public function __construct(
        Id $customerId,
        BookingStatus $status
    ) {
        $this->customerId = $customerId;
        $this->status = $status;
    }

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
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
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
     * @return Collection
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param Collection $extras
     */
    public function setExtras(Collection $extras)
    {
        $this->extras = $extras;
    }

    /**
     * @return Id
     */
    public function getCouponId()
    {
        return $this->couponId;
    }

    /**
     * @param Id $couponId
     */
    public function setCouponId(Id $couponId)
    {
        $this->couponId = $couponId;
    }

    /**
     * @return Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param Coupon $coupon
     */
    public function setCoupon(Coupon $coupon)
    {
        $this->coupon = $coupon;
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
     * @return Json
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param Json $customFields
     */
    public function setCustomFields($customFields)
    {
        $this->customFields = $customFields;
    }

    /**
     * @return Json
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param Json $info
     */
    public function setInfo(Json $info)
    {
        $this->info = $info;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'           => null !== $this->getId() ? $this->getId()->getValue() : null,
            'customerId'   => $this->getCustomerId()->getValue(),
            'customer'     => null !== $this->getCustomer() ? $this->getCustomer()->toArray() : null,
            'status'       => $this->getStatus()->getValue(),
            'extras'       => null !== $this->getExtras() ? $this->getExtras()->toArray() : null,
            'couponId'     => null !== $this->getCouponId() ? $this->getCouponId()->getValue() : null,
            'price'        => null !== $this->getPrice() ? $this->getPrice()->getValue() : null,
            'coupon'       => null !== $this->getCoupon() ? $this->getCoupon()->toArray() : null,
            'customFields' => null !== $this->getCustomFields() ? $this->getCustomFields()->getValue() : null,
            'info'         => null !== $this->getInfo() ? $this->getInfo()->getValue() : null,
        ];
    }
}
