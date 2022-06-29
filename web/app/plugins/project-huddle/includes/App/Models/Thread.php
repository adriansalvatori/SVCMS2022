<?php

namespace PH\Models;

use PH\Models\Post;
use PH\Traits\HasParents;
use PH\Traits\HasAssigned;
use PH\Traits\CanBeResolved;
use PH\Traits\HasSubscribers;

if (!defined('ABSPATH')) {
    exit;
}

class Thread extends Post
{
    use CanBeResolved, HasSubscribers, HasParents, HasAssigned;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }
}
