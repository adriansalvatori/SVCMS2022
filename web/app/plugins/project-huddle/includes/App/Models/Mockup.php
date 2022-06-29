<?php

namespace PH\Models;

use PH\Models\Project;
use PH\Traits\HasRestRequests;

if (!defined('ABSPATH')) {
    exit;
}

class Mockup extends Project
{
    use HasRestRequests;
}
