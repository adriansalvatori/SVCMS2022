<?php

namespace PH\Models;

use PH\Models\Item;
use PH\Traits\HasVersions;

if (!defined('ABSPATH')) {
    exit;
}

class Image extends Item
{
    use HasVersions;
}
