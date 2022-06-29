<?php

namespace AmeliaBooking\Domain\Entity\Bookable;

use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\String\Description;
use AmeliaBooking\Domain\ValueObjects\String\Name;

/**
 * Class AbstractExtra
 *
 * @package AmeliaBooking\Domain\Entity\Bookable
 */
abstract class AbstractExtra
{
    /** @var Id */
    private $id;

    /** @var Name */
    protected $name;

    /** @var Description */
    protected $description;

    /** @var Price */
    protected $price;

    /** @var PositiveInteger */
    protected $maxQuantity;

    /** @var PositiveInteger */
    protected $position;

    /**
     * @param Name            $name
     * @param Description     $description
     * @param Price           $price
     * @param PositiveInteger $maxQuantity
     * @param PositiveInteger $position
     */
    public function __construct(
        Name $name,
        Description $description,
        Price $price,
        PositiveInteger $maxQuantity,
        PositiveInteger $position
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->maxQuantity = $maxQuantity;
        $this->position = $position;
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
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Description $description
     */
    public function setDescription(Description $description)
    {
        $this->description = $description;
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
     * @return PositiveInteger
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * @param PositiveInteger $maxQuantity
     */
    public function setMaxQuantity(PositiveInteger $maxQuantity)
    {
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * @return PositiveInteger
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param PositiveInteger $position
     */
    public function setPosition(PositiveInteger $position)
    {
        $this->position = $position;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'          => null !== $this->getId() ? $this->getId()->getValue() : null,
            'name'        => $this->getName()->getValue(),
            'description' => $this->getDescription()->getValue(),
            'price'       => $this->getPrice()->getValue(),
            'maxQuantity' => $this->getMaxQuantity()->getValue(),
            'position'    => $this->getPosition()->getValue()
        ];
    }
}
