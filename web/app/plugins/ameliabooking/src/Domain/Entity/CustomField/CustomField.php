<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\CustomField;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\Json;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\IntegerValue;
use AmeliaBooking\Domain\ValueObjects\String\CustomFieldType;
use AmeliaBooking\Domain\ValueObjects\String\Label;

/**
 * Class CustomField
 *
 * @package AmeliaBooking\Domain\Entity\CustomField
 */
class CustomField
{
    /** @var Id */
    private $id;

    /** @var Label */
    private $label;

    /** @var CustomFieldType */
    private $type;

    /** @var BooleanValueObject */
    private $required;

    /** @var IntegerValue */
    private $position;

    /** @var  Json */
    private $translations;

    /** @var Collection */
    private $options;

    /** @var Collection */
    private $services;

    /** @var Collection */
    private $events;

    /**
     * CustomField constructor.
     *
     * @param Label              $label
     * @param CustomFieldType    $type
     * @param BooleanValueObject $required
     * @param IntegerValue       $position
     */
    public function __construct(
        Label $label,
        CustomFieldType $type,
        BooleanValueObject $required,
        IntegerValue $position
    ) {
        $this->label = $label;
        $this->type = $type;
        $this->required = $required;
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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Label $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return CustomFieldType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param CustomFieldType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return BooleanValueObject
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param BooleanValueObject $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return IntegerValue
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param IntegerValue $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return Json
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param Json $translations
     */
    public function setTranslations(Json $translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Collection $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param Collection $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Collection $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'           => null !== $this->getId() ? $this->getId()->getValue() : null,
            'label'        => $this->getLabel()->getValue(),
            'type'         => $this->getType()->getValue(),
            'required'     => $this->getRequired()->getValue(),
            'position'     => $this->getPosition()->getValue(),
            'options'      => $this->getOptions() ? $this->getOptions()->toArray() : [],
            'services'     => $this->getServices() ? $this->getServices()->toArray() : [],
            'events'       => $this->getEvents() ? $this->getEvents()->toArray() : [],
            'translations' => $this->getTranslations() ? $this->getTranslations()->getValue() : null,
        ];
    }
}
