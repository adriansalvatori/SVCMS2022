<?php

namespace AmeliaBooking\Domain\Entity\User;

use AmeliaBooking\Domain\ValueObjects\Gender;
use AmeliaBooking\Domain\ValueObjects\String\Email;
use AmeliaBooking\Domain\ValueObjects\String\Name;
use AmeliaBooking\Domain\ValueObjects\String\Phone;

/**
 * Class Customer
 *
 * @package AmeliaBooking\Domain\Entity\User
 */
class Customer extends AbstractUser
{
    /** @var Gender */
    private $gender;

    /**
     * @param Name        $firstName
     * @param Name        $lastName
     * @param Email       $email
     * @param Phone       $phone
     * @param Gender      $gender
     */
    public function __construct(
        Name $firstName,
        Name $lastName,
        Email $email,
        Phone $phone,
        Gender $gender
    ) {
        parent::__construct($firstName, $lastName, $email);
        $this->phone = $phone;
        $this->gender = $gender;
    }

    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param Gender $gender
     */
    public function setGender(Gender $gender)
    {
        $this->gender = $gender;
    }

    /**
     * Get the user type in a string form
     */
    public function getType()
    {
        return self::USER_ROLE_CUSTOMER;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'phone'  => $this->getPhone()->getValue(),
                'gender' => $this->getGender()->getValue(),
            ]
        );
    }
}
