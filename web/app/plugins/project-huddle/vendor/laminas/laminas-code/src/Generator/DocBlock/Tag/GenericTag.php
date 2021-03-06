<?php

namespace ProjectHuddle\Vendor\Laminas\Code\Generator\DocBlock\Tag;

use ProjectHuddle\Vendor\Laminas\Code\Generator\AbstractGenerator;
use ProjectHuddle\Vendor\Laminas\Code\Generic\Prototype\PrototypeGenericInterface;

use function ltrim;

class GenericTag extends AbstractGenerator implements TagInterface, PrototypeGenericInterface
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $content;

    /**
     * @param string $name
     * @param string $content
     */
    public function __construct($name = null, $content = null)
    {
        if (! empty($name)) {
            $this->setName($name);
        }

        if (! empty($content)) {
            $this->setContent($content);
        }
    }

    /**
     * @param  string $name
     * @return GenericTag
     */
    public function setName($name)
    {
        $this->name = ltrim($name, '@');
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $content
     * @return GenericTag
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@' . $this->name
            . (! empty($this->content) ? ' ' . $this->content : '');
    }
}
