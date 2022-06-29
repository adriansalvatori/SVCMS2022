<?php

namespace PH\Models;

use PH\Models\Post;
use PH\Traits\HasParents;
use PH\Traits\CanBeApproved;
use PH\Traits\HasAccessToken;
use PH\Traits\HasSubscribers;
use PH\Traits\HasSignatureKey;
use PH\Traits\HasApprovalItems;

if (!defined('ABSPATH')) {
    exit;
}

class Project extends Post
{
    use HasSubscribers, HasParents, HasAccessToken, CanBeApproved, HasApprovalItems, HasSignatureKey;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }

    public function allowsGuests()
    {
        $guests  = filter_var(get_post_meta($this->ID, 'allow_guests', true), FILTER_VALIDATE_BOOLEAN);
        // backwards compat
        if (!$guests) {
            $guests = get_post_meta($this->ID, 'project_access', true) === 'public';
        }
        return $guests;
    }

    public function projectId()
    {
        return $this->ID;
    }

    public function project()
    {
        return $this;
    }

    public function getThreadsResolveStatus()
    {
        $defaults = [
            'total'    => 0,
            'resolved' => 0,
        ];

        if (!$this->ID) {
            return $defaults;
        }

        $resolve_status = get_transient("ph_resolved_status_" . $this->ID);

        // this code runs when there is no valid transient set
        if (false === $resolve_status) {
            // get pages
            $threads = new \WP_Query(
                array(
                    'post_type'      => ph_get_thread_post_types(),
                    'posts_per_page' => -1,
                    'meta_value'     => $this->ID,
                    'meta_key'       => 'project_id',
                )
            );

            $resolved = 0;
            if (!empty($threads->posts)) {
                foreach ($threads->posts as $thread) {
                    if (filter_var(get_post_meta($thread->ID, 'resolved', true), FILTER_VALIDATE_BOOLEAN)) {
                        $resolved++;
                    }
                }
            }

            $resolve_status = array(
                'total'    => $threads->post_count,
                'resolved' => $resolved,
            );

            set_transient("ph_resolved_status_" . $this->ID, $resolve_status, 30 * DAY_IN_SECONDS); // expires in 1 month
        }

        return wp_parse_args($resolve_status, $defaults);
    }
}
