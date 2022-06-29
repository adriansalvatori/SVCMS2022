<?php

namespace PH\Models;

use PH\Models\User;

if (!defined('ABSPATH')) {
    exit;
}

class Activity
{
    public $user;
    private $from = 'now';
    private $to = 'now';
    private $subscribed = false;
    private $include = [];
    private $exclude = [];
    private $types = [];
    private $type = 'activity';
    private $activity = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->types = ph_get_timeline_comment_types();
        $this->projects = $user->subscribedProjectIds();
        return $this;
    }

    public function type($type)
    {
        $this->from = $this->getFrom($type);
        $this->to = $this->getTo($type);

        return $this;
    }

    /**
     * Restrict to a set of projects
     *
     * @param array $ids
     * @return void
     */
    public function projects($ids = array())
    {
        $this->projects = (array) $ids;
        return $this;
    }

    /**
     * Get from time
     *
     * @param [type] $type
     * @return void
     */
    public function getFrom($type)
    {
        switch ($type) {
            case 'daily':
                $from = '1 day ago';
                break;
            case 'weekly':
                $from = '1 week ago';
                break;
            default:
                // get last date
                $set = '-' . PH()->activity_emails->get_interval() . ' minutes';
                $from = PH()->activity_emails->get_last_scheduled_action_date('ph_activity_summary_email');
                if (!$from || strtotime($from) > strtotime($set)) {
                    $from = $set;
                }
                break;
        }
        return apply_filters("ph_{$this->type}_activity_from", $from, $type);
    }

    /**
     * Get to time
     *
     * @param [type] $type
     * @return void
     */
    public function getTo($type)
    {
        $to = 'activity' === $type ? 'now' : 'today 11:59pm';
        return apply_filters("ph_{$this->type}_activity_to", $to, $type);
    }

    public function from($time)
    {
        $this->from = $time;
        return $this;
    }

    public function to($time)
    {
        $this->to = $time;
        return $this;
    }

    public function subscribed($val)
    {
        $this->subscribed = $val;
        return $this;
    }

    public function includeIds($val)
    {
        $this->include = (array) $val;
        return $this;
    }

    public function excludeIds($val)
    {
        $this->exclude = (array) $val;
        return $this;
    }

    public function types($types)
    {
        $this->types = $types;
    }

    public function get()
    {
        if ($this->activity !== null) {
            return $this->activity;
        }

        // must be subscribed to at least one project
        if (empty($this->projects)) {
            return array();
        }

        // remove assign for now
        // if (($key = array_search('ph_assign', $this->types)) !== false) {
        //     unset($this->types[$key]);
        // }

        $this->activity = $this->query();
        return $this->activity;
    }

    /**
     * Query activity
     *
     * @return void
     */
    protected function query()
    {
        $comments = $this->comments();

        // add assignments
        $assignments = [];
        if ($assignments = $this->assignments()) {
            $comments = array_merge($comments, $assignments);
        }

        // if we're only getting subscribed members, 
        // remove any comments for non-subscribed threads
        if ($this->subscribed && !empty($comments)) {
            // get my thread ids
            $members_threads = $this->user->subscribedTaskIds();

            foreach ($comments as $key => $comment) {
                // exclude image or project approvals
                if (
                    'project_image' === get_post_type($comment->comment_post_ID) ||
                    'ph-project' === get_post_type($comment->comment_post_ID)
                ) {
                    continue;
                }
                // if it's not one of my threads, unset
                if (!in_array($comment->comment_post_ID, $members_threads)) {
                    unset($comments[$key]);
                }
            }
        }

        return $comments;
    }

    /**
     * Get comment activity
     *
     * @return array
     */
    public function comments()
    {
        $args = [
            // within the last week
            'date_query' => [
                [
                    'after'     => $this->from,
                    'before'    => $this->to,
                    'inclusive' => true,
                ],
            ],
            // only with the users subscribed projects
            'meta_query' => [
                [
                    'key'     => 'project_id',
                    'value'   => (array) $this->projects,
                    'compare' => 'IN',
                ],
                [
                    'key'   => 'is_private',
                    'compare' => 'NOT EXISTS'
                ]
            ],
            'type__in'   => apply_filters('ph_activity_email_comment_types', $this->types),
        ];

        if ($this->include) {
            $args['post__in'] = $this->include;
        }

        $args = apply_filters('ph_activity_email_query', $args, $this->user->ID, $this);

        remove_action('pre_get_comments', 'ph_hide_ph_comments', 10);
        $comments_query = new \WP_Comment_Query();
        $result   = $comments_query->query($args);
        wp_reset_query();
        add_action('pre_get_comments', 'ph_hide_ph_comments', 10);

        return apply_filters('ph_' . __METHOD__ . '_activity_query', $result);
    }

    /**
     * Get assignement activity
     *
     * @return array
     */
    public function assignments()
    {
        // get users assignments only
        $args = [
            // within the last week
            'date_query' => [
                [
                    'after'     => $this->from,
                    'before'    => $this->to,
                    'inclusive' => true,
                ],
            ],
            'type__in'   => ['ph_assign'],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'assigned',
                    'value'   => (int) $this->user->ID,
                ],
                [
                    'key'     => 'project_id',
                    'value'   => (array) $this->projects,
                    'compare' => 'IN',
                ],
            ]
        ];

        // The Query
        remove_action('pre_get_comments', 'ph_hide_ph_comments', 10);
        $query = new \WP_Comment_Query();
        $result = $query->query($args);
        wp_reset_query();
        add_action('pre_get_comments', 'ph_hide_ph_comments', 10);

        return apply_filters('ph_' . __METHOD__ . '_activity_query', $result);
    }


    /**
     * Group by meta value
     *
     * @param string $meta
     * @return void
     */
    public function groupBy($meta = 'project_id')
    {
        if ($this->activity === null) {
            $this->get();
        }
        if (empty($this->activity)) {
            return $this;
        }

        $comments = [];
        foreach ($this->activity as $comment) {
            if (!$value = get_comment_meta($comment->comment_ID, $meta, true)) {
                continue;
            }
            $comments[$value][] = $comment;
        }

        $this->activity = $comments;

        return $this;
    }
}
