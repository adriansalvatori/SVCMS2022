<?php

namespace PH\Traits;

trait HasApprovalItems
{
    public function getItemsApprovalStatus()
    {
        $defaults = array(
            'total'    => 0,
            'approved' => 0,
        );

        // must have id
        if (!$this->ID) {
            return $defaults;
        }

        // get transient
        $approval_status = get_transient("ph_approved_status_" . $this->ID);

        // this code runs when there is no valid transient set.
        if (false === $approval_status) {
            // get items
            $items = new \WP_Query(
                [
                    'post_type'      => ph_get_item_post_types(),
                    'posts_per_page' => -1,
                    'meta_value'     => $this->ID,
                    'meta_key'       => 'parent_id',
                    'fields'         => 'ids',
                ]
            );

            $item_ids          = $items->posts;
            $approval_comments = [];
            $approved          = 0;

            if (!empty($item_ids)) {
                // count approved
                foreach ($item_ids as $item_id) {
                    if (ph_post_is_approved($item_id)) {
                        $approved++;
                    }
                }

                // get last approval comment
                $approval_comments = ph_get_comments(
                    [
                        'type'     => ph_approval_term_taxonomy(),
                        'post__in' => $item_ids,
                        'number'   => 1,
                    ]
                );
            }

            $by = false;
            $on = false;

            if (!empty($approval_comments) && is_array($approval_comments)) {
                $comment = $approval_comments[0];

                if (is_a($comment, 'WP_Comment')) {
                    $by = $comment->user_id ? get_userdata($comment->user_id)->display_name : $comment->comment_author;
                    $on = $comment->comment_date_gmt; // use gmt and normalize to users timezone later
                }
            }

            $approval_status = array(
                'total'    => count($item_ids),
                'approved' => $approved,
                'by'       => $by,
                'on'       => $on,
            );

            set_transient("ph_approved_status_" . $this->ID, $approval_status, 30 * DAY_IN_SECONDS); // expires in 1 month
        }

        return wp_parse_args($approval_status, $defaults);
    }

    public function itemsApproved()
    {
        $status = $this->getItemsApprovalStatus();
        return $status['total'] && $status['total'] === $status['approved'];
    }
}
