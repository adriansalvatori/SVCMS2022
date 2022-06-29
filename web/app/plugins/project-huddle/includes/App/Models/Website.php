<?php

namespace PH\Models;

use PH\Models\Project;
use PH\Traits\HasRestRequests;

if (!defined('ABSPATH')) {
    exit;
}

class Website extends Project
{
    use HasRestRequests;

    public function setInstalled($val)
    {
        return update_post_meta($this->ID, 'ph_installed', (bool) $val);
    }
}
