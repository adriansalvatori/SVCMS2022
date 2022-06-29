<?php

namespace PH\Models;

use PH\Models\Thread;

if (!defined('ABSPATH')) {
    exit;
}

class WebsiteThread extends Thread
{
    protected $post_type = 'phw_comment_loc';

    protected $required = [
        'post_content',
        'post_author',
        'parent_id',
        'project_id',
        'screenPosition',
    ];

    // create comment when thread is created
    public function create($args, $create_comment = true)
    {
        error_log(print_r('creating'));
        $thread = parent::create($args);

        // add comment
        if ($create_comment) {
            ph_insert_comment([
                'user_id' => $args['post_author'],
                'comment_content' => $args['post_content'],
                'comment_post_ID' => $thread->ID,
            ]);
        }

        return $thread;
    }
}
