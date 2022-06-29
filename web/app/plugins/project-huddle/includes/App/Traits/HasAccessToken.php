<?php

namespace PH\Traits;

/**
 * Has email subscriptions
 */
trait HasAccessToken
{
    /**
     * Get the access token for the project
     *
     * @return String
     */
    public function getToken()
    {
        if (in_array(get_post_type($this->ID), ph_get_child_post_types())) {
            $parents = ph_get_parents_ids($this->ID);
            return ph_get_post_access_token($parents['project']);
        } else {
            return ph_get_post_access_token($this->ID);
        }
    }

    /**
     * Get the access link for the post's project
     *
     * @return string
     */
    public function getAccessLink()
    {
        return esc_url(add_query_arg(
            array(
                'access_token' => esc_attr($this->getToken()),
            ),
            get_permalink($this->ID)
        ));
    }
}
