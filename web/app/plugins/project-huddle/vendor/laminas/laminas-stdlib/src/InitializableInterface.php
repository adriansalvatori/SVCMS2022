<?php

declare(strict_types=1);

namespace ProjectHuddle\Vendor\Laminas\Stdlib;

/**
 * Interface to allow objects to have initialization logic
 */
interface InitializableInterface
{
    /**
     * Init an object
     *
     * @return void
     */
    public function init();
}
