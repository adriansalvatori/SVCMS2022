<?php

namespace ProjectHuddle\Vendor\Laminas\Code\Reflection\DocBlock\Tag;

use ProjectHuddle\Vendor\Laminas\Code\Generic\Prototype\PrototypeInterface;

interface TagInterface extends PrototypeInterface
{
    /**
     * @param  string $content
     * @return void
     */
    public function initialize($content);
}
