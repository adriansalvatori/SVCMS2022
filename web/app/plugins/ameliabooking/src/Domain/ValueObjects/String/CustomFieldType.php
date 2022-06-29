<?php

namespace AmeliaBooking\Domain\ValueObjects\String;

/**
 * Class PaymentType
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class CustomFieldType
{
    const TEXT = 'text';
    const TEXTAREA = 'text-area';
    const SELECT = 'select';
    const CHECKBOX = 'checkbox';
    const RADIO = 'radio';
    const CONTENT = 'content';

    /**
     * @var string
     */
    private $type;

    /**
     * Status constructor.
     *
     * @param int $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Return the status from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->type;
    }
}
