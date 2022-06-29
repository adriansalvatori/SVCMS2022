<?php

namespace ProjectHuddle\Vendor\Laminas\XmlRpc\Value;

use ProjectHuddle\Vendor\Laminas\XmlRpc\AbstractValue;

abstract class AbstractScalar extends AbstractValue
{
    /**
     * Generate the XML code that represent a scalar native MXL-RPC value
     *
     * @return void
     */
    protected function generate()
    {
        $generator = static::getGenerator();

        $generator
            ->openElement('value')
            ->openElement($this->type, $this->value)
            ->closeElement($this->type)
            ->closeElement('value');
    }
}
