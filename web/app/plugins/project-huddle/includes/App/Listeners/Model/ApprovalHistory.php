<?php

namespace PH\Listeners\Model;

if (!defined('ABSPATH')) exit;

class ApprovalHistory
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($meta_id, $post_id, $meta_key, $_meta_value)
    {
        return;
    }
}
