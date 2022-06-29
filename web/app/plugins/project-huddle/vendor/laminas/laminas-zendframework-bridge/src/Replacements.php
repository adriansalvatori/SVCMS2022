<?php

namespace ProjectHuddle\Vendor\Laminas\ZendFrameworkBridge;

use function array_merge;
use function str_replace;
use function strpos;
use function strtr;

class Replacements
{
    /** @var string[] */
    private $replacements;

    public function __construct(array $additionalReplacements = [])
    {
        $this->replacements = array_merge(
            require __DIR__ . '/../config/replacements.php',
            $additionalReplacements
        );

        // Provide multiple variants of strings containing namespace ProjectHuddle\Vendor\separators
        foreach ($this->replacements as $original => $replacement) {
            if (false === strpos($original, '\\')) {
                continue;
            }
            $this->replacements[str_replace('\\', '\\\\', $original)] = str_replace('\\', '\\\\', $replacement);
            $this->replacements[str_replace('\\', '\\\\\\\\', $original)] = str_replace('\\', '\\\\\\\\', $replacement);
        }
    }

    /**
     * @param string $value
     * @return string
     */
    public function replace($value)
    {
        return strtr($value, $this->replacements);
    }
}
