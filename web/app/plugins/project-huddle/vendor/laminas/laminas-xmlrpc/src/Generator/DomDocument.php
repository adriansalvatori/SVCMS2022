<?php

namespace ProjectHuddle\Vendor\Laminas\XmlRpc\Generator;

use DOMNode;

/**
 * DOMDocument based implementation of a XML/RPC generator
 */
class DomDocument extends AbstractGenerator
{
    /** @var \DOMDocument */
    protected $dom;

    /** @var DOMNode */
    protected $currentElement;

    /**
     * Start XML element
     *
     * @param string $name
     * @return void
     */
    protected function openXmlElement($name)
    {
        $newElement = $this->dom->createElement($name);

        $this->currentElement = $this->currentElement->appendChild($newElement);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text
     */
    protected function writeTextData($text)
    {
        $this->currentElement->appendChild($this->dom->createTextNode($text));
    }

    /**
     * Close a previously opened XML element
     *
     * Resets $currentElement to the next parent node in the hierarchy
     *
     * @param string $name
     * @return void
     */
    protected function closeXmlElement($name)
    {
        if (isset($this->currentElement->parentNode)) {
            $this->currentElement = $this->currentElement->parentNode;
        }
    }

    /**
     * Save XML as a string
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->dom->saveXml();
    }

    /**
     * Initializes internal objects
     *
     * @return void
     */
    protected function init()
    {
        $this->dom            = new \DOMDocument('1.0', $this->encoding);
        $this->currentElement = $this->dom;
    }
}
