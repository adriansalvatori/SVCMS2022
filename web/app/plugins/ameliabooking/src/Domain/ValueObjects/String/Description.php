<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\ValueObjects\String;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;

/**
 * Class Description
 *
 * @package AmeliaBooking\Domain\ValueObjects\String
 */
final class Description
{
    const MAX_LENGTH = 4095;
    /**
     * @var string
     */
    private $description;

    /**
     * Description constructor.
     *
     * @param string $description
     *
     * @throws InvalidArgumentException
     */
    public function __construct($description)
    {
        if (strlen($description) > static::MAX_LENGTH) {
            throw new InvalidArgumentException(
                "Description \"{$description}\" must be less than " . static::MAX_LENGTH . ' chars'
            );
        }

        $this->description = $description;
    }

    /**
     * Return the description from the value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->description;
    }
}
