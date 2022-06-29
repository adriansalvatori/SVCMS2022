<?php

namespace PH\Models;

use PH\Models\Post;
use PH\Traits\CanBeApproved;
use PH\Traits\HasParents;
use PH\Traits\HasAccessToken;
use PH\Traits\HasResolvedThreads;

if (!defined('ABSPATH')) {
    exit;
}

class Item extends Post
{
    use HasParents, HasAccessToken, CanBeApproved, HasResolvedThreads;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }

    /**
     * Use project members for subscribed ids
     *
     * @return array
     */
    public function subscribedIds()
    {
        return (array) $this->project()->subscribedIds();
    }
}
