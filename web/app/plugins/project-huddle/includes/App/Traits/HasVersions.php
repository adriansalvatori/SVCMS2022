<?php

namespace PH\Traits;

/**
 * Has versions
 */
trait HasVersions
{
    public function versions($args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'order'   => 'DESC',
                'orderby' => 'date ID',
            ]
        );

        $args = array_merge(
            $args,
            [
                'post_parent' => $this->ID,
                'post_type'   => 'ph_version',
            ]
        );

        if (!$revisions = get_children($args)) {
            return [];
        }

        return $revisions;
    }

    public function currentVersion()
    {
        $current = get_transient('ph_current_version_' . $this->ID);

        if (false === $current) {
            $current = count($this->versions()) + 1;
            set_transient('ph_current_version_' . $this->ID, $current, YEAR_IN_SECONDS); // expires in 1 year
        }

        return $current;
    }
}
