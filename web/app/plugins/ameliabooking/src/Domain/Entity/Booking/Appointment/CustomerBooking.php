<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Booking\Appointment;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Booking\AbstractCustomerBooking;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\IntegerValue;
use AmeliaBooking\Domain\ValueObjects\String\Token;

/**
 * Class CustomerBooking
 *
 * @package AmeliaBooking\Domain\Entity\Booking\Appointment
 */
class CustomerBooking extends AbstractCustomerBooking
{
    /** @var Id */
    private $appointmentId;

    /** @var IntegerValue */
    private $persons;

    /** @var Collection */
    private $payments;

    /** @var Collection */
    private $ticketsBooking;

    /** @var Token */
    private $token;

    /** @var IntegerValue */
    private $utcOffset;

    /** @var  BooleanValueObject */
    protected $aggregatedPrice;

    /** @var  BooleanValueObject */
    protected $isChangedStatus;

    /** @var PackageCustomerService */
    protected $packageCustomerService;

    /** @var  BooleanValueObject */
    protected $deposit;

    /** @var  BooleanValueObject */
    protected $isLastBooking;

    /**
     * CustomerBooking constructor.
     *
     * @param Id            $customerId
     * @param BookingStatus $status
     * @param IntegerValue  $persons
     */
    public function __construct(
        Id $customerId,
        BookingStatus $status,
        IntegerValue $persons
    ) {
        parent::__construct($customerId, $status);
        $this->persons = $persons;
    }

    /**
     * @return Id
     */
    public function getAppointmentId()
    {
        return $this->appointmentId;
    }

    /**
     * @param Id $appointmentId
     */
    public function setAppointmentId(Id $appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    /**
     * @return IntegerValue
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param IntegerValue $persons
     */
    public function setPersons(IntegerValue $persons)
    {
        $this->persons = $persons;
    }

    /**
     * @return Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Collection $payments
     */
    public function setPayments(Collection $payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return IntegerValue
     */
    public function getUtcOffset()
    {
        return $this->utcOffset;
    }

    /**
     * @param IntegerValue $utcOffset
     */
    public function setUtcOffset(IntegerValue $utcOffset)
    {
        $this->utcOffset = $utcOffset;
    }

    /**
     * @return BooleanValueObject
     */
    public function getAggregatedPrice()
    {
        return $this->aggregatedPrice;
    }

    /**
     * @param BooleanValueObject $aggregatedPrice
     */
    public function setAggregatedPrice(BooleanValueObject $aggregatedPrice)
    {
        $this->aggregatedPrice = $aggregatedPrice;
    }

    /**
     * @return BooleanValueObject
     */
    public function isChangedStatus()
    {
        return $this->isChangedStatus;
    }

    /**
     * @param BooleanValueObject $isChangedStatus
     */
    public function setChangedStatus(BooleanValueObject $isChangedStatus)
    {
        $this->isChangedStatus = $isChangedStatus;
    }

    /**
     * @return PackageCustomerService
     */
    public function getPackageCustomerService()
    {
        return $this->packageCustomerService;
    }

    /**
     * @param PackageCustomerService $packageCustomerService
     */
    public function setPackageCustomerService(PackageCustomerService $packageCustomerService)
    {
        $this->packageCustomerService = $packageCustomerService;
    }

    /**
     * @return BooleanValueObject
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @param BooleanValueObject $deposit
     */
    public function setDeposit(BooleanValueObject $deposit)
    {
        $this->deposit = $deposit;
    }

    /**
     * @return BooleanValueObject
     */
    public function isLastBooking()
    {
        return $this->isLastBooking;
    }

    /**
     * @param BooleanValueObject $isLastBooking
     */
    public function setLastBooking($isLastBooking)
    {
        $this->isLastBooking = $isLastBooking;
    }


    /**
     * @return Collection
     */
    public function getTicketsBooking()
    {
        return $this->ticketsBooking;
    }

    /**
     * @param Collection $ticketsBooking
     */
    public function setTicketsBooking(Collection $ticketsBooking)
    {
        $this->ticketsBooking = $ticketsBooking;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'appointmentId'   => null !== $this->getAppointmentId() ? $this->getAppointmentId()->getValue() : null,
                'persons'         => $this->getPersons()->getValue(),
                'token'           => $this->getToken() ? $this->getToken()->getValue() : null,
                'payments'        => null !== $this->getPayments() ? $this->getPayments()->toArray() : null,
                'utcOffset'       => null !== $this->getUtcOffset() ? $this->getUtcOffset()->getValue() : null,
                'aggregatedPrice' => $this->getAggregatedPrice() ? $this->getAggregatedPrice()->getValue() : null,
                'isChangedStatus' => $this->isChangedStatus() ? $this->isChangedStatus()->getValue() : null,
                'isLastBooking'   => $this->isLastBooking() ? $this->isLastBooking()->getValue() : null,
                'packageCustomerService' => $this->getPackageCustomerService() ?
                    $this->getPackageCustomerService()->toArray() : null,
                'ticketsData'         => null !== $this->getTicketsBooking() ?
                    $this->getTicketsBooking()->toArray() : null,
            ]
        );
    }
}
